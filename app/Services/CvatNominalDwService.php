<?php

namespace App\Services;

use App\Models\ConsolidationRegistration;
use App\Models\CvatNominalCitizen;
use App\Models\CvatNominalMetric;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class CvatNominalDwService
{
    public const SOURCE = 'pec_nt30_2025_v2';

    public function getMetrics(?int $year = null, ?int $month = null, ?string $team = null, ?int $quarter = null, ?string $cnes = null): ?object
    {
        if (! Schema::hasTable('cvat_nominal_metrics')) {
            return null;
        }

        $baseMetric = CvatNominalMetric::query()->where('source', self::SOURCE);
        if ($year !== null) {
            $baseMetric->where('year', $year);
        }
        if ($month !== null) {
            $baseMetric->where('month', $month);
        } elseif ($quarter !== null) {
            $startMonth = ($quarter - 1) * 4 + 1;
            $endMonth = $quarter * 4;
            $baseMetric->whereBetween('month', [$startMonth, $endMonth]);
        }
        $metric = $baseMetric->orderByDesc('year')->orderByDesc('month')->first();

        if ((blank($team) && blank($cnes)) || ! $metric) {
            return $metric;
        }

        $teamVal = trim((string) $team);
        $cnesVal = trim((string) $cnes);
        $teamQuery = DB::table('cvat_nominal_citizens')
            ->where('source', self::SOURCE)
            ->where('registration_eligible', true)
            ->where('year', $metric->year)
            ->where('month', $metric->month);

        if (filled($team)) {
            $teamQuery->where(function ($q) use ($teamVal) {
                $q->where('ine', $teamVal)
                  ->orWhere('team_name', 'like', '%'.$teamVal.'%');
            });
        }

        if (filled($cnes)) {
            $teamQuery->where('cnes', $cnesVal);
        }

        $counts = $teamQuery->selectRaw('
            COUNT(*) as mici_total,
            COUNT(CASE WHEN mici_updated = 1 THEN 1 END) as mici_updated,
            COUNT(CASE WHEN mici_updated = 0 THEN 1 END) as mici_outdated,
            COUNT(CASE WHEN has_micdt = 0 THEN 1 END) as mici_without_micdt_total,
            COUNT(CASE WHEN has_micdt = 1 THEN 1 END) as mici_with_micdt_total,
            COUNT(CASE WHEN mici_updated = 1 AND (has_micdt = 0 OR micdt_updated = 0) THEN 1 END) as mici_updated_micdt_outdated_or_none,
            COUNT(CASE WHEN mici_updated = 1 AND has_micdt = 0 THEN 1 END) as mici_updated_without_micdt,
            COUNT(CASE WHEN mici_updated = 1 AND micdt_updated = 1 THEN 1 END) as mici_and_micdt_updated,
            COUNT(CASE WHEN mici_updated = 0 AND has_micdt = 1 AND micdt_updated = 0 THEN 1 END) as mici_and_micdt_outdated,
            COUNT(CASE WHEN is_linked = 1 THEN 1 END) as citizens_linked,
            COUNT(CASE WHEN is_linked = 0 THEN 1 END) as citizens_not_linked,
            MAX(last_visit_date) as last_record_date,
            MAX(team_name) as team_name,
            MAX(ine) as team_ine,
            COUNT(CASE WHEN vulnerability_type = "sem_criterio" AND (social_benefit NOT IN ("bpc", "pbf", "bpc_pbf") OR social_benefit IS NULL) THEN 1 END) as no_criteria_total,
            COUNT(CASE WHEN vulnerability_type = "sem_criterio" AND (social_benefit NOT IN ("bpc", "pbf", "bpc_pbf") OR social_benefit IS NULL) AND is_accompanied = 1 THEN 1 END) as no_criteria_accompanied,
            COUNT(CASE WHEN vulnerability_type = "sem_criterio" AND (social_benefit NOT IN ("bpc", "pbf", "bpc_pbf") OR social_benefit IS NULL) AND is_accompanied = 0 THEN 1 END) as no_criteria_not_accompanied,
            COUNT(CASE WHEN vulnerability_type IN ("idoso", "crianca") AND (social_benefit NOT IN ("bpc", "pbf", "bpc_pbf") OR social_benefit IS NULL) THEN 1 END) as elderly_or_child_total,
            COUNT(CASE WHEN vulnerability_type IN ("idoso", "crianca") AND (social_benefit NOT IN ("bpc", "pbf", "bpc_pbf") OR social_benefit IS NULL) AND is_accompanied = 1 THEN 1 END) as elderly_or_child_accompanied,
            COUNT(CASE WHEN vulnerability_type IN ("idoso", "crianca") AND (social_benefit NOT IN ("bpc", "pbf", "bpc_pbf") OR social_benefit IS NULL) AND is_accompanied = 0 THEN 1 END) as elderly_or_child_not_accompanied,
            COUNT(CASE WHEN vulnerability_type = "sem_criterio" AND social_benefit IN ("bpc", "pbf", "bpc_pbf") THEN 1 END) as bpc_or_pbf_total,
            COUNT(CASE WHEN vulnerability_type = "sem_criterio" AND social_benefit IN ("bpc", "pbf", "bpc_pbf") AND is_accompanied = 1 THEN 1 END) as bpc_or_pbf_accompanied,
            COUNT(CASE WHEN vulnerability_type = "sem_criterio" AND social_benefit IN ("bpc", "pbf", "bpc_pbf") AND is_accompanied = 0 THEN 1 END) as bpc_or_pbf_not_accompanied,
            COUNT(CASE WHEN vulnerability_type IN ("idoso", "crianca") AND social_benefit IN ("bpc", "pbf", "bpc_pbf") THEN 1 END) as elderly_child_and_benefit_total,
            COUNT(CASE WHEN vulnerability_type IN ("idoso", "crianca") AND social_benefit IN ("bpc", "pbf", "bpc_pbf") AND is_accompanied = 1 THEN 1 END) as elderly_child_and_benefit_accompanied,
            COUNT(CASE WHEN vulnerability_type IN ("idoso", "crianca") AND social_benefit IN ("bpc", "pbf", "bpc_pbf") AND is_accompanied = 0 THEN 1 END) as elderly_child_and_benefit_not_accompanied
        ')->first();

        if (! $counts || (int) $counts->mici_total === 0) {
            return $metric;
        }

        $counts->reference_date = $metric->reference_date;
        $counts->year = $metric->year;
        $counts->month = $metric->month;
        $counts->source = $metric->source;
        $counts->benefit_data_available = (bool) ($metric->benefit_data_available ?? false);
        $counts->excluded_without_pec_id = $metric->excluded_without_pec_id ?? 0;
        $counts->pbf_import_id = $metric->pbf_import_id ?? null;
        $counts->pbf_vigencia = $metric->pbf_vigencia ?? null;
        $counts->pbf_confirmed_total = $metric->pbf_confirmed_total ?? 0;
        $counts->is_team_specific = true;

        return $counts;
    }

    /**
     * @param array<string, mixed> $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildCitizensQuery(array $filters = [])
    {
        $metric = $this->getMetrics();
        $query = CvatNominalCitizen::query()->where('source', self::SOURCE)->where('registration_eligible', true);
        if ($metric) {
            $query->where('year', $metric->year)->where('month', $metric->month);
        } else {
            $query->whereRaw('1 = 0');
        }

        foreach (['cns', 'cpf', 'name', 'professional_cns', 'professional_name', 'cnes', 'ine', 'microarea'] as $field) {
            if (filled($filters[$field] ?? null)) {
                $value = trim((string) $filters[$field]);
                if (in_array($field, ['cns', 'cpf', 'professional_cns'], true)) {
                    $value = preg_replace('/\D/', '', $value);
                }
                $query->where($field, 'like', '%'.$value.'%');
            }
        }

        if (filled($filters['team'] ?? null)) {
            $teamVal = trim((string) $filters['team']);
            $query->where(function ($q) use ($teamVal) {
                $q->where('ine', $teamVal)
                  ->orWhere('team_name', 'like', '%'.$teamVal.'%');
            });
        }

        foreach (['race_color', 'vulnerability_type', 'social_benefit'] as $field) {
            if (filled($filters[$field] ?? null) && $filters[$field] !== 'ALL') {
                $query->where($field, $filters[$field]);
            }
        }

        foreach (['mici_updated', 'has_micdt', 'micdt_updated', 'is_accompanied', 'is_linked'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, filter_var($filters[$field], FILTER_VALIDATE_BOOLEAN));
            }
        }

        $sort = (string) ($filters['sort_by'] ?? 'name');
        $allowed = ['name', 'birth_date', 'age', 'ine', 'cnes', 'microarea', 'cidadao_pec_id', 'mici_date', 'micdt_date'];
        $query->orderBy(in_array($sort, $allowed, true) ? $sort : 'name', ($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc');

        return $query;
    }

    /** @param array<string, mixed> $filters */
    public function queryCitizens(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 30);

        return $this->buildCitizensQuery($filters)->paginate(in_array($perPage, [10, 15, 30, 50, 100], true) ? $perPage : 30);
    }

    /** @return array{success: bool, is_live: bool, message: string, metrics: ?CvatNominalMetric, nominal_citizens_count: int, rows: int} */
    public function syncFromPec(?ConnectionInterface $connection = null, int $year = 0, int $month = 0, ?callable $progressCallback = null): array
    {
        $year = $year ?: (int) now()->year;
        $month = $month ?: (int) now()->month;

        try {
            if ($year !== (int) now()->year || $month !== (int) now()->month) {
                throw new RuntimeException('A visão territorial do PEC mostra o estado atual. Só a competência corrente pode ser extraída sem fabricar um histórico.');
            }
            $connection ??= DB::connection('pgsql_esus');
            $connection->select('SELECT 1');

            return $this->extractFromLivePec($connection, $year, $month, $progressCallback);
        } catch (Throwable $e) {
            return [
                'success' => false,
                'is_live' => false,
                'message' => 'Extração não concluída: '.$e->getMessage(),
                'metrics' => $this->getMetrics($year, $month),
                'nominal_citizens_count' => 0,
                'rows' => 0,
            ];
        }
    }

    /** @return array{success: bool, is_live: bool, message: string, metrics: CvatNominalMetric, nominal_citizens_count: int, rows: int} */
    public function extractFromLivePec(ConnectionInterface $connection, int $year, int $month, ?callable $progressCallback = null): array
    {
        $today = Carbon::today();
        if ($year !== (int) $today->year || $month !== (int) $today->month) {
            throw new RuntimeException('A visão territorial do PEC mostra o estado atual. Só a competência corrente pode ser extraída sem fabricar um histórico.');
        }

        $required = [
            'tb_acomp_cidadaos_vinculados', 'tb_cidadao', 'tb_fat_cad_individual', 'tb_fat_cidadao_territorio',
            'tb_dim_tempo', 'tb_fat_visita_domiciliar', 'tb_fat_atendimento_individual',
            'tb_fat_atendimento_odonto', 'tb_fat_atendimento_domiciliar', 'tb_fat_atvdd_coletiva_part',
            'tb_fat_marca_consumo_alimnt', 'tb_fat_proced_atend', 'tb_fat_vacinacao',
        ];
        foreach ($required as $table) {
            if (! $connection->selectOne('SELECT to_regclass(?) AS name', [$table])?->name) {
                throw new RuntimeException("Tabela necessária ausente no PEC: {$table}.");
            }
        }

        $eligibleInes = array_fill_keys(array_column(app(CnesXmlParserService::class)->getEligibleC1Teams(), 'ine'), true);
        if ($eligibleInes === []) {
            throw new RuntimeException('Não há relação CNES válida de equipes eSF/eAP para aferir o vínculo.');
        }

        $referenceDate = $today->toDateString();
        $cutoff24 = $today->copy()->subMonthsNoOverflow(24)->toDateString();
        $cutoff12 = $today->copy()->subMonthsNoOverflow(12)->toDateString();
        $excludedWithoutId = 0;
        $processed = 0;
        $pbfImport = null;

        $connection->beginTransaction();
        try {
            $connection->statement('SET TRANSACTION READ ONLY');
            $connection->statement('SET LOCAL statement_timeout TO 30000');
            $excludedWithoutId = (int) $connection->selectOne('SELECT COUNT(*) AS n FROM tb_acomp_cidadaos_vinculados WHERE co_fat_cidadao_pec IS NULL')->n;
            $pbfImport = $this->loadLatestPbfImport($connection);

            DB::transaction(function () use ($connection, $year, $month, $referenceDate, $cutoff24, $cutoff12, $eligibleInes, $progressCallback, &$processed, $excludedWithoutId, $pbfImport): void {
                // Legacy records have no source. Removing them avoids an apparent second, fictitious population.
                CvatNominalCitizen::query()->whereNull('source')->delete();
                CvatNominalMetric::query()->whereNull('source')->delete();
                CvatNominalCitizen::query()->where('source', self::SOURCE)->delete();

                $lastId = 0;
                do {
                    $rows = $connection->select(<<<'SQL'
                        SELECT v.co_fat_cidadao_pec AS id, v.no_cidadao AS name,
                               v.dt_nascimento_cidadao AS birth_date, v.no_sexo_cidadao AS gender,
                               v.no_raca_cor AS race_color, v.nu_cns_cidadao AS cns, v.nu_cpf_cidadao AS cpf,
                               v.nu_cnes_vinc_equipe AS cnes, v.nu_ine_vinc_equipe AS ine,
                               v.no_equipe_vinc_equipe AS team_name,
                               v.nu_micro_area_domicilio AS microarea,
                               v.st_possui_fci AS has_fci, v.st_possui_fcdt AS has_fcdt,
                               v.dt_atualizacao_fcd AS micdt_date,
                               COALESCE(c.st_fora_area, 0) AS fora_area
                        FROM tb_acomp_cidadaos_vinculados v
                        LEFT JOIN tb_cidadao c ON c.co_seq_cidadao = v.co_cidadao
                        WHERE v.co_fat_cidadao_pec > ?
                        ORDER BY v.co_fat_cidadao_pec
                        LIMIT 500
                    SQL, [$lastId]);

                    if ($rows === []) {
                        break;
                    }
                    $ids = array_map(static fn (object $row): int => (int) $row->id, $rows);
                    $lastId = max($ids);
                    $cad = $this->latestCadastros($connection, $ids, $referenceDate);
                    $moved = $this->latestTerritoryStatus($connection, $ids);
                    $contacts = $this->contactCounts($connection, $ids, $cutoff12, $referenceDate);
                    $now = now();
                    $batch = [];

                    foreach ($rows as $row) {
                        $id = (int) $row->id;
                        $fci = $cad[$id] ?? null;
                        $birthDate = $row->birth_date ? (string) $row->birth_date : null;
                        $hasIdentity = $birthDate && (trim((string) $row->cpf) !== '' || trim((string) $row->cns) !== '');
                        $eligible = $hasIdentity && (int) $row->has_fci === 1
                            && $fci && ! $fci['refused'] && ! $fci['inactive'] && ! $fci['moved']
                            && (int) $row->fora_area !== 1 && ! ($moved[$id] ?? false);
                        $miciDate = $eligible ? $fci['date'] : null;
                        $micdtDate = $eligible && (int) $row->has_fcdt === 1 && $row->micdt_date
                            && (string) $row->micdt_date <= $referenceDate ? (string) $row->micdt_date : null;
                        $age = $birthDate ? max(0, Carbon::parse($birthDate)->diffInYears($referenceDate)) : 0;
                        $care = $contacts[$id]['care'] ?? 0;
                        $total = $contacts[$id]['total'] ?? 0;
                        // PEC stores PBF imports separately from individual registration flags.
                        // An unmatched document is unknown because BPC remains unavailable.
                        $cpf = preg_replace('/\D/', '', (string) $row->cpf);
                        $cns = preg_replace('/\D/', '', (string) $row->cns);
                        $hasPbf = $pbfImport !== null && (
                            ($cpf !== '' && isset($pbfImport['cpf'][$cpf]))
                            || ($cns !== '' && isset($pbfImport['cns'][$cns]))
                        );
                        $batch[] = [
                            'cidadao_pec_id' => $id,
                            'source' => self::SOURCE,
                            'year' => $year,
                            'month' => $month,
                            'name' => mb_strtoupper(trim((string) $row->name)) ?: 'NOME NÃO INFORMADO',
                            'birth_date' => $birthDate,
                            'age' => $age,
                            'gender' => $row->gender ? mb_substr((string) $row->gender, 0, 1) : null,
                            'race_color' => $row->race_color ?: 'Não informada',
                            'cns' => trim((string) $row->cns),
                            'cpf' => trim((string) $row->cpf),
                            'cnes' => trim((string) $row->cnes),
                            'ine' => trim((string) $row->ine),
                            'team_name' => trim((string) $row->team_name),
                            'facility_name' => '',
                            'professional_cns' => '',
                            'professional_name' => '',
                            'microarea' => trim((string) $row->microarea) ?: '00',
                            'registration_eligible' => (bool) $eligible,
                            'mici_date' => $miciDate,
                            'mici_updated' => $miciDate !== null && $miciDate >= $cutoff24,
                            'has_micdt' => $micdtDate !== null,
                            'micdt_date' => $micdtDate,
                            'micdt_updated' => $micdtDate !== null && $micdtDate >= $cutoff24,
                            'is_linked' => $eligible && isset($eligibleInes[trim((string) $row->ine)]),
                            'vulnerability_type' => $age >= 60 ? 'idoso' : ($age < 5 && $birthDate ? 'crianca' : 'sem_criterio'),
                            'social_benefit' => $hasPbf ? 'pbf' : 'nao_informado',
                            'care_contacts' => $care,
                            'total_contacts' => $total,
                            'is_accompanied' => $eligible && $care >= 1 && $total >= 2,
                            'last_visit_date' => $contacts[$id]['last'] ?? null,
                            'address' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    CvatNominalCitizen::query()->insert($batch);
                    $processed += count($batch);
                    if ($progressCallback && $processed % 5000 < 500) {
                        $progressCallback(min(85, 20 + intdiv($processed, 1000)), "Processados {$processed} cidadãos do PEC");
                    }
                } while (count($rows) === 500);

                if ($processed === 0) {
                    throw new RuntimeException('A visão territorial não retornou cidadãos identificáveis; nenhum consolidado foi gravado.');
                }
                $this->consolidateMetricsFromLocal($year, $month, $referenceDate, $excludedWithoutId, $pbfImport);
            });
            $connection->rollBack();
        } catch (Throwable $e) {
            $connection->rollBack();
            throw $e;
        }

        $metrics = $this->getMetrics($year, $month);
        if ($progressCallback !== null) {
            $progressCallback(100, 'Extração local concluída; o escore Y ainda depende dos dados de BPC.');
        }

        return [
            'success' => true,
            'is_live' => true,
            'message' => "Extraídos {$processed} cidadãos do PEC para {$year}/M{$month}. Resultado local parcial; sem classificação ministerial.",
            'metrics' => $metrics,
            'nominal_citizens_count' => $processed,
            'rows' => $processed,
        ];
    }

    /** @return array{id: int, vigencia: string, cpf: array<string, true>, cns: array<string, true>}|null */
    private function loadLatestPbfImport(ConnectionInterface $connection): ?array
    {
        if (! $connection->selectOne("SELECT to_regclass('tb_importacao_bolsa_familia') AS name")?->name
            || ! $connection->selectOne("SELECT to_regclass('tb_cidadao_bolsa_familia') AS name")?->name) {
            return null;
        }

        $import = $connection->selectOne(<<<'SQL'
            SELECT co_seq_importaca_bolsa_familia AS id, ds_vigencia AS vigencia
            FROM tb_importacao_bolsa_familia
            WHERE st_importacao = 'FINALIZADO' AND st_vig_mais_recente_localidade = 1
            ORDER BY dt_importacao_fim DESC, co_seq_importaca_bolsa_familia DESC
            LIMIT 1
        SQL);
        if (! $import) {
            return null;
        }

        $documents = ['CPF' => [], 'CNS' => []];
        foreach ($connection->select(<<<'SQL'
            SELECT tp_documento, nu_documento
            FROM tb_cidadao_bolsa_familia
            WHERE co_importacao_bolsa_familia = ? AND tp_documento IN ('CPF', 'CNS')
        SQL, [(int) $import->id]) as $row) {
            $document = preg_replace('/\D/', '', (string) $row->nu_documento);
            if ($document !== '') {
                $documents[(string) $row->tp_documento][$document] = true;
            }
        }

        return [
            'id' => (int) $import->id,
            'vigencia' => (string) $import->vigencia,
            'cpf' => $documents['CPF'],
            'cns' => $documents['CNS'],
        ];
    }

    /** @param list<int> $ids @return array<int, array{date: string, refused: bool, inactive: bool, moved: bool}> */
    private function latestCadastros(ConnectionInterface $connection, array $ids, string $referenceDate): array
    {
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $rows = $connection->select(<<<SQL
            SELECT DISTINCT ON (fci.co_fat_cidadao_pec)
                   fci.co_fat_cidadao_pec AS id, dt.dt_registro AS date,
                   fci.st_recusa_cadastro AS refused, fci.st_ficha_inativa AS inactive,
                   fci.co_dim_tipo_saida_cadastro AS exit_type
            FROM tb_fat_cad_individual fci
            JOIN tb_dim_tempo dt ON dt.co_seq_dim_tempo = fci.co_dim_tempo
            WHERE fci.co_fat_cidadao_pec IN ({$marks}) AND dt.dt_registro <= ?
            ORDER BY fci.co_fat_cidadao_pec, dt.dt_registro DESC, fci.co_seq_fat_cad_individual DESC
        SQL, [...$ids, $referenceDate]);

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row->id] = [
                'date' => (string) $row->date,
                'refused' => (int) $row->refused === 1,
                'inactive' => (int) $row->inactive === 1,
                'moved' => (int) $row->exit_type === 2,
            ];
        }

        return $result;
    }

    /** @param list<int> $ids @return array<int, bool> */
    private function latestTerritoryStatus(ConnectionInterface $connection, array $ids): array
    {
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $rows = $connection->select(<<<SQL
            SELECT DISTINCT ON (co_fat_cidadao_pec) co_fat_cidadao_pec AS id, st_mudou_se AS moved
            FROM tb_fat_cidadao_territorio
            WHERE co_fat_cidadao_pec IN ({$marks})
            ORDER BY co_fat_cidadao_pec, co_seq_fat_cidadao_territorio DESC
        SQL, $ids);

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row->id] = (int) $row->moved === 1;
        }

        return $result;
    }

    /** @param list<int> $ids @return array<int, array{total: int, care: int, last: ?string}> */
    private function contactCounts(ConnectionInterface $connection, array $ids, string $from, string $until): array
    {
        $sources = [
            ['tb_fat_visita_domiciliar', 'co_seq_fat_visita_domiciliar', 1],
            ['tb_fat_atendimento_individual', 'co_seq_fat_atd_ind', 1],
            ['tb_fat_atendimento_odonto', 'co_seq_fat_atd_odnt', 1],
            ['tb_fat_atendimento_domiciliar', 'co_seq_fat_atend_domiciliar', 1],
            ['tb_fat_atvdd_coletiva_part', 'co_seq_fat_atvdd_cltv_part', 1],
            ['tb_fat_marca_consumo_alimnt', 'co_seq_fat_marca_con_almnt', 1],
            ['tb_fat_proced_atend', 'co_seq_fat_proced_atend', 0],
            ['tb_fat_vacinacao', 'co_seq_fat_vacinacao', 0],
        ];
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $unions = [];
        $bindings = [];
        foreach ($sources as [$table, $key, $care]) {
            $unions[] = "SELECT f.co_fat_cidadao_pec AS id, COALESCE(NULLIF(TRIM(f.nu_uuid_ficha::text), ''), '{$table}:' || f.{$key}) AS event_key, {$care} AS care, dt.dt_registro AS date FROM {$table} f JOIN tb_dim_tempo dt ON dt.co_seq_dim_tempo = f.co_dim_tempo WHERE f.co_fat_cidadao_pec IN ({$marks}) AND dt.dt_registro BETWEEN ? AND ?";
            array_push($bindings, ...[...$ids, $from, $until]);
        }
        $union = implode(' UNION ALL ', $unions);
        $rows = $connection->select(<<<SQL
            SELECT id, COUNT(DISTINCT event_key) AS total,
                   COUNT(DISTINCT CASE WHEN care = 1 THEN event_key END) AS care,
                   MAX(date) AS last_date
            FROM ({$union}) events
            GROUP BY id
        SQL, $bindings);

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row->id] = ['total' => (int) $row->total, 'care' => (int) $row->care, 'last' => $row->last_date ? (string) $row->last_date : null];
        }

        return $result;
    }

    /** @param array{id: int, vigencia: string, cpf: array<string, true>, cns: array<string, true>}|null $pbfImport */
    public function consolidateMetricsFromLocal(int $year, int $month, ?string $referenceDate = null, int $excludedWithoutId = 0, ?array $pbfImport = null): CvatNominalMetric
    {
        $base = CvatNominalCitizen::query()->where('source', self::SOURCE)->where('year', $year)->where('month', $month)->where('registration_eligible', true);
        $total = (clone $base)->count();
        if ($total === 0) {
            throw new RuntimeException('Nenhum cadastro individual válido foi encontrado; consolidado não gerado.');
        }
        $updated = (clone $base)->where('mici_updated', true)->count();
        $withMicdt = (clone $base)->where('has_micdt', true)->count();
        $only = (clone $base)->where('mici_updated', true)->where(fn ($q) => $q->where('has_micdt', false)->orWhere('micdt_updated', false))->count();
        $both = (clone $base)->where('mici_updated', true)->where('micdt_updated', true)->count();
        $linked = (clone $base)->where('is_linked', true)->count();

        $nominalMetric = CvatNominalMetric::query()->updateOrCreate(['year' => $year, 'month' => $month], [
            'source' => self::SOURCE,
            'reference_date' => $referenceDate ?? now()->toDateString(),
            'benefit_data_available' => false,
            'excluded_without_pec_id' => $excludedWithoutId,
            'pbf_import_id' => $pbfImport['id'] ?? null,
            'pbf_vigencia' => $pbfImport['vigencia'] ?? null,
            'pbf_confirmed_total' => (clone $base)->where('social_benefit', 'pbf')->count(),
            'last_record_date' => (clone $base)->max('last_visit_date'),
            'mici_total' => $total,
            'mici_updated' => $updated,
            'mici_outdated' => $total - $updated,
            'mici_without_micdt_total' => $total - $withMicdt,
            'mici_updated_micdt_outdated_or_none' => $only,
            'mici_updated_without_micdt' => (clone $base)->where('mici_updated', true)->where('has_micdt', false)->count(),
            'mici_with_micdt_total' => $withMicdt,
            'mici_and_micdt_updated' => $both,
            'mici_and_micdt_outdated' => (clone $base)->where('mici_updated', false)->where('has_micdt', true)->where('micdt_updated', false)->count(),
            'citizens_linked' => $linked,
            'citizens_not_linked' => $total - $linked,
            'no_criteria_total' => (clone $base)->where('vulnerability_type', 'sem_criterio')->where(fn ($q) => $q->whereNotIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->orWhereNull('social_benefit'))->count(),
            'elderly_or_child_total' => (clone $base)->whereIn('vulnerability_type', ['idoso', 'crianca'])->where(fn ($q) => $q->whereNotIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->orWhereNull('social_benefit'))->count(),
            'bpc_or_pbf_total' => (clone $base)->where('vulnerability_type', 'sem_criterio')->whereIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->count(),
            'elderly_child_and_benefit_total' => (clone $base)->whereIn('vulnerability_type', ['idoso', 'crianca'])->whereIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->count(),
            'no_criteria_accompanied' => (clone $base)->where('vulnerability_type', 'sem_criterio')->where(fn ($q) => $q->whereNotIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->orWhereNull('social_benefit'))->where('is_accompanied', true)->count(),
            'elderly_or_child_accompanied' => (clone $base)->whereIn('vulnerability_type', ['idoso', 'crianca'])->where(fn ($q) => $q->whereNotIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->orWhereNull('social_benefit'))->where('is_accompanied', true)->count(),
            'bpc_or_pbf_accompanied' => (clone $base)->where('vulnerability_type', 'sem_criterio')->whereIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->where('is_accompanied', true)->count(),
            'elderly_child_and_benefit_accompanied' => (clone $base)->whereIn('vulnerability_type', ['idoso', 'crianca'])->whereIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->where('is_accompanied', true)->count(),
            'no_criteria_not_accompanied' => (clone $base)->where('vulnerability_type', 'sem_criterio')->where(fn ($q) => $q->whereNotIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->orWhereNull('social_benefit'))->where('is_accompanied', false)->count(),
            'elderly_or_child_not_accompanied' => (clone $base)->whereIn('vulnerability_type', ['idoso', 'crianca'])->where(fn ($q) => $q->whereNotIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->orWhereNull('social_benefit'))->where('is_accompanied', false)->count(),
            'bpc_or_pbf_not_accompanied' => (clone $base)->where('vulnerability_type', 'sem_criterio')->whereIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->where('is_accompanied', false)->count(),
            'elderly_child_and_benefit_not_accompanied' => (clone $base)->whereIn('vulnerability_type', ['idoso', 'crianca'])->whereIn('social_benefit', ['bpc', 'pbf', 'bpc_pbf'])->where('is_accompanied', false)->count(),
        ]);

        $quarter = (int) ceil($month / 4);
        ConsolidationRegistration::query()->updateOrCreate(
            ['year' => $year, 'quarter' => $quarter],
            [
                'mici_updated_count' => $nominalMetric->mici_updated,
                'mici_outdated_count' => $nominalMetric->mici_outdated,
                'micdt_updated_count' => $nominalMetric->mici_and_micdt_updated,
                'micdt_outdated_count' => $nominalMetric->mici_and_micdt_outdated,
            ]
        );

        return $nominalMetric;
    }
}
