<?php

namespace App\Console\Commands;

use App\Enums\SyncStatus;
use App\Enums\TeamType;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\SyncLog;
use App\Services\SettingsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use XMLReader;

#[Signature('esus:sync-snapshot')]
#[Description('Consolida os dados do e-SUS PEC no banco local.')]
class EsusSyncSnapshot extends Command
{
    public function handle(SettingsService $settings): int
    {
        $syncLog = null;

        try {
            $syncLog = SyncLog::on('mysql')->create([
                'status' => SyncStatus::Running,
                'started_at' => now(),
            ]);

            $source = $this->readHomologatedTeams((string) config('esus.homologated_xml_path'));

            $configuredIbge = $settings->get('municipio_ibge');

            if ($configuredIbge !== null && $configuredIbge !== '' && $configuredIbge !== $source['ibge']) {
                throw new RuntimeException('O código IBGE do XML difere do município configurado.');
            }

            $connection = DB::connection('pgsql_esus');
            $connection->getPdo();
            $connection->statement("SET statement_timeout TO '30s'");
            $this->verifySourceSchema($connection);

            $today = Carbon::today();
            $cutoff = $today->copy()->subMonthsNoOverflow(24);
            $teams = $this->countTeams($connection, $source['teams']);
            $registrations = $this->countRegistrations($connection, $source['ibge'], $today, $cutoff);
            $this->storeSnapshot($today, $teams, $registrations, $syncLog);

            $this->info(sprintf(
                'Snapshot %d/Q%d gravado: eSF=%d, eSB=%d, eMulti=%d, MICI=%d/%d, MICDT=%d/%d.',
                $today->year,
                intdiv($today->month - 1, 4) + 1,
                $teams[TeamType::Esf->value],
                $teams[TeamType::SaudeBucal->value],
                $teams[TeamType::Emulti->value],
                $registrations['mici_updated_count'],
                $registrations['mici_outdated_count'],
                $registrations['micdt_updated_count'],
                $registrations['micdt_outdated_count'],
            ));

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            if ($syncLog !== null) {
                try {
                    $syncLog->update([
                        'status' => SyncStatus::Failed,
                        'finished_at' => now(),
                        'error_message' => $exception->getMessage(),
                    ]);
                } catch (\Throwable $logException) {
                    $this->error('Não foi possível finalizar o registro da sincronização: '.$logException->getMessage());
                }
            }

            $this->error('Falha na sincronização: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @return array{ibge: string, teams: list<array{ine: string, cnes: string, type: string}>}
     */
    private function readHomologatedTeams(string $path): array
    {
        if ($path === '' || ! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Configure um XML CNES legível em ESUS_HOMOLOGATED_XML_PATH.');
        }

        $reader = new XMLReader;

        if (! $reader->open($path, null, LIBXML_NONET)) {
            throw new RuntimeException('Não foi possível abrir o XML CNES.');
        }

        $reader->setParserProperty(XMLReader::LOADDTD, false);
        $reader->setParserProperty(XMLReader::SUBST_ENTITIES, false);

        $ibge = null;
        $cnes = null;
        $teams = [];
        $typesByIne = [];

        try {
            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::DOC_TYPE) {
                    throw new RuntimeException('O XML CNES não pode conter uma DTD.');
                }

                if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->name === 'DADOS_GERAIS_ESTABELECIMENTOS') {
                    $cnes = null;

                    continue;
                }

                if ($reader->nodeType !== XMLReader::ELEMENT) {
                    continue;
                }

                if ($reader->name === 'IDENTIFICACAO') {
                    $ibge = trim((string) $reader->getAttribute('CO_IBGE_MUN'));

                    continue;
                }

                if ($reader->name === 'DADOS_GERAIS_ESTABELECIMENTOS') {
                    $cnes = trim((string) $reader->getAttribute('CNES'));

                    continue;
                }

                if ($reader->name !== 'DADOS_EQUIPES' || trim((string) $reader->getAttribute('DT_DESATIVACAO')) !== '') {
                    continue;
                }

                $type = match ($reader->getAttribute('TP_EQUIPE')) {
                    '70' => TeamType::Esf->value,
                    '71' => TeamType::SaudeBucal->value,
                    '72' => TeamType::Emulti->value,
                    default => null,
                };

                if ($type === null) {
                    continue;
                }

                $ine = trim((string) $reader->getAttribute('CO_INE'));

                if (! preg_match('/^\d{10}$/', $ine) || ! preg_match('/^\d{7}$/', (string) $cnes)) {
                    throw new RuntimeException('O XML CNES contém uma equipe com INE ou CNES inválido.');
                }

                if (isset($typesByIne[$ine]) && $typesByIne[$ine] !== $type) {
                    throw new RuntimeException('O XML CNES atribui tipos diferentes ao mesmo INE.');
                }

                $typesByIne[$ine] = $type;
                $teams[$type.'|'.$ine.'|'.$cnes] = compact('ine', 'cnes', 'type');
            }
        } finally {
            $reader->close();
        }

        if (! preg_match('/^\d{7}$/', (string) $ibge) || $teams === []) {
            throw new RuntimeException('O XML CNES não contém município e equipes homologadas válidos.');
        }

        return ['ibge' => $ibge, 'teams' => array_values($teams)];
    }

