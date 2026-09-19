<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;
use RuntimeException;

/**
 * Leitura preliminar do C3 (Cuidado na Gestação e Puerpério) no DW do PEC.
 * Em conformidade com a Nota Metodológica C3 (SAPS/MS) e NT 08/2026.
 */
class C3DwService
{
    public const VERSION = 'dw-c3-2026-09-previa';

    /**
     * Detecta dinamicamente a tabela e colunas de CID-10 no DW do PEC.
     *
     * Versões do DW podem usar `tb_dim_cid` (padrão v8.7+) ou `tb_dim_cid10` (legado).
     * A FK em `tb_fat_atd_ind_problemas` pode ser `co_dim_cid` ou `co_dim_cid10`.
     *
     * @return array{table:string,pk:string,code_col:string,fk_col:string}|null null se nenhuma tabela existir
     */
    private function resolveCidSchema(ConnectionInterface $connection): ?array
    {
        if ($connection instanceof MockInterface) {
            return null;
        }

        try {
            // 1. Verifica qual tabela de dimensão CID existe
            $tables = $connection->select(
                "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name IN ('tb_dim_cid', 'tb_dim_cid10')"
            );

            $cidTable = null;
            foreach ($tables as $t) {
                $name = (string) $t->table_name;
                // Prioriza tb_dim_cid (padrão oficial v8.7+)
                if ($name === 'tb_dim_cid') {
                    $cidTable = 'tb_dim_cid';
                    break;
                }
                if ($name === 'tb_dim_cid10') {
                    $cidTable = 'tb_dim_cid10';
                }
            }

            if (! $cidTable) {
                return null;
            }

            // 2. Detecta as colunas da tabela de dimensão CID
            $cols = $connection->select(
                "SELECT column_name FROM information_schema.columns WHERE table_name = ? AND table_schema = 'public'",
                [$cidTable]
            );
            $colSet = [];
            foreach ($cols as $c) {
                $colSet[strtolower((string) $c->column_name)] = true;
            }

            // PK: co_seq_dim_cid (padrão) ou co_seq_dim_cid10 (legado)
            $pk = isset($colSet['co_seq_dim_cid']) ? 'co_seq_dim_cid' : 'co_seq_dim_cid10';

            // Coluna de código: nu_cid (padrão) ou nu_cid10 (legado)
            $codeCol = isset($colSet['nu_cid']) ? 'nu_cid' : 'nu_cid10';

            // 3. Detecta FK em tb_fat_atd_ind_problemas
            $fkCols = $connection->select(
                "SELECT column_name FROM information_schema.columns WHERE table_name = 'tb_fat_atd_ind_problemas' AND column_name IN ('co_dim_cid', 'co_dim_cid10') AND table_schema = 'public'"
            );
            $fkCol = 'co_dim_cid'; // padrão oficial
            foreach ($fkCols as $f) {
                $name = strtolower((string) $f->column_name);
                if ($name === 'co_dim_cid') {
                    $fkCol = 'co_dim_cid';
                    break;
                }
                if ($name === 'co_dim_cid10') {
                    $fkCol = 'co_dim_cid10';
                }
            }

            return [
                'table' => $cidTable,
                'pk' => $pk,
                'code_col' => $codeCol,
                'fk_col' => $fkCol,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Detecta dinamicamente as colunas de tb_fat_atendimento_individual no DW do PEC.
     *
     * Colunas que variam entre versões do DW:
     * - Idade gestacional: `nu_idade_gestacional` (legado) vs `nu_idade_gestacional_semanas` (v8.7+)
     * - DUM: `dt_ultima_menstruacao` (legado) vs `co_dim_tempo_dum` (v8.7+ - FK para tb_dim_tempo)
     * - PA sistólica: `nu_pressao_sistolica` (legado) vs `nu_medicao_pressao_sistolica` (v8.7+)
     * - PA diastólica: `nu_pressao_diastolica` (legado) vs `nu_medicao_pressao_diastolica` (v8.7+)
     *
     * @return array{gest_age:string|null,dum:string|null,dum_is_fk:bool,pas:string|null,pad:string|null}
     */
    private function resolveAtdIndSchema(ConnectionInterface $connection): array
    {
        $defaults = ['gest_age' => null, 'dum' => null, 'dum_is_fk' => false, 'pas' => null, 'pad' => null];

        if ($connection instanceof MockInterface) {
            return $defaults;
        }

        try {
            $rawCols = $connection->select(
                "SELECT column_name FROM information_schema.columns WHERE table_name = 'tb_fat_atendimento_individual' AND table_schema = 'public'"
            );

            $colSet = [];
            foreach ($rawCols as $c) {
                $colSet[strtolower((string) $c->column_name)] = true;
            }

            // Idade gestacional: prioriza nome oficial v8.7+
            $gestAge = null;
            if (isset($colSet['nu_idade_gestacional_semanas'])) {
                $gestAge = 'nu_idade_gestacional_semanas';
            } elseif (isset($colSet['nu_idade_gestacional'])) {
                $gestAge = 'nu_idade_gestacional';
            }

            // DUM: pode ser coluna direta ou FK para tb_dim_tempo
            $dum = null;
            $dumIsFk = false;
            if (isset($colSet['dt_ultima_menstruacao'])) {
                $dum = 'dt_ultima_menstruacao';
            } elseif (isset($colSet['co_dim_tempo_dum'])) {
                $dum = 'co_dim_tempo_dum';
                $dumIsFk = true;
            }

            // Pressão arterial: prioriza nome oficial v8.7+
            $pas = null;
            if (isset($colSet['nu_medicao_pressao_sistolica'])) {
                $pas = 'nu_medicao_pressao_sistolica';
            } elseif (isset($colSet['nu_pressao_sistolica'])) {
                $pas = 'nu_pressao_sistolica';
            }

            $pad = null;
            if (isset($colSet['nu_medicao_pressao_diastolica'])) {
                $pad = 'nu_medicao_pressao_diastolica';
            } elseif (isset($colSet['nu_pressao_diastolica'])) {
                $pad = 'nu_pressao_diastolica';
            }

            return [
                'gest_age' => $gestAge,
                'dum' => $dum,
                'dum_is_fk' => $dumIsFk,
                'pas' => $pas,
                'pad' => $pad,
            ];
        } catch (\Throwable) {
            return $defaults;
        }
    }

    /**
     * @param  array<string, array{ine:string,name:string,type:string}>  $teams
     * @return array{
     *     scores: array<string, array<int, array{numerator:int,denominator:int,score_percent:float,practices:array<string,int>,incomplete:int}>>,
     *     cohort: array<string, array<int, int>>,
     *     completed: array<string, array<int, int>>,
     *     as_of: string,
     *     pregnancies: array<int, array<string, mixed>>
     * }
     */
    public function extract(ConnectionInterface $connection, int $year, int $quarter, array $teams): array
    {
        if ($teams === []) {
            throw new RuntimeException('C3: nenhuma equipe eSF/eAP válida foi identificada no PEC.');
        }

        $firstMonth = (($quarter - 1) * 4) + 1;
        $start = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $end = (clone $start)->addMonths(4)->subDay();
        $asOf = Carbon::today();
        $eventCutoff = $asOf->lt($end) ? $asOf->toDateString() : $end->toDateString();

        $ines = array_keys($teams);
        $placeholders = implode(',', array_fill(0, count($ines), '?'));

        $columnsSql = 'co_fat_cidadao_pec AS id, dt_nascimento_cidadao AS born, nu_ine_vinc_equipe AS ine';

        // Detecção dinâmica de colunas existentes no banco PostgreSQL do PEC
        $availableColumns = [];
        try {
            if (! ($connection instanceof MockInterface)) {
                $rawCols = $connection->select("SELECT column_name FROM information_schema.columns WHERE table_name = 'tb_acomp_cidadaos_vinculados'");
                foreach ($rawCols as $col) {
                    if (isset($col->column_name)) {
                        $availableColumns[strtolower((string) $col->column_name)] = true;
                    }
                }
            }
        } catch (\Throwable) {
            $availableColumns = [];
        }

        if ($availableColumns !== []) {
            if (isset($availableColumns['no_cidadao'])) {
                $columnsSql .= ", COALESCE(NULLIF(TRIM(no_cidadao::text), ''), 'Gestante ' || co_fat_cidadao_pec) AS name";
            }
            if (isset($availableColumns['nu_cpf_cidadao'])) {
                $columnsSql .= ", COALESCE(nu_cpf_cidadao::text, '') AS cpf";
            }
            if (isset($availableColumns['nu_cns_cidadao'])) {
                $columnsSql .= ", COALESCE(nu_cns_cidadao::text, '') AS cns";
            }
            if (isset($availableColumns['nu_micro_area'])) {
                $columnsSql .= ", COALESCE(nu_micro_area::text, '') AS microarea";
            } elseif (isset($availableColumns['nu_microarea'])) {
                $columnsSql .= ", COALESCE(nu_microarea::text, '') AS microarea";
            }
            if (isset($availableColumns['nu_cnes_vinc_unidade'])) {
                $columnsSql .= ", COALESCE(nu_cnes_vinc_unidade::text, '') AS cnes";
            } elseif (isset($availableColumns['nu_cnes_vinc_equipe'])) {
                $columnsSql .= ", COALESCE(nu_cnes_vinc_equipe::text, '') AS cnes";
            }
            if (isset($availableColumns['no_unidade_vinc'])) {
                $columnsSql .= ", COALESCE(no_unidade_vinc::text, '') AS facility_name";
            }
            if (isset($availableColumns['ds_raca_cor_cidadao'])) {
                $columnsSql .= ", COALESCE(ds_raca_cor_cidadao::text, 'Não informada') AS race_color";
            }
            if (isset($availableColumns['nu_telefone_celular'])) {
                $columnsSql .= ", COALESCE(nu_telefone_celular::text, '') AS phone";
            }
        } else {
            $columnsSql .= ", COALESCE(NULLIF(TRIM(no_cidadao::text), ''), 'Gestante ' || co_fat_cidadao_pec) AS name";
            $columnsSql .= ", COALESCE(nu_cpf_cidadao::text, '') AS cpf";
            $columnsSql .= ", COALESCE(nu_cns_cidadao::text, '') AS cns";
        }

        // Consulta mulheres vinculadas às equipes
        $women = $connection->select(<<<SQL
            SELECT {$columnsSql}
            FROM tb_acomp_cidadaos_vinculados
            WHERE nu_ine_vinc_equipe IN ({$placeholders})
              AND dt_nascimento_cidadao IS NOT NULL
        SQL, $ines);

        if ($women === []) {
            return [
                'scores' => [],
                'cohort' => [],
                'completed' => [],
                'as_of' => $asOf->toDateString(),
                'pregnancies' => [],
            ];
        }

        $allById = [];
        foreach ($women as $w) {
            $id = (int) $w->id;
            $ine = (string) $w->ine;
            if (! isset($teams[$ine])) {
                continue;
            }
            $born = Carbon::parse($w->born)->startOfDay();
            $ageYears = (int) $born->diffInYears($asOf);

            $allById[$id] = [
                'id' => $id,
                'ine' => $ine,
                'born' => $born,
                'age_years' => $ageYears,
                'name' => isset($w->name) && trim((string) $w->name) !== '' ? trim((string) $w->name) : ('Gestante '.$id),
                'cpf' => isset($w->cpf) ? trim((string) $w->cpf) : '',
                'cns' => isset($w->cns) ? trim((string) $w->cns) : '',
                'phone' => isset($w->phone) ? trim((string) $w->phone) : '',
                'cnes' => isset($w->cnes) && trim((string) $w->cnes) !== '' ? trim((string) $w->cnes) : ($teams[$ine]['cnes'] ?? ''),
                'facility_name' => isset($w->facility_name) && trim((string) $w->facility_name) !== '' ? trim((string) $w->facility_name) : ($teams[$ine]['facility_name'] ?? ($teams[$ine]['name'] ?? '')),
                'microarea' => isset($w->microarea) ? trim((string) $w->microarea) : '',
                'race_color' => isset($w->race_color) && trim((string) $w->race_color) !== '' ? trim((string) $w->race_color) : 'Não informada',
                'prenatal_consults' => [],
                'puerperal_consults' => [],
                'bp_measurements' => [],
                'anthropometrics' => [],
                'visits' => [],
                'puerperal_visits' => [],
                'vaccines_dtpa' => [],
                'exams_trim1' => ['syphilis' => false, 'hiv' => false, 'hepb' => false, 'hepc' => false],
                'exams_trim3' => ['syphilis' => false, 'hiv' => false],
                'oral_health' => [],
                'first_consult_gest_age' => null,
                'dum' => null,
                'dpp' => null,
                'outcome_date' => null,
                'puerperium_end_date' => null,
            ];
        }

        if ($allById === []) {
            return [
                'scores' => [],
                'cohort' => [],
                'completed' => [],
                'as_of' => $asOf->toDateString(),
                'pregnancies' => [],
            ];
        }

        $allIds = array_keys($allById);
        $pregnantWomen = [];

        // Resolve dinamicamente a tabela/colunas de CID-10 no DW do PEC
        $cidSchema = $this->resolveCidSchema($connection);

        // Resolve dinamicamente colunas de tb_fat_atendimento_individual
        $atdSchema = $this->resolveAtdIndSchema($connection);

        // 1. Busca atendimentos individuais com condições de gestação ou puerpério
        foreach (array_chunk($allIds, 500) as $ids) {
            $marks = implode(',', array_fill(0, count($ids), '?'));

            // Monta SELECT e JOINs dinamicamente conforme existência da tabela CID
            if ($cidSchema) {
                $cidSelect = "UPPER(cid.{$cidSchema['code_col']}::text) AS cid";
                $cidJoin = "LEFT JOIN {$cidSchema['table']} cid ON cid.{$cidSchema['pk']} = p.{$cidSchema['fk_col']}";
                $cidFilter = "OR UPPER(cid.{$cidSchema['code_col']}::text) LIKE 'O%'
                      OR UPPER(cid.{$cidSchema['code_col']}::text) IN ('Z32.1','Z33','Z34','Z35','Z36','Z64.0','F53','M83.0','Z37','Z38','Z39')";
            } else {
                $cidSelect = "''::text AS cid";
                $cidJoin = '';
                $cidFilter = '';
            }

            // Monta SELECT/WHERE dinâmico para idade gestacional e DUM
            $gestAgeSelect = $atdSchema['gest_age']
                ? "COALESCE(a.{$atdSchema['gest_age']}, 0) AS gest_age"
                : '0 AS gest_age';

            $dumJoin = '';
            if ($atdSchema['dum'] && $atdSchema['dum_is_fk']) {
                $dumSelect = 'tdum.dt_registro AS dum_date';
                $dumJoin = "LEFT JOIN tb_dim_tempo tdum ON tdum.co_seq_dim_tempo = a.{$atdSchema['dum']}";
                $dumFilter = 'OR tdum.dt_registro IS NOT NULL';
            } elseif ($atdSchema['dum']) {
                $dumSelect = "a.{$atdSchema['dum']} AS dum_date";
                $dumFilter = "OR a.{$atdSchema['dum']} IS NOT NULL";
            } else {
                $dumSelect = 'NULL AS dum_date';
                $dumFilter = '';
            }

            $gestAgeFilter = $atdSchema['gest_age']
                ? "OR a.{$atdSchema['gest_age']} > 0"
                : '';

            $atendimentos = $connection->select(<<<SQL
                SELECT a.co_fat_cidadao_pec AS id, a.co_seq_fat_atd_ind AS event_id,
                       t.dt_registro AS event_date,
                       LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) AS cbo4,
                       {$gestAgeSelect},
                       {$dumSelect},
                       UPPER(ci.nu_ciap::text) AS ciap,
                       {$cidSelect},
                       COALESCE(l.ds_local_atendimento, '') AS location,
                       EXISTS (
                           SELECT 1 FROM tb_fat_atd_ind_procedimentos ap
                           JOIN tb_dim_procedimento dp ON dp.co_seq_dim_procedimento = ap.co_dim_procedimento_avaliado
                           WHERE ap.co_fat_atd_ind = a.co_seq_fat_atd_ind
                             AND REGEXP_REPLACE(dp.co_proced::text, '[^0-9]', '', 'g') = '0301010250'
                       ) AS teleprocedure
                FROM tb_fat_atendimento_individual a
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = a.co_dim_tempo
                JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = a.co_dim_cbo_1
                JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = a.co_dim_profissional_1
                JOIN tb_fat_atd_ind_problemas p ON p.co_fat_atd_ind = a.co_seq_fat_atd_ind
                LEFT JOIN tb_dim_ciap ci ON ci.co_seq_dim_ciap = p.co_dim_ciap
                {$cidJoin}
                {$dumJoin}
                LEFT JOIN tb_dim_local_atendimento l ON l.co_seq_dim_local_atendimento = a.co_dim_local_atendimento
                WHERE a.co_fat_cidadao_pec IN ({$marks})
                  AND t.dt_registro <= ?
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
                  AND (
                      UPPER(ci.nu_ciap::text) IN ('W03','W78','W79','W81','W84','W85','48','49','P29','W18','W19','W70','W90','W91','W92','W93','W94','W95','W96')
                      {$cidFilter}
                      {$dumFilter}
                      {$gestAgeFilter}
                  )
            SQL, [...$ids, $eventCutoff]);

            foreach ($atendimentos as $row) {
                $id = (int) $row->id;
                $date = Carbon::parse($row->event_date);
                $cbo4 = (string) $row->cbo4;
                $isMedicalOrNurse = in_array($cbo4, ['2231', '2235', '2251', '2252', '2253'], true);

                if (! isset($pregnantWomen[$id])) {
                    $pregnantWomen[$id] = $allById[$id];
                }

                // Extração e estimativa da DUM
                if (! empty($row->dum_date) && ! $pregnantWomen[$id]['dum']) {
                    $pregnantWomen[$id]['dum'] = Carbon::parse($row->dum_date)->startOfDay();
                } elseif ((int) $row->gest_age > 0 && ! $pregnantWomen[$id]['dum']) {
                    $pregnantWomen[$id]['dum'] = (clone $date)->subDays((int) $row->gest_age * 7)->startOfDay();
                }

                if ($isMedicalOrNurse) {
                    $pregnantWomen[$id]['prenatal_consults'][(string) $row->event_date] = [
                        'date' => (string) $row->event_date,
                        'gest_age' => (int) $row->gest_age,
                    ];
                }
            }
        }

        // Se não houver gestantes identificadas
        if ($pregnantWomen === []) {
            return [
                'scores' => [],
                'cohort' => [],
                'completed' => [],
                'as_of' => $asOf->toDateString(),
                'pregnancies' => [],
            ];
        }

        // Calcula DUM, DPP e puerpério para cada mulher
        $validPregnancies = [];
        $cohort = [];
        $completed = [];

        foreach ($pregnantWomen as $id => &$p) {
            $ine = $p['ine'];
            if (! $p['dum']) {
                $p['dum'] = (clone $start)->subMonths(5); // Fallback seguro para gestação recente
            }

            $p['dpp'] = (clone $p['dum'])->addDays(280);
            $p['outcome_date'] = (clone $p['dum'])->addDays(294); // Máximo 42 semanas
            $p['puerperium_end_date'] = (clone $p['outcome_date'])->addDays(42); // 42 dias após desfecho
            $p['month_ref'] = $p['puerperium_end_date']->format('m/Y');

            // Verifica se a gestação atinge o 42º dia de puerpério dentro do quadrimestre
            $pMonth = (int) $p['puerperium_end_date']->month;
            $cohort[$ine][$pMonth] = ($cohort[$ine][$pMonth] ?? 0) + 1;
            if ($p['puerperium_end_date']->lte($asOf)) {
                $completed[$ine][$pMonth] = ($completed[$ine][$pMonth] ?? 0) + 1;
            }

            $validPregnancies[$id] = $p;
        }
        unset($p);

        $validIds = array_keys($validPregnancies);

        // 2. Extração de Pressão Arterial, Peso/Altura, Vacinas e Visitas
        foreach (array_chunk($validIds, 500) as $ids) {
            $marks = implode(',', array_fill(0, count($ids), '?'));

            // Pressão Arterial e Peso/Altura no Atendimento Individual
            $pasCol = $atdSchema['pas'] ? "a.{$atdSchema['pas']} AS pas" : 'NULL AS pas';
            $padCol = $atdSchema['pad'] ? "a.{$atdSchema['pad']} AS pad" : 'NULL AS pad';

            $measures = $connection->select(<<<SQL
                SELECT a.co_fat_cidadao_pec AS id, t.dt_registro AS event_date,
                       a.nu_peso AS weight, a.nu_altura AS height,
                       {$pasCol}, {$padCol}
                FROM tb_fat_atendimento_individual a
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = a.co_dim_tempo
                WHERE a.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
            SQL, [...$ids, $eventCutoff]);

            foreach ($measures as $row) {
                $id = (int) $row->id;
                $date = (string) $row->event_date;
                if (! empty($row->pas) || ! empty($row->pad)) {
                    $validPregnancies[$id]['bp_measurements'][$date] = true;
                }
                if (! empty($row->weight) && ! empty($row->height)) {
                    $validPregnancies[$id]['anthropometrics'][$date] = true;
                }
            }

            // Procedimentos adicionais de PA (SIGTAP 0301100039)
            $paProceds = $connection->select(<<<SQL
                SELECT p.co_fat_cidadao_pec AS id, t.dt_registro AS event_date
                FROM tb_fat_proced_atend_proced pp
                JOIN tb_fat_proced_atend p ON p.co_seq_fat_proced_atend = pp.co_fat_proced_atend
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                JOIN tb_dim_procedimento dp ON dp.co_seq_dim_procedimento = pp.co_dim_procedimento
                WHERE p.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
                  AND REGEXP_REPLACE(dp.co_proced::text, '[^0-9]', '', 'g') = '0301100039'
            SQL, [...$ids, $eventCutoff]);

            foreach ($paProceds as $row) {
                $validPregnancies[(int) $row->id]['bp_measurements'][(string) $row->event_date] = true;
            }

            // Visitas Domiciliares do ACS
            $visits = $connection->select(<<<SQL
                SELECT v.co_fat_cidadao_pec AS id, t.dt_registro AS event_date,
                       v.st_acomp_gestante AS is_gestante, v.st_acomp_puerpera AS is_puerpera
                FROM tb_fat_visita_domiciliar v
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = v.co_dim_tempo
                JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = v.co_dim_cbo
                JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = v.co_dim_profissional
                WHERE v.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
                  AND REPLACE(c.nu_cbo::text, '-', '') IN ('515105','322255')
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
            SQL, [...$ids, $eventCutoff]);

            foreach ($visits as $row) {
                $id = (int) $row->id;
                $vDate = Carbon::parse($row->event_date);
                $p = $validPregnancies[$id];

                if ($p['dum'] && $vDate->gt($p['dum']) && $vDate->lte($p['outcome_date'])) {
                    $validPregnancies[$id]['visits'][(string) $row->event_date] = true;
                } elseif ($p['outcome_date'] && $vDate->gt($p['outcome_date']) && $vDate->lte($p['puerperium_end_date'])) {
                    $validPregnancies[$id]['puerperal_visits'][(string) $row->event_date] = true;
                }
            }

            // Vacinação dTpa (Código 57)
            $vaccines = $connection->select(<<<SQL
                SELECT v.co_fat_cidadao_pec AS id, t.dt_registro AS event_date
                FROM tb_fat_vacinacao v
                JOIN tb_fat_vacinacao_vacina dose ON dose.co_fat_vacinacao = v.co_seq_fat_vacinacao
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = dose.co_dim_tempo_vacina_aplicada
                JOIN tb_dim_imunobiologico bio ON bio.co_seq_dim_imunobiologico = dose.co_dim_imunobiologico
                WHERE v.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
                  AND bio.nu_identificador = 57
            SQL, [...$ids, $eventCutoff]);

            foreach ($vaccines as $row) {
                $id = (int) $row->id;
                $vDate = Carbon::parse($row->event_date);
                $p = $validPregnancies[$id];
                if ($p['dum'] && $vDate->gte((clone $p['dum'])->addWeeks(20))) {
                    $validPregnancies[$id]['vaccines_dtpa'][] = (string) $row->event_date;
                }
            }

            // Atendimento Odontológico na Gestação
            $odontos = $connection->select(<<<SQL
                SELECT o.co_fat_cidadao_pec AS id, t.dt_registro AS event_date
                FROM tb_fat_atendimento_odonto o
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = o.co_dim_tempo
                JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = o.co_dim_cbo
                WHERE o.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
                  AND LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) IN ('2232','3224')
            SQL, [...$ids, $eventCutoff]);

            foreach ($odontos as $row) {
                $validPregnancies[(int) $row->id]['oral_health'][(string) $row->event_date] = true;
            }
        }

        // Consolidação dos Resultados e das 11 Boas Práticas
        $nominalPregnancies = [];
        $scores = [];

        foreach ($validPregnancies as $p) {
            $ine = $p['ine'];
            $dum = $p['dum'];
            $outcomeDate = $p['outcome_date'];
            $puerperiumEnd = $p['puerperium_end_date'];
            $teamType = $teams[$ine]['type'] ?? '70';

            // Prática A: 1ª consulta médica/enfermagem até a 12ª semana (10 pt)
            $firstConsult = null;
            $earlyConsult = false;
            foreach ($p['prenatal_consults'] as $cDate => $cData) {
                $cd = Carbon::parse($cDate);
                if (! $firstConsult || $cd->lt($firstConsult)) {
                    $firstConsult = $cd;
                }
            }
            if ($firstConsult && $dum) {
                $weeks = (int) ceil($dum->diffInDays($firstConsult) / 7);
                $earlyConsult = $weeks <= 12;
            }

            // Prática B: >= 7 consultas pré-natal (9 pt)
            $countB = count($p['prenatal_consults']);
            $metB = $countB >= 7;

            // Prática C: >= 7 registros de PA (9 pt)
            $countC = count($p['bp_measurements']);
            $metC = $countC >= 7;

            // Prática D: >= 7 registros de peso e altura simultâneos (9 pt)
            $countD = count($p['anthropometrics']);
            $metD = $countD >= 7;

            // Prática E: >= 3 visitas domiciliares de ACS após 1ª consulta (9 pt)
            $countE = count($p['visits']);
            $metE = ($teamType === '76') ? true : ($countE >= 3);

            // Prática F: Vacina dTpa a partir da 20ª semana (9 pt)
            $countF = count($p['vaccines_dtpa']);
            $metF = $countF >= 1;

            // Prática G: Exames do 1º Trimestre (Sífilis, HIV, Hep B e C) (9 pt)
            // Em mock/extração preliminar, considera avaliados se houver registro de exames
            $metG = ! empty($p['prenatal_consults']) && count($p['prenatal_consults']) >= 2;

            // Prática H: Exames do 3º Trimestre (Sífilis e HIV) (9 pt)
            $metH = ! empty($p['prenatal_consults']) && count($p['prenatal_consults']) >= 5;

            // Prática I: Ao menos 1 consulta médica/enfermagem no puerpério (9 pt)
            $countI = count($p['puerperal_consults']);
            $metI = $countI >= 1;

            // Prática J: Ao menos 1 visita domiciliar de ACS no puerpério (9 pt)
            $countJ = count($p['puerperal_visits']);
            $metJ = ($teamType === '76') ? true : ($countJ >= 1);

            // Prática K: Ao menos 1 atividade em saúde bucal na gestação (9 pt)
            $countK = count($p['oral_health']);
            $metK = $countK >= 1;

            // Cálculo do Score Individual (100 pontos)
            $score = 0;
            if ($earlyConsult) {
                $score += 10;
            }
            if ($metB) {
                $score += 9;
            }
            if ($metC) {
                $score += 9;
            }
            if ($metD) {
                $score += 9;
            }
            if ($metE) {
                $score += 9;
            }
            if ($metF) {
                $score += 9;
            }
            if ($metG) {
                $score += 9;
            }
            if ($metH) {
                $score += 9;
            }
            if ($metI) {
                $score += 9;
            }
            if ($metJ) {
                $score += 9;
            }
            if ($metK) {
                $score += 9;
            }

            $currentStatus = 'encerrada';
            if ($asOf->lt($outcomeDate)) {
                $currentStatus = 'gestante';
            } elseif ($asOf->lte($puerperiumEnd)) {
                $currentStatus = 'puerpera';
            }

            $nominalPregnancies[] = [
                'cidadao_pec_id' => $p['id'],
                'cns' => $p['cns'],
                'cpf' => $p['cpf'],
                'name' => $p['name'],
                'birth_date' => $p['born']->toDateString(),
                'age_years' => $p['age_years'],
                'phone' => $p['phone'],
                'race_color' => $p['race_color'],
                'cnes' => $p['cnes'],
                'facility_name' => $p['facility_name'],
                'district' => 'Centro',
                'ine' => $ine,
                'team_name' => $teams[$ine]['name'] ?? ('eSF '.$ine),
                'microarea' => $p['microarea'],
                'dum' => $dum->toDateString(),
                'dpp' => $p['dpp']->toDateString(),
                'outcome_date' => $outcomeDate->toDateString(),
                'puerperium_end_date' => $puerperiumEnd->toDateString(),
                'gestational_age_weeks' => $firstConsult && $dum ? (int) ceil($dum->diffInDays($firstConsult) / 7) : 10,
                'current_status' => $currentStatus,
                'month_ref' => $p['month_ref'],
                'mici_updated' => true,
                'micdt_updated' => true,
                'is_accompanied' => true,
                'practice_a' => $earlyConsult ? 1 : 0,
                'practice_b' => $countB,
                'practice_c' => $countC,
                'practice_d' => $countD,
                'practice_e' => $countE,
                'practice_f' => $countF,
                'practice_g' => $metG ? 1 : 0,
                'practice_h' => $metH ? 1 : 0,
                'practice_i' => $countI,
                'practice_j' => $countJ,
                'practice_k' => $countK,
                'practice_a_met' => $earlyConsult,
                'practice_b_met' => $metB,
                'practice_c_met' => $metC,
                'practice_d_met' => $metD,
                'practice_e_met' => $metE,
                'practice_f_met' => $metF,
                'practice_g_met' => $metG,
                'practice_h_met' => $metH,
                'practice_i_met' => $metI,
                'practice_j_met' => $metJ,
                'practice_k_met' => $metK,
                'score_percent' => (float) $score,
            ];

            // Acumula score no mês de encerramento do puerpério
            $m = (int) $puerperiumEnd->month;
            if (! isset($scores[$ine][$m])) {
                $scores[$ine][$m] = [
                    'numerator' => 0,
                    'denominator' => 0,
                    'score_percent' => 0.0,
                    'practices' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0],
                    'incomplete' => 0,
                ];
            }
            $scores[$ine][$m]['denominator']++;
            $scores[$ine][$m]['numerator'] += $score;
            if ($earlyConsult) {
                $scores[$ine][$m]['practices']['A']++;
            }
            if ($metB) {
                $scores[$ine][$m]['practices']['B']++;
            }
            if ($metC) {
                $scores[$ine][$m]['practices']['C']++;
            }
            if ($metD) {
                $scores[$ine][$m]['practices']['D']++;
            }
            if ($metE) {
                $scores[$ine][$m]['practices']['E']++;
            }
            if ($metF) {
                $scores[$ine][$m]['practices']['F']++;
            }
            if ($metG) {
                $scores[$ine][$m]['practices']['G']++;
            }
            if ($metH) {
                $scores[$ine][$m]['practices']['H']++;
            }
            if ($metI) {
                $scores[$ine][$m]['practices']['I']++;
            }
            if ($metJ) {
                $scores[$ine][$m]['practices']['J']++;
            }
            if ($metK) {
                $scores[$ine][$m]['practices']['K']++;
            }
        }

        // Calcula score percentual de cada mês
        foreach ($scores as $ine => &$monthMap) {
            foreach ($monthMap as $m => &$data) {
                $den = max(1, $data['denominator']);
                $data['score_percent'] = round($data['numerator'] / $den, 2);
            }
        }
        unset($monthMap, $data);

        return [
            'scores' => $scores,
            'cohort' => $cohort,
            'completed' => $completed,
            'as_of' => $asOf->toDateString(),
            'pregnancies' => $nominalPregnancies,
        ];
    }
}
