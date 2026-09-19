<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

/**
 * Extração local do C3 no DW do PEC.
 *
 * O painel lê somente snapshots MySQL; esta classe é executada apenas pela
 * rotina CLI/background de processamento e consulta o PostgreSQL do PEC.
 */
class C3DwService
{
    public const VERSION = 'dw-c3-2026-09-normative-v2';

    private const CHUNK_SIZE = 100;

    private const PREGNANCY_CIAPS = ['W03', 'W78', 'W79', 'W81', 'W84', 'W85'];

    private const EXCLUSION_CIAPS = ['W82', 'W83'];

    private const PREGNANCY_CID_PREFIX3 = [
        'O10', 'O11', 'O12', 'O13', 'O14', 'O15', 'O16', 'O20', 'O21', 'O22',
        'O23', 'O24', 'O25', 'O26', 'O28', 'O29', 'O30', 'O31', 'O32', 'O33',
        'O34', 'O35', 'O36', 'O40', 'O41', 'O43', 'O44', 'O46', 'O47', 'O48',
        'O98',
    ];

    private const PREGNANCY_CID_PREFIX4 = ['O990', 'O991', 'O992', 'O993', 'O994', 'O995', 'O996', 'O997'];

    private const PREGNANCY_CID_EXACT = ['O752', 'O753', 'Z321', 'Z33', 'Z34', 'Z35', 'Z36', 'Z640'];

    private const EXCLUSION_CID_PREFIX3 = ['O02', 'O03', 'O04', 'O05', 'O06'];

    private const EXCLUSION_CID_EXACT = ['Z303'];

    private const EXAM_CODES = [
        'syphilis' => ['0214010074', '0214010082', '0214010252', '0202031098', '0202031110', '0202031179'],
        'hiv' => ['0214010040', '0214010279', '0214010058', '0213010780', '0213010500', '0202030300'],
        'hepb' => ['0214010104', '0214010236', '0202030784', '0202030970', '0213010208'],
        'hepc' => ['0214010090', '0214010309', '0202030059', '0202030679'],
    ];

    public function __construct(private readonly C3PracticeCalculator $calculator) {}

    /**
     * @param  array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>  $teams
     * @return array<string, mixed>
     */
    public function extract(ConnectionInterface $connection, int $year, int $quarter, array $teams): array
    {
        if ($teams === []) {
            throw new RuntimeException('C3: nenhuma equipe eSF/eAP válida foi identificada no PEC.');
        }

        $firstMonth = (($quarter - 1) * 4) + 1;
        $start = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $end = (clone $start)->addMonths(4)->subDay()->endOfDay();
        $asOf = Carbon::today();
        $eventCutoff = $asOf->lt($end) ? $asOf->copy()->endOfDay() : $end;
        // DUM + 294 dias de gestação + 42 dias de puerpério.
        $historyStart = (clone $start)->subDays(336)->startOfDay();

        $citizens = $this->loadLinkedCitizens($connection, $teams, $asOf);
        if ($citizens === []) {
            return $this->emptyResult($asOf);
        }

        $atdSchema = $this->resolveIndividualCareSchema($connection);
        $cidSchema = $this->resolveCidSchema($connection);
        $conditionKeys = $this->resolveConditionKeys($connection, $cidSchema);
        $pregnancies = $this->identifyPregnancies(
            $connection,
            $citizens,
            $conditionKeys,
            $atdSchema,
            $historyStart,
            $eventCutoff,
            $start,
            $end
        );

        if ($pregnancies === []) {
            return $this->emptyResult($asOf);
        }

        $this->loadClinicalEvents($connection, $pregnancies, $atdSchema, $eventCutoff);

        return $this->consolidate($pregnancies, $teams, $start, $end, $asOf);
    }

    /** @return array<string, mixed> */
    private function emptyResult(Carbon $asOf): array
    {
        return ['scores' => [], 'cohort' => [], 'completed' => [], 'as_of' => $asOf->toDateString(), 'pregnancies' => []];
    }