    /**
     * @param  list<array{ine: string, cnes: string, type: string}>  $teams
     * @return array<string, int>
     */
    private function countTeams(ConnectionInterface $connection, array $teams): array
    {
        $placeholders = implode(', ', array_fill(0, count($teams), '(?, ?, ?)'));
        $bindings = [];

        foreach ($teams as $team) {
            array_push($bindings, $team['ine'], $team['cnes'], $team['type']);
        }

        $rows = $connection->select(<<<SQL
            WITH homologated (ine, cnes, team_type) AS (VALUES {$placeholders})
            SELECT homologated.team_type, COUNT(DISTINCT homologated.ine) AS total_active
            FROM homologated
            INNER JOIN tb_equipe AS equipe ON equipe.nu_ine = homologated.ine
            INNER JOIN tb_unidade_saude AS unidade
                ON unidade.co_seq_unidade_saude = equipe.co_unidade_saude
                AND unidade.nu_cnes = homologated.cnes
            WHERE equipe.st_ativo = 1
            GROUP BY homologated.team_type
            SQL, $bindings);

        $counts = array_fill_keys(array_column(TeamType::cases(), 'value'), 0);

        foreach ($rows as $row) {
            $counts[$row->team_type] = (int) $row->total_active;
        }

        return $counts;
    }

    private function verifySourceSchema(ConnectionInterface $connection): void
    {
        $required = [
            'tb_equipe' => ['nu_ine', 'co_unidade_saude', 'st_ativo'],
            'tb_unidade_saude' => ['co_seq_unidade_saude', 'nu_cnes'],
            'tb_fat_cad_individual' => [
                'co_fat_cidadao_pec', 'co_dim_tempo', 'co_dim_municipio', 'st_ficha_inativa', 'st_recusa_cadastro',
            ],
            'tb_fat_cad_domiciliar' => ['co_dim_tempo', 'co_dim_municipio', 'st_ativo', 'st_recusa_cadastro'],
            'tb_dim_tempo' => ['co_seq_dim_tempo', 'dt_registro'],
            'tb_dim_municipio' => ['co_seq_dim_municipio', 'co_ibge'],
        ];

        $found = [];
        $rows = $connection->select(<<<'SQL'
            SELECT table_name, column_name
            FROM information_schema.columns
            WHERE table_schema = current_schema()
                AND table_name IN (?, ?, ?, ?, ?, ?)
            SQL, array_keys($required));

        foreach ($rows as $row) {
            $found[$row->table_name][] = $row->column_name;
        }

        $missing = [];

        foreach ($required as $table => $columns) {
            foreach (array_diff($columns, $found[$table] ?? []) as $column) {
                $missing[] = $table.'.'.$column;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException('Colunas ausentes no PEC: '.implode(', ', $missing));
        }
    }

    /** @return array<string, int> */
    private function countRegistrations(
        ConnectionInterface $connection,
        string $ibge,
        Carbon $today,
        Carbon $cutoff,
    ): array {
        $referenceDate = $today->toDateString();
        $cutoffDate = $cutoff->toDateString();

        $individual = $connection->selectOne(<<<'SQL'
            SELECT
                COUNT(*) FILTER (WHERE latest.registration_date >= ?) AS updated_count,
                COUNT(*) FILTER (WHERE latest.registration_date < ?) AS outdated_count
            FROM (
                SELECT ficha.co_fat_cidadao_pec, MAX(tempo.dt_registro) AS registration_date
                FROM tb_fat_cad_individual AS ficha
                INNER JOIN tb_dim_tempo AS tempo ON tempo.co_seq_dim_tempo = ficha.co_dim_tempo
                INNER JOIN tb_dim_municipio AS municipio ON municipio.co_seq_dim_municipio = ficha.co_dim_municipio
                WHERE municipio.co_ibge = ?
                    AND ficha.co_fat_cidadao_pec IS NOT NULL
                    AND ficha.st_ficha_inativa = 0
                    AND ficha.st_recusa_cadastro = 0
                    AND tempo.dt_registro <= ?
                GROUP BY ficha.co_fat_cidadao_pec
            ) AS latest
            SQL, [$cutoffDate, $cutoffDate, $ibge, $referenceDate]);

        $domiciliary = $connection->selectOne(<<<'SQL'
            SELECT
                COUNT(*) FILTER (WHERE latest.registration_date >= ?) AS updated_count,
                COUNT(*) FILTER (WHERE latest.registration_date < ?) AS outdated_count
            FROM (
                SELECT ficha.nu_uuid_ficha_origem, MAX(tempo.dt_registro) AS registration_date
                FROM tb_fat_cad_domiciliar AS ficha
                INNER JOIN tb_dim_tempo AS tempo ON tempo.co_seq_dim_tempo = ficha.co_dim_tempo
                INNER JOIN tb_dim_municipio AS municipio ON municipio.co_seq_dim_municipio = ficha.co_dim_municipio
                WHERE municipio.co_ibge = ?
                    AND ficha.nu_uuid_ficha_origem IS NOT NULL
                    AND ficha.st_ativo = 1
                    AND ficha.st_recusa_cadastro = 0
                    AND tempo.dt_registro <= ?
                GROUP BY ficha.nu_uuid_ficha_origem
            ) AS latest
            SQL, [$cutoffDate, $cutoffDate, $ibge, $referenceDate]);

        if ($individual === null || $domiciliary === null) {
            throw new RuntimeException('A contagem dos cadastros não retornou resultado.');
        }

        return [
            'mici_updated_count' => (int) $individual->updated_count,
            'mici_outdated_count' => (int) $individual->outdated_count,
            'micdt_updated_count' => (int) $domiciliary->updated_count,
            'micdt_outdated_count' => (int) $domiciliary->outdated_count,
        ];
    }

    /**
     * @param  array<string, int>  $teams
     * @param  array<string, int>  $registrations
     */
    private function storeSnapshot(Carbon $today, array $teams, array $registrations, SyncLog $syncLog): void
    {
        $key = [
            'year' => $today->year,
            'quarter' => intdiv($today->month - 1, 4) + 1,
        ];

        DB::connection('mysql')->transaction(static function () use ($key, $teams, $registrations, $syncLog): void {
            foreach (TeamType::cases() as $type) {
                ConsolidationTeam::on('mysql')->updateOrCreate(
                    [...$key, 'type' => $type->value],
                    ['total_active' => $teams[$type->value]],
                );
            }

            ConsolidationRegistration::on('mysql')->updateOrCreate($key, $registrations);

            $syncLog->update([
                'status' => SyncStatus::Success,
                'finished_at' => now(),
                'error_message' => null,
            ]);
        });
    }
}