    /**
     * @param  array<string, array<string, mixed>>  $teams
     * @return array<int, array<string, mixed>>
     */
    private function loadLinkedCitizens(ConnectionInterface $connection, array $teams, Carbon $asOf): array
    {
        $columns = $this->tableColumns($connection, 'tb_acomp_cidadaos_vinculados');
        foreach (['co_fat_cidadao_pec', 'dt_nascimento_cidadao', 'nu_ine_vinc_equipe', 'no_cidadao'] as $required) {
            if (! isset($columns[$required])) {
                throw new RuntimeException("C3: a visão tb_acomp_cidadaos_vinculados não contém a coluna obrigatória {$required}.");
            }
        }

        $select = [
            'co_fat_cidadao_pec AS id',
            'dt_nascimento_cidadao AS born',
            'nu_ine_vinc_equipe AS ine',
            "NULLIF(TRIM(no_cidadao::text), '') AS name",
        ];
        $optional = [
            'nu_cpf_cidadao' => ['cpf', "COALESCE(nu_cpf_cidadao::text, '')"],
            'nu_cns_cidadao' => ['cns', "COALESCE(nu_cns_cidadao::text, '')"],
            'nu_telefone_celular' => ['phone', "COALESCE(nu_telefone_celular::text, '')"],
            'nu_telefone_contato' => ['phone', "COALESCE(nu_telefone_contato::text, '')"],
            'nu_micro_area_domicilio' => ['microarea', "COALESCE(nu_micro_area_domicilio::text, '')"],
            'nu_micro_area_tb_cidadao' => ['microarea', "COALESCE(nu_micro_area_tb_cidadao::text, '')"],
            'nu_micro_area' => ['microarea', "COALESCE(nu_micro_area::text, '')"],
            'nu_microarea' => ['microarea', "COALESCE(nu_microarea::text, '')"],
            'nu_cnes_vinc_equipe' => ['cnes', "COALESCE(nu_cnes_vinc_equipe::text, '')"],
            'nu_cnes_vinc_unidade' => ['cnes', "COALESCE(nu_cnes_vinc_unidade::text, '')"],
            'no_unidade_vinc' => ['facility_name', "COALESCE(no_unidade_vinc::text, '')"],
            'no_raca_cor' => ['race_color', "COALESCE(no_raca_cor::text, '')"],
            'ds_raca_cor_cidadao' => ['race_color', "COALESCE(ds_raca_cor_cidadao::text, '')"],
        ];
        $selectedAliases = [];
        foreach ($optional as $column => [$alias, $expression]) {
            if (isset($columns[$column]) && ! isset($selectedAliases[$alias])) {
                $select[] = "{$expression} AS {$alias}";
                $selectedAliases[$alias] = true;
            }
        }

        $ines = array_keys($teams);
        $rows = $connection->select(sprintf(
            'SELECT %s FROM tb_acomp_cidadaos_vinculados WHERE nu_ine_vinc_equipe IN (%s) AND dt_nascimento_cidadao IS NOT NULL',
            implode(', ', $select),
            $this->marks(count($ines))
        ), $ines);

        $citizens = [];
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $ine = trim((string) $row->ine);
            $name = trim((string) ($row->name ?? ''));
            $cpf = trim((string) ($row->cpf ?? ''));
            $cns = trim((string) ($row->cns ?? ''));
            $hasValidCpf = strlen((string) preg_replace('/\D+/', '', $cpf)) === 11;
            $hasValidCns = strlen((string) preg_replace('/\D+/', '', $cns)) === 15;

            // A nota exige nome, nascimento e ao menos CPF ou CNS com formato válido.
            if ($id <= 0 || ! isset($teams[$ine]) || $name === '' || (! $hasValidCpf && ! $hasValidCns)) {
                continue;
            }

            $born = Carbon::parse($row->born)->startOfDay();
            $citizens[$id] = [
                'id' => $id,
                'ine' => $ine,
                'born' => $born,
                'age_years' => (int) $born->diffInYears($asOf),
                'name' => $name,
                'cpf' => $cpf,
                'cns' => $cns,
                'phone' => trim((string) ($row->phone ?? '')),
                'cnes' => trim((string) ($row->cnes ?? ($teams[$ine]['cnes'] ?? ''))),
                'facility_name' => trim((string) ($row->facility_name ?? ($teams[$ine]['facility_name'] ?? $teams[$ine]['name'] ?? ''))),
                'microarea' => trim((string) ($row->microarea ?? '')),
                'race_color' => trim((string) ($row->race_color ?? '')) ?: 'Não informada',
            ];
        }

        return $citizens;
    }

    /** @return array<string, string|bool|null> */
    private function resolveIndividualCareSchema(ConnectionInterface $connection): array
    {
        $columns = $this->tableColumns($connection, 'tb_fat_atendimento_individual');
        $schema = [
            'gest_age' => isset($columns['nu_idade_gestacional_semanas']) ? 'nu_idade_gestacional_semanas' : (isset($columns['nu_idade_gestacional']) ? 'nu_idade_gestacional' : null),
            'dum' => isset($columns['dt_ultima_menstruacao']) ? 'dt_ultima_menstruacao' : (isset($columns['co_dim_tempo_dum']) ? 'co_dim_tempo_dum' : null),
            'dum_is_fk' => ! isset($columns['dt_ultima_menstruacao']) && isset($columns['co_dim_tempo_dum']),
            'pas' => isset($columns['nu_medicao_pressao_sistolica']) ? 'nu_medicao_pressao_sistolica' : (isset($columns['nu_pressao_sistolica']) ? 'nu_pressao_sistolica' : null),
            'pad' => isset($columns['nu_medicao_pressao_diastolica']) ? 'nu_medicao_pressao_diastolica' : (isset($columns['nu_pressao_diastolica']) ? 'nu_pressao_diastolica' : null),
        ];

        if ($schema['dum_is_fk']) {
            $dumColumns = $this->tableColumns($connection, 'tb_dim_tempo_dum');
            if (isset($dumColumns['co_seq_dim_tempo_dum'], $dumColumns['dt_registro'])) {
                $schema['dum_table'] = 'tb_dim_tempo_dum';
                $schema['dum_pk'] = 'co_seq_dim_tempo_dum';
            } else {
                throw new RuntimeException('C3: co_dim_tempo_dum existe, mas a dimensão oficial tb_dim_tempo_dum não foi localizada.');
            }
        }

        return $schema;
    }

    /** @return array{table:string,pk:string,code:string,fk:string}|null */
    private function resolveCidSchema(ConnectionInterface $connection): ?array
    {
        $problemColumns = $this->tableColumns($connection, 'tb_fat_atd_ind_problemas');
        foreach ([
            ['tb_dim_cid', 'co_seq_dim_cid', 'nu_cid', 'co_dim_cid'],
            ['tb_dim_cid10', 'co_seq_dim_cid10', 'nu_cid10', 'co_dim_cid10'],
        ] as [$table, $pk, $code, $fk]) {
            $columns = $this->tableColumns($connection, $table);
            if (isset($columns[$pk], $columns[$code], $problemColumns[$fk])) {
                return compact('table', 'pk', 'code', 'fk');
            }
        }

        return null;
    }

    /** @return array<string, array<int, int>> */
    private function resolveConditionKeys(ConnectionInterface $connection, ?array $cidSchema): array
    {
        $ciapColumns = $this->tableColumns($connection, 'tb_dim_ciap');
        $ciapValidity = isset($ciapColumns['st_registro_valido']) ? ' AND st_registro_valido = 1' : '';
        $ciapRows = $connection->select(<<<SQL
            SELECT co_seq_dim_ciap AS id,
                   REGEXP_REPLACE(UPPER(COALESCE(nu_ciap::text, '')), '[^A-Z0-9]', '', 'g') AS code
            FROM tb_dim_ciap
            WHERE nu_ciap IS NOT NULL{$ciapValidity}
        SQL);
        $result = ['pregnancy_ciap' => [], 'exclusion_ciap' => [], 'pregnancy_cid' => [], 'exclusion_cid' => []];
        foreach ($ciapRows as $row) {
            $code = (string) $row->code;
            if (in_array($code, self::PREGNANCY_CIAPS, true)) {
                $result['pregnancy_ciap'][] = (int) $row->id;
            }
            if (in_array($code, self::EXCLUSION_CIAPS, true)) {
                $result['exclusion_ciap'][] = (int) $row->id;
            }
        }

        if ($cidSchema) {
            $cidColumns = $this->tableColumns($connection, $cidSchema['table']);
            $cidValidity = isset($cidColumns['st_registro_valido']) ? ' AND st_registro_valido = 1' : '';
            $cidValidity .= isset($cidColumns['st_ativo']) ? ' AND st_ativo = 1' : '';
            $cidRows = $connection->select("SELECT {$cidSchema['pk']} AS id, REGEXP_REPLACE(UPPER(COALESCE({$cidSchema['code']}::text, '')), '[^A-Z0-9]', '', 'g') AS code FROM {$cidSchema['table']} WHERE {$cidSchema['code']} IS NOT NULL{$cidValidity}");
            foreach ($cidRows as $row) {
                $code = (string) $row->code;
                if ($this->isPregnancyCid($code)) {
                    $result['pregnancy_cid'][] = (int) $row->id;
                }
                if ($this->isExclusionCid($code)) {
                    $result['exclusion_cid'][] = (int) $row->id;
                }
            }
        }

        return $result;
    }

    private function isPregnancyCid(string $code): bool
    {
        return in_array($code, self::PREGNANCY_CID_EXACT, true)
            || in_array(substr($code, 0, 3), self::PREGNANCY_CID_PREFIX3, true)
            || in_array(substr($code, 0, 4), self::PREGNANCY_CID_PREFIX4, true);
    }

    private function isExclusionCid(string $code): bool
    {
        return in_array($code, self::EXCLUSION_CID_EXACT, true)
            || in_array(substr($code, 0, 3), self::EXCLUSION_CID_PREFIX3, true);
    }

    /**
     * @param  array<int, array<string, mixed>>  $citizens
     * @param  array<string, array<int, int>>  $keys
     * @param  array<string, string|bool|null>  $schema
     * @return array<int, array<string, mixed>>
     */
    private function identifyPregnancies(
        ConnectionInterface $connection,
        array $citizens,
        array $keys,
        array $schema,
        Carbon $historyStart,
        Carbon $eventCutoff,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $problemColumns = $this->tableColumns($connection, 'tb_fat_atd_ind_problemas');
        $cidSchema = $this->resolveCidSchema($connection);
        $ciapColumn = isset($problemColumns['co_dim_ciap']) ? 'p.co_dim_ciap' : null;
        $cidColumn = $cidSchema ? "p.{$cidSchema['fk']}" : null;
        $gestSelect = $schema['gest_age'] ? "COALESCE(a.{$schema['gest_age']}, 0)" : '0';
        $gestCondition = $schema['gest_age'] ? "a.{$schema['gest_age']} > 0" : 'FALSE';
        $dumJoin = '';
        $dumSelect = 'NULL::date';
        $dumCondition = 'FALSE';
        if ($schema['dum']) {
            if ($schema['dum_is_fk']) {
                $dumJoin = "LEFT JOIN {$schema['dum_table']} tdum ON tdum.{$schema['dum_pk']} = a.{$schema['dum']}";
                $dumSelect = 'tdum.dt_registro';
                $dumCondition = 'tdum.dt_registro IS NOT NULL';
            } else {
                $dumSelect = "a.{$schema['dum']}";
                $dumCondition = "a.{$schema['dum']} IS NOT NULL";
            }
        }

        $eligibleCondition = $this->integerIn($ciapColumn, $keys['pregnancy_ciap']).' OR '.$this->integerIn($cidColumn, $keys['pregnancy_cid']);
        $exclusionCondition = $this->integerIn($ciapColumn, $keys['exclusion_ciap']).' OR '.$this->integerIn($cidColumn, $keys['exclusion_cid']);
        $candidates = [];
        $exclusions = [];

        foreach (array_chunk(array_keys($citizens), self::CHUNK_SIZE) as $ids) {
            $rows = $connection->select(<<<SQL
                SELECT DISTINCT a.co_fat_cidadao_pec AS id,
                       t.dt_registro AS event_date,
                       {$gestSelect} AS gest_age,
                       {$dumSelect} AS dum_date,
                       CASE WHEN ({$exclusionCondition}) THEN 1 ELSE 0 END AS is_exclusion
                FROM tb_fat_atendimento_individual a
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = a.co_dim_tempo
                JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = a.co_dim_profissional_1
                LEFT JOIN tb_fat_atd_ind_problemas p ON p.co_fat_atd_ind = a.co_seq_fat_atd_ind
                {$dumJoin}
                WHERE a.co_fat_cidadao_pec IN ({$this->marks(count($ids))})
                  AND t.dt_registro BETWEEN ? AND ?
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
                  AND (({$eligibleCondition}) OR ({$exclusionCondition}) OR {$dumCondition} OR {$gestCondition})
            SQL, [...$ids, $historyStart->toDateString(), $eventCutoff->toDateString()]);

            foreach ($rows as $row) {
                $id = (int) $row->id;
                if ((int) $row->is_exclusion === 1) {
                    $exclusions[$id][] = Carbon::parse($row->event_date)->startOfDay();

                    continue;
                }

                $eventDate = Carbon::parse($row->event_date)->startOfDay();
                $dum = null;
                $source = 'gestational_age';
                if (! empty($row->dum_date)) {
                    $dum = Carbon::parse($row->dum_date)->startOfDay();
                    $source = 'dum';
                } elseif ((int) $row->gest_age > 0) {
                    $dum = (clone $eventDate)->subDays(((int) $row->gest_age) * 7)->startOfDay();
                }
                if ($dum && $dum->lte($eventDate)) {
                    $candidates[$id][$dum->toDateString()] = ['dum' => $dum, 'source' => $source];
                }
            }
        }

        $pregnancies = [];
        foreach ($candidates as $id => $candidateMap) {
            if (! isset($citizens[$id])) {
                continue;
            }

            $matching = array_values(array_filter($candidateMap, static function (array $candidate) use ($periodStart, $periodEnd, $exclusions, $id): bool {
                $outcome = (clone $candidate['dum'])->addDays(294);
                $puerperiumEnd = (clone $candidate['dum'])->addDays(336);
                foreach ($exclusions[$id] ?? [] as $exclusionDate) {
                    if ($exclusionDate->betweenIncluded($candidate['dum'], $outcome)) {
                        return false;
                    }
                }

                return $puerperiumEnd->betweenIncluded($periodStart, $periodEnd);
            }));
            if ($matching === []) {
                continue;
            }
            usort($matching, static fn (array $a, array $b): int => [$b['source'] === 'dum', $b['dum']->timestamp] <=> [$a['source'] === 'dum', $a['dum']->timestamp]);
            $dum = $matching[0]['dum'];
            $outcome = (clone $dum)->addDays(294);
            $puerperiumEnd = (clone $outcome)->addDays(42);
            $pregnancies[$id] = [
                ...$citizens[$id],
                'dum' => $dum,
                'dum_source' => $matching[0]['source'],
                'dpp' => (clone $dum)->addDays(280),
                'outcome_date' => $outcome,
                'outcome_source' => 'dum_plus_294_days',
                'puerperium_end_date' => $puerperiumEnd,
                'month_ref' => $puerperiumEnd->format('m/Y'),
                'prenatal_consults' => [],
                'puerperal_consults' => [],
                'bp_measurements' => [],
                'anthropometrics' => [],
                'visits' => [],
                'puerperal_visits' => [],
                'vaccines_dtpa' => [],
                'exams' => ['syphilis' => [], 'hiv' => [], 'hepb' => [], 'hepc' => []],
                'oral_health' => [],
            ];
        }

        return $pregnancies;
    }

    /**
     * @param  array<int, array<string, mixed>>  $pregnancies
     * @param  array<string, string|bool|null>  $schema
     */
    private function loadClinicalEvents(ConnectionInterface $connection, array &$pregnancies, array $schema, Carbon $eventCutoff): void
    {
        $lower = collect($pregnancies)->min(static fn (array $p): string => $p['dum']->toDateString());
        $upper = collect($pregnancies)->max(static fn (array $p): string => $p['puerperium_end_date']->toDateString());
        $upper = Carbon::parse($upper)->min($eventCutoff)->toDateString();

        foreach (array_chunk(array_keys($pregnancies), self::CHUNK_SIZE) as $ids) {
            $marks = $this->marks(count($ids));
            $pas = $schema['pas'] ? "a.{$schema['pas']}" : 'NULL';
            $pad = $schema['pad'] ? "a.{$schema['pad']}" : 'NULL';

            $rows = $connection->select(<<<SQL
                SELECT a.co_fat_cidadao_pec AS id, t.dt_registro AS event_date,
                       LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) AS cbo4,
                       a.nu_peso AS weight, a.nu_altura AS height, {$pas} AS pas, {$pad} AS pad
                FROM tb_fat_atendimento_individual a
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = a.co_dim_tempo
                JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = a.co_dim_cbo_1
                JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = a.co_dim_profissional_1
                WHERE a.co_fat_cidadao_pec IN ({$marks})
                  AND t.dt_registro BETWEEN ? AND ?
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
            SQL, [...$ids, $lower, $upper]);
            foreach ($rows as $row) {
                $id = (int) $row->id;
                $date = (string) $row->event_date;
                $event = Carbon::parse($date);
                $pregnancy = $pregnancies[$id];
                if (in_array((string) $row->cbo4, ['2231', '2235', '2251', '2252', '2253'], true)) {
                    if ($event->betweenIncluded($pregnancy['dum'], $pregnancy['outcome_date'])) {
                        $pregnancies[$id]['prenatal_consults'][$date] = true;
                    } elseif ($event->gt($pregnancy['outcome_date']) && $event->lte($pregnancy['puerperium_end_date'])) {
                        $pregnancies[$id]['puerperal_consults'][$date] = true;
                    }
                }
                if ($event->betweenIncluded($pregnancy['dum'], $pregnancy['outcome_date'])) {
                    if (! empty($row->pas) && ! empty($row->pad)) {
                        $pregnancies[$id]['bp_measurements'][$date] = true;
                    }
                    if (! empty($row->weight) && ! empty($row->height)) {
                        $pregnancies[$id]['anthropometrics'][$date] = true;
                    }
                }
            }

            $this->loadProcedureBloodPressure($connection, $pregnancies, $ids, $lower, $upper);
            $this->loadHomeVisits($connection, $pregnancies, $ids, $lower, $upper);
            $this->loadVaccines($connection, $pregnancies, $ids, $lower, $upper);
            $this->loadOralHealth($connection, $pregnancies, $ids, $lower, $upper);
            $this->loadExamEvents($connection, $pregnancies, $ids, $lower, $upper);
        }
    }

    /** @param array<int, array<string, mixed>> $pregnancies @param array<int, int> $ids */
    private function loadProcedureBloodPressure(ConnectionInterface $connection, array &$pregnancies, array $ids, string $lower, string $upper): void
    {
        $rows = $this->selectOptional($connection, <<<SQL
            SELECT pp.co_fat_cidadao_pec AS id, t.dt_registro AS event_date
            FROM tb_fat_proced_atend_proced pp
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = pp.co_dim_tempo
            JOIN tb_dim_procedimento dp ON dp.co_seq_dim_procedimento = pp.co_dim_procedimento
            WHERE pp.co_fat_cidadao_pec IN ({$this->marks(count($ids))})
              AND t.dt_registro BETWEEN ? AND ?
              AND REGEXP_REPLACE(dp.co_proced::text, '[^0-9]', '', 'g') = '0301100039'
        SQL, [...$ids, $lower, $upper]);
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $date = Carbon::parse($row->event_date);
            if ($date->betweenIncluded($pregnancies[$id]['dum'], $pregnancies[$id]['outcome_date'])) {
                $pregnancies[$id]['bp_measurements'][$date->toDateString()] = true;
            }
        }
    }

    /** @param array<int, array<string, mixed>> $pregnancies @param array<int, int> $ids */
    private function loadHomeVisits(ConnectionInterface $connection, array &$pregnancies, array $ids, string $lower, string $upper): void
    {
        $rows = $connection->select(<<<SQL
            SELECT v.co_fat_cidadao_pec AS id, t.dt_registro AS event_date
            FROM tb_fat_visita_domiciliar v
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = v.co_dim_tempo
            JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = v.co_dim_cbo
            JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = v.co_dim_profissional
            WHERE v.co_fat_cidadao_pec IN ({$this->marks(count($ids))})
              AND t.dt_registro BETWEEN ? AND ?
              AND REPLACE(c.nu_cbo::text, '-', '') IN ('515105', '322255')
              AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
        SQL, [...$ids, $lower, $upper]);
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $date = Carbon::parse($row->event_date);
            if ($date->betweenIncluded($pregnancies[$id]['dum'], $pregnancies[$id]['outcome_date'])) {
                $pregnancies[$id]['visits'][$date->toDateString()] = true;
            } elseif ($date->gt($pregnancies[$id]['outcome_date']) && $date->lte($pregnancies[$id]['puerperium_end_date'])) {
                $pregnancies[$id]['puerperal_visits'][$date->toDateString()] = true;
            }
        }
    }

    /** @param array<int, array<string, mixed>> $pregnancies @param array<int, int> $ids */
    private function loadVaccines(ConnectionInterface $connection, array &$pregnancies, array $ids, string $lower, string $upper): void
    {
        $rows = $connection->select(<<<SQL
            SELECT v.co_fat_cidadao_pec AS id, t.dt_registro AS event_date
            FROM tb_fat_vacinacao v
            JOIN tb_fat_vacinacao_vacina dose ON dose.co_fat_vacinacao = v.co_seq_fat_vacinacao
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = dose.co_dim_tempo_vacina_aplicada
            JOIN tb_dim_imunobiologico bio ON bio.co_seq_dim_imunobiologico = dose.co_dim_imunobiologico
            WHERE v.co_fat_cidadao_pec IN ({$this->marks(count($ids))})
              AND t.dt_registro BETWEEN ? AND ? AND TRIM(bio.nu_identificador::text) = '57'
        SQL, [...$ids, $lower, $upper]);
        foreach ($rows as $row) {
            $pregnancies[(int) $row->id]['vaccines_dtpa'][(string) $row->event_date] = true;
        }
    }

    /** @param array<int, array<string, mixed>> $pregnancies @param array<int, int> $ids */
    private function loadOralHealth(ConnectionInterface $connection, array &$pregnancies, array $ids, string $lower, string $upper): void
    {
        $rows = $connection->select(<<<SQL
            SELECT o.co_fat_cidadao_pec AS id, t.dt_registro AS event_date
            FROM tb_fat_atendimento_odonto o
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = o.co_dim_tempo
            JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = o.co_dim_cbo_1
            JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = o.co_dim_profissional_1
            WHERE o.co_fat_cidadao_pec IN ({$this->marks(count($ids))})
              AND t.dt_registro BETWEEN ? AND ?
              AND LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) IN ('2232', '3224')
              AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
        SQL, [...$ids, $lower, $upper]);
        foreach ($rows as $row) {
            $pregnancies[(int) $row->id]['oral_health'][(string) $row->event_date] = true;
        }

        // MIP: procedimento individualizado realizado por CD/TSB durante a gestação.
        $procedureRows = $this->selectOptional($connection, <<<SQL
            SELECT p.co_fat_cidadao_pec AS id, t.dt_registro AS event_date
            FROM tb_fat_proced_atend p
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
            JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = p.co_dim_cbo
            JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = p.co_dim_profissional
            WHERE p.co_fat_cidadao_pec IN ({$this->marks(count($ids))})
              AND t.dt_registro BETWEEN ? AND ?
              AND LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) IN ('2232', '3224')
              AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
        SQL, [...$ids, $lower, $upper]);
        foreach ($procedureRows as $row) {
            $pregnancies[(int) $row->id]['oral_health'][(string) $row->event_date] = true;
        }
    }

    /** @param array<int, array<string, mixed>> $pregnancies @param array<int, int> $ids */
    private function loadExamEvents(ConnectionInterface $connection, array &$pregnancies, array $ids, string $lower, string $upper): void
    {
        $marks = $this->marks(count($ids));
        $rows = $this->selectOptional($connection, <<<SQL
            SELECT e.co_fat_cidadao_pec AS id,
                   COALESCE(e.dt_realizacao, e.dt_resultado, t.dt_registro) AS event_date,
                   REGEXP_REPLACE(dp.co_proced::text, '[^0-9]', '', 'g') AS code
            FROM tb_fat_atd_ind_exames e
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = e.co_dim_tempo
            JOIN tb_dim_procedimento dp ON dp.co_seq_dim_procedimento = e.co_dim_procedimento
            JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = e.co_dim_profissional_1
            WHERE e.co_fat_cidadao_pec IN ({$marks})
              AND COALESCE(e.dt_realizacao, e.dt_resultado, t.dt_registro) BETWEEN ? AND ?
              AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
        SQL, [...$ids, $lower, $upper]);

        $rows = [...$rows, ...$this->selectOptional($connection, <<<SQL
            SELECT p.co_fat_cidadao_pec AS id, t.dt_registro AS event_date,
                   REGEXP_REPLACE(dp.co_proced::text, '[^0-9]', '', 'g') AS code
            FROM tb_fat_atd_ind_procedimentos p
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
            JOIN tb_dim_procedimento dp ON dp.co_seq_dim_procedimento = p.co_dim_procedimento_avaliado
            JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = p.co_dim_profissional_1
            WHERE p.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro BETWEEN ? AND ?
              AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
        SQL, [...$ids, $lower, $upper])];

        // MIP: procedimentos individualizados também podem registrar os testes.
        $rows = [...$rows, ...$this->selectOptional($connection, <<<SQL
            SELECT pp.co_fat_cidadao_pec AS id, t.dt_registro AS event_date,
                   REGEXP_REPLACE(dp.co_proced::text, '[^0-9]', '', 'g') AS code
            FROM tb_fat_proced_atend_proced pp
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = pp.co_dim_tempo
            JOIN tb_dim_procedimento dp ON dp.co_seq_dim_procedimento = pp.co_dim_procedimento
            JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = pp.co_dim_profissional
            WHERE pp.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro BETWEEN ? AND ?
              AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
        SQL, [...$ids, $lower, $upper])];

        foreach ($rows as $row) {
            $category = $this->examCategory((string) $row->code);
            if ($category) {
                $pregnancies[(int) $row->id]['exams'][$category][(string) $row->event_date] = true;
            }
        }
    }

    private function examCategory(string $code): ?string
    {
        $normalized = preg_replace('/\D+/', '', $code) ?? '';
        foreach (self::EXAM_CODES as $category => $codes) {
            if (in_array($normalized, $codes, true)) {
                return $category;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $pregnancies
     * @param  array<string, array<string, mixed>>  $teams
     * @return array<string, mixed>
     */
    private function consolidate(array $pregnancies, array $teams, Carbon $periodStart, Carbon $periodEnd, Carbon $asOf): array
    {
        $scores = [];
        $cohort = [];
        $completed = [];
        $nominal = [];

        foreach ($pregnancies as $pregnancy) {
            $puerperiumEnd = $pregnancy['puerperium_end_date'];
            if (! $puerperiumEnd->betweenIncluded($periodStart, $periodEnd)) {
                continue;
            }
            $ine = $pregnancy['ine'];
            $month = (int) $puerperiumEnd->month;
            $teamType = (string) ($teams[$ine]['type'] ?? '70');
            $result = $this->calculator->calculate($pregnancy, $teamType);

            $cohort[$ine][$month] = ($cohort[$ine][$month] ?? 0) + 1;
            if ($puerperiumEnd->lte($asOf)) {
                $completed[$ine][$month] = ($completed[$ine][$month] ?? 0) + 1;
            }

            $scores[$ine][$month] ??= [
                'numerator' => 0,
                'denominator' => 0,
                'score_percent' => 0.0,
                'practices' => array_fill_keys(range('A', 'K'), 0),
                'incomplete' => 0,
            ];
            $scores[$ine][$month]['denominator']++;
            $scores[$ine][$month]['numerator'] += $result['score_percent'];
            foreach (range('a', 'k') as $letter) {
                if ($result["practice_{$letter}_met"]) {
                    $scores[$ine][$month]['practices'][strtoupper($letter)]++;
                }
            }
            if ($result['score_percent'] < 100) {
                $scores[$ine][$month]['incomplete']++;
            }

            $outcome = $pregnancy['outcome_date'];
            $status = $asOf->lt($outcome) ? 'gestante' : ($asOf->lte($puerperiumEnd) ? 'puerpera' : 'encerrada');
            $gestationalAge = $asOf->lt($outcome)
                ? max(0, min(42, intdiv((int) $pregnancy['dum']->diffInDays($asOf, false), 7)))
                : 42;
            $nominal[] = [
                'cidadao_pec_id' => $pregnancy['id'],
                'cns' => $pregnancy['cns'],
                'cpf' => $pregnancy['cpf'],
                'name' => $pregnancy['name'],
                'birth_date' => $pregnancy['born']->toDateString(),
                'age_years' => $pregnancy['age_years'],
                'phone' => $pregnancy['phone'],
                'race_color' => $pregnancy['race_color'],
                'cnes' => $pregnancy['cnes'],
                'facility_name' => $pregnancy['facility_name'],
                'district' => null,
                'ine' => $ine,
                'team_name' => $teams[$ine]['name'] ?? ('Equipe '.$ine),
                'microarea' => $pregnancy['microarea'],
                'dum' => $pregnancy['dum']->toDateString(),
                'dpp' => $pregnancy['dpp']->toDateString(),
                'outcome_date' => $outcome->toDateString(),
                'puerperium_end_date' => $puerperiumEnd->toDateString(),
                'gestational_age_weeks' => $gestationalAge,
                'current_status' => $status,
                'month_ref' => $pregnancy['month_ref'],
                // A visão comprova vínculo, mas não comprova atualização de MICI/MICDT.
                'mici_updated' => false,
                'micdt_updated' => false,
                'is_accompanied' => true,
                ...array_filter($result, static fn (string $key): bool => str_starts_with($key, 'practice_'), ARRAY_FILTER_USE_KEY),
                'score_percent' => (float) $result['score_percent'],
            ];
        }

        foreach ($scores as &$months) {
            foreach ($months as &$data) {
                $data['score_percent'] = round($data['numerator'] / max(1, $data['denominator']), 2);
            }
        }
        unset($months, $data);

        return ['scores' => $scores, 'cohort' => $cohort, 'completed' => $completed, 'as_of' => $asOf->toDateString(), 'pregnancies' => $nominal];
    }

    /** @return array<string, true> */
    private function tableColumns(ConnectionInterface $connection, string $table): array
    {
        try {
            $rows = $connection->select(
                "SELECT column_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ?",
                [$table]
            );
        } catch (Throwable) {
            return [];
        }

        $columns = [];
        foreach ($rows as $row) {
            $columns[strtolower((string) $row->column_name)] = true;
        }

        return $columns;
    }

    /** @param array<int, mixed> $bindings @return array<int, object> */
    private function selectOptional(ConnectionInterface $connection, string $sql, array $bindings): array
    {
        try {
            return $connection->select($sql, $bindings);
        } catch (Throwable $error) {
            $message = strtolower($error->getMessage());
            if (str_contains($message, 'does not exist') || str_contains($message, 'undefined column') || str_contains($message, 'undefined table')) {
                return [];
            }

            throw $error;
        }
    }

    private function integerIn(?string $column, array $values): string
    {
        if (! $column || $values === []) {
            return 'FALSE';
        }

        return $column.' IN ('.implode(',', array_map('intval', $values)).')';
    }

    private function marks(int $count): string
    {
        return implode(',', array_fill(0, $count, '?'));
    }
}
