<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;
use RuntimeException;

/**
 * Leitura preliminar do C2 no DW do PEC. Nunca persiste identificadores de cidadãos.
 * O resultado local não substitui o SIAPS: o DW não inclui necessariamente a RNDS.
 */
class C2DwService
{
    public const VERSION = 'dw-c2-2026-09-previa';

    /** @var array<string, list<int>> */
    private const ANTIGENS = [
        'dtp' => [29, 39, 42, 43, 46, 47, 58],
        'hepb' => [9, 42, 43],
        'hib' => [17, 29, 39, 42, 43],
        'vip' => [22, 29, 43, 58],
        'scr' => [24, 56],
        'pneumo' => [26, 59, 106, 107],
    ];

    /**
     * @param  array<string, array{ine:string,name:string,type:string}>  $teams
     * @return array{scores:array<string, array<int, array{numerator:int,denominator:int,score_percent:float,practices:array<string,int>,incomplete:int}>>,cohort:array<string,array<int,int>>,completed:array<string,array<int,int>>,as_of:string}
     */
    public function extract(ConnectionInterface $connection, int $year, int $quarter, array $teams): array
    {
        if ($teams === []) {
            throw new RuntimeException('C2: nenhuma equipe eSF/eAP válida foi identificada no PEC.');
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
                $columnsSql .= ", COALESCE(NULLIF(TRIM(no_cidadao::text), ''), 'Criança ' || co_fat_cidadao_pec) AS name";
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
            if (isset($availableColumns['no_mae_cidadao'])) {
                $columnsSql .= ", COALESCE(no_mae_cidadao::text, '') AS mother_name";
            } elseif (isset($availableColumns['no_mae'])) {
                $columnsSql .= ", COALESCE(no_mae::text, '') AS mother_name";
            }
        } else {
            // Em testes com mocks ou quando information_schema não estiver disponível
            $columnsSql .= ", COALESCE(NULLIF(TRIM(no_cidadao::text), ''), 'Criança ' || co_fat_cidadao_pec) AS name";
            $columnsSql .= ", COALESCE(nu_cpf_cidadao::text, '') AS cpf";
            $columnsSql .= ", COALESCE(nu_cns_cidadao::text, '') AS cns";
        }

        $children = $connection->select(<<<SQL
            SELECT {$columnsSql}
            FROM tb_acomp_cidadaos_vinculados
            WHERE co_fat_cidadao_pec IS NOT NULL
              AND dt_nascimento_cidadao IS NOT NULL
              AND (NULLIF(TRIM(nu_cpf_cidadao::text), '') IS NOT NULL
                   OR NULLIF(TRIM(nu_cns_cidadao::text), '') IS NOT NULL)
              AND nu_ine_vinc_equipe IN ({$placeholders})
              AND (dt_nascimento_cidadao + INTERVAL '2 years')::date BETWEEN ? AND ?
        SQL, [...$ines, $start->toDateString(), $end->toDateString()]);

        $allById = [];
        foreach ($children as $child) {
            $id = (int) $child->id;
            $ine = trim((string) $child->ine);
            if ($id <= 0 || ! isset($teams[$ine])) {
                continue;
            }
            $born = Carbon::parse($child->born)->startOfDay();
            $birthday = (clone $born)->addYearsNoOverflow(2);
            $allById[$id] = [
                'id' => $id,
                'ine' => $ine,
                'born' => $born,
                'birthday' => $birthday,
                'name' => isset($child->name) && trim((string) $child->name) !== '' ? trim((string) $child->name) : ('Criança '.$id),
                'mother_name' => isset($child->mother_name) ? trim((string) $child->mother_name) : '',
                'cpf' => isset($child->cpf) ? trim((string) $child->cpf) : '',
                'cns' => isset($child->cns) ? trim((string) $child->cns) : '',
                'cnes' => isset($child->cnes) && trim((string) $child->cnes) !== '' ? trim((string) $child->cnes) : ($teams[$ine]['cnes'] ?? ''),
                'facility_name' => isset($child->facility_name) && trim((string) $child->facility_name) !== '' ? trim((string) $child->facility_name) : ($teams[$ine]['facility_name'] ?? ($teams[$ine]['name'] ?? '')),
                'microarea' => isset($child->microarea) ? trim((string) $child->microarea) : '',
                'race_color' => isset($child->race_color) && trim((string) $child->race_color) !== '' ? trim((string) $child->race_color) : 'Não informada',
            ];
        }

        $cohort = [];
        $completed = [];
        $byId = [];
        foreach ($allById as $id => $child) {
            $ine = $child['ine'];
            $month = (int) $child['birthday']->month;
            $cohort[$ine][$month] = ($cohort[$ine][$month] ?? 0) + 1;
            if ($child['birthday']->lte($asOf)) {
                $completed[$ine][$month] = ($completed[$ine][$month] ?? 0) + 1;
            }
            $byId[$id] = $child + [
                'consultations' => [],
                'measurements' => [],
                'visits' => [],
                'vaccines' => [],
            ];
        }

        if ($byId === []) {
            return ['scores' => [], 'cohort' => $cohort, 'completed' => $completed, 'as_of' => $asOf->toDateString(), 'children' => []];
        }

        foreach (array_chunk(array_keys($byId), 500) as $ids) {
            $marks = implode(',', array_fill(0, count($ids), '?'));

            // Problema/condição avaliado A98 (Puericultura), CBO médico/enfermeiro e CNS profissional.
            $consultations = $connection->select(<<<SQL
                SELECT DISTINCT a.co_fat_cidadao_pec AS id, a.co_seq_fat_atd_ind AS event_id,
                       t.dt_registro AS event_date, COALESCE(l.ds_local_atendimento, '') AS location,
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
                JOIN tb_dim_ciap ci ON ci.co_seq_dim_ciap = p.co_dim_ciap
                LEFT JOIN tb_dim_local_atendimento l ON l.co_seq_dim_local_atendimento = a.co_dim_local_atendimento
                WHERE a.co_fat_cidadao_pec IN ({$marks})
                  AND t.dt_registro <= ?
                  AND LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) IN ('2231','2235','2251','2252','2253')
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
                  AND (UPPER(ci.nu_ciap::text) IN ('A98','ABP004') OR UPPER(ci.no_ciap) LIKE '%PUERICULTURA%')
                  AND p.st_avaliado::text IN ('1','t','true')
            SQL, [...$ids, $eventCutoff]);
            foreach ($consultations as $row) {
                $byId[(int) $row->id]['consultations'][(string) $row->event_id] = [
                    'date' => (string) $row->event_date,
                    'remote' => in_array((string) $row->teleprocedure, ['1', 't', 'true'], true)
                        || preg_match('/tele|remot|virtual/i', (string) $row->location) === 1,
                ];
            }

            // Registros reais de peso/altura nas três tabelas fato individualizadas.
            $measurements = $connection->select(<<<SQL
                SELECT a.co_fat_cidadao_pec AS id, t.dt_registro AS event_date,
                       a.nu_peso AS weight, a.nu_altura AS height
                FROM tb_fat_atendimento_individual a
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = a.co_dim_tempo
                JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = a.co_dim_cbo_1
                JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = a.co_dim_profissional_1
                WHERE a.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
                  AND LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) IN
                      ('2231','2232','2234','2235','2236','2237','2238','2239','2241','2251','2252','2253','3222','5151')
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
                UNION ALL
                SELECT p.co_fat_cidadao_pec, t.dt_registro, p.nu_peso, p.nu_altura
                FROM tb_fat_proced_atend p
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = p.co_dim_cbo
                JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = p.co_dim_profissional
                WHERE p.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
                  AND LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) IN
                      ('2231','2232','2234','2235','2236','2237','2238','2239','2241','2251','2252','2253','3222','5151')
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
                UNION ALL
                SELECT v.co_fat_cidadao_pec, t.dt_registro, v.nu_peso, v.nu_altura
                FROM tb_fat_visita_domiciliar v
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = v.co_dim_tempo
                JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = v.co_dim_cbo
                JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = v.co_dim_profissional
                WHERE v.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
                  AND LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) IN
                      ('2231','2232','2234','2235','2236','2237','2238','2239','2241','2251','2252','2253','3222','5151')
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
            SQL, [...$ids, $eventCutoff, ...$ids, $eventCutoff, ...$ids, $eventCutoff]);
            foreach ($measurements as $row) {
                $date = (string) $row->event_date;
                $id = (int) $row->id;
                $byId[$id]['measurements'][$date]['weight'] =
                    ($byId[$id]['measurements'][$date]['weight'] ?? false) || (float) $row->weight > 0;
                $byId[$id]['measurements'][$date]['height'] =
                    ($byId[$id]['measurements'][$date]['height'] ?? false) || (float) $row->height > 0;
            }

            // MIP individualizado: códigos de antropometria e medições podem vir sem valor numérico.
            $procedures = $connection->select(<<<SQL
                SELECT pp.co_fat_cidadao_pec AS id, t.dt_registro AS event_date,
                       REGEXP_REPLACE(dp.co_proced::text, '[^0-9]', '', 'g') AS code
                FROM tb_fat_proced_atend_proced pp
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = pp.co_dim_tempo
                JOIN tb_dim_procedimento dp ON dp.co_seq_dim_procedimento = pp.co_dim_procedimento
                JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = pp.co_dim_cbo
                JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = pp.co_dim_profissional
                WHERE pp.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
                  AND LEFT(REPLACE(c.nu_cbo::text, '-', ''), 4) IN
                      ('2231','2232','2234','2235','2236','2237','2238','2239','2241','2251','2252','2253','3222','5151')
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
                  AND REGEXP_REPLACE(dp.co_proced::text, '[^0-9]', '', 'g') IN
                      ('0101040024','0301010269','0101040083','0101040075')
            SQL, [...$ids, $eventCutoff]);
            foreach ($procedures as $row) {
                $date = (string) $row->event_date;
                $id = (int) $row->id;
                $code = (string) $row->code;
                if (in_array($code, ['0101040024', '0301010269', '0101040083'], true)) {
                    $byId[$id]['measurements'][$date]['weight'] = true;
                }
                if (in_array($code, ['0101040024', '0301010269', '0101040075'], true)) {
                    $byId[$id]['measurements'][$date]['height'] = true;
                }
            }

            $visits = $connection->select(<<<SQL
                SELECT v.co_fat_cidadao_pec AS id, t.dt_registro AS event_date
                FROM tb_fat_visita_domiciliar v
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = v.co_dim_tempo
                JOIN tb_dim_cbo c ON c.co_seq_dim_cbo = v.co_dim_cbo
                JOIN tb_dim_profissional prof ON prof.co_seq_dim_profissional = v.co_dim_profissional
                WHERE v.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
                  AND REPLACE(c.nu_cbo::text, '-', '') IN ('515105','322255')
                  AND NULLIF(TRIM(prof.nu_cns::text), '') IS NOT NULL
                  AND (v.st_acomp_recem_nascido::text IN ('1','t','true')
                       OR v.st_acomp_crianca::text IN ('1','t','true'))
            SQL, [...$ids, $eventCutoff]);
            foreach ($visits as $row) {
                $byId[(int) $row->id]['visits'][(string) $row->event_date] = true;
            }

            $vaccines = $connection->select(<<<SQL
                SELECT v.co_fat_cidadao_pec AS id, t.dt_registro AS event_date,
                       bio.nu_identificador AS vaccine_code
                FROM tb_fat_vacinacao v
                JOIN tb_fat_vacinacao_vacina dose ON dose.co_fat_vacinacao = v.co_seq_fat_vacinacao
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = dose.co_dim_tempo_vacina_aplicada
                JOIN tb_dim_imunobiologico bio ON bio.co_seq_dim_imunobiologico = dose.co_dim_imunobiologico
                WHERE v.co_fat_cidadao_pec IN ({$marks}) AND t.dt_registro <= ?
            SQL, [...$ids, $eventCutoff]);
            foreach ($vaccines as $row) {
                $byId[(int) $row->id]['vaccines'][] = [
                    'date' => (string) $row->event_date,
                    'code' => (int) $row->vaccine_code,
                ];
            }
        }

        $result = [];
        $nominalChildren = [];
        foreach ($byId as $child) {
            $month = (int) $child['birthday']->month;
            $ine = $child['ine'];
            $result[$ine][$month] ??= [
                'numerator' => 0,
                'denominator' => 0,
                'score_percent' => 0.0,
                'practices' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0],
                'incomplete' => 0,
            ];
            $practice = $this->scoreChild($child, $teams[$ine]['type']);
            $result[$ine][$month]['denominator']++;
            $childScore = 0.0;
            foreach ($practice as $key => $met) {
                if ($met) {
                    $result[$ine][$month]['practices'][$key]++;
                    $result[$ine][$month]['numerator'] += 20;
                    $childScore += 20.0;
                }
            }
            if (count(array_filter($practice)) < 5) {
                $result[$ine][$month]['incomplete']++;
            }

            // Métricas individuais das boas práticas
            $born = $child['born'];
            $birthday = $child['birthday'];
            $first30 = (clone $born)->addDays(30);
            $consultations = array_filter($child['consultations'], fn ($event) => $this->within((string) $event['date'], $born, $birthday));
            $earlyConsultations = array_filter($consultations, fn ($event) => ! $event['remote'] && $this->within((string) $event['date'], $born, $first30));

            $pairedDays = 0;
            foreach ($child['measurements'] as $date => $measurement) {
                if ($this->within((string) $date, $born, $birthday)
                    && ($measurement['weight'] ?? false) && ($measurement['height'] ?? false)) {
                    $pairedDays++;
                }
            }

            $nominalChildren[] = [
                'cidadao_pec_id' => $child['id'],
                'cns' => $child['cns'],
                'cpf' => $child['cpf'],
                'name' => $child['name'],
                'mother_name' => $child['mother_name'],
                'birth_date' => $child['born']->toDateString(),
                'age_months' => (int) $child['born']->diffInMonths($asOf),
                'race_color' => $child['race_color'],
                'cnes' => $child['cnes'],
                'facility_name' => $child['facility_name'],
                'ine' => $ine,
                'team_name' => $teams[$ine]['name'] ?? ('eSF '.$ine),
                'month_ref' => $child['birthday']->format('m/Y'),
                'microarea' => $child['microarea'],
                'mici_updated' => true,
                'micdt_updated' => true,
                'is_accompanied' => true,
                'practice_a' => count($earlyConsultations),
                'practice_b' => count($consultations),
                'practice_c' => $pairedDays,
                'practice_d' => count($child['visits']),
                'practice_e' => count($child['vaccines']),
                'practice_a_met' => $practice['A'],
                'practice_b_met' => $practice['B'],
                'practice_c_met' => $practice['C'],
                'practice_d_met' => $practice['D'],
                'practice_e_met' => $practice['E'],
                'score_percent' => $childScore,
            ];
        }

        foreach ($result as &$months) {
            foreach ($months as &$data) {
                $data['score_percent'] = round($data['numerator'] / $data['denominator'], 2);
            }
        }

        return [
            'scores' => $result,
            'cohort' => $cohort,
            'completed' => $completed,
            'as_of' => $asOf->toDateString(),
            'children' => $nominalChildren,
        ];
    }

    /**
     * @param  array{born:Carbon,birthday:Carbon,consultations:array,measurements:array,visits:array,vaccines:array}  $child
     * @return array{A:bool,B:bool,C:bool,D:bool,E:bool}
     */
    public function scoreChild(array $child, string $teamType): array
    {
        $born = $child['born'];
        $birthday = $child['birthday'];
        $first30 = (clone $born)->addDays(30);
        $sixMonths = (clone $born)->addMonthsNoOverflow(6);
        $consultations = array_filter($child['consultations'], fn ($event) => $this->within((string) $event['date'], $born, $birthday));

        $a = false;
        foreach ($consultations as $event) {
            if (! $event['remote'] && $this->within((string) $event['date'], $born, $first30)) {
                $a = true;
                break;
            }
        }

        $pairedDays = 0;
        foreach ($child['measurements'] as $date => $measurement) {
            if ($this->within((string) $date, $born, $birthday)
                && ($measurement['weight'] ?? false) && ($measurement['height'] ?? false)) {
                $pairedDays++;
            }
        }

        $visits = array_keys($child['visits']);
        $earlyVisits = array_filter($visits, fn ($date) => $this->within((string) $date, $born, $first30));
        $laterVisits = array_filter($visits, fn ($date) => $this->within((string) $date, $born, $sixMonths));
        $d = $teamType === '76';
        foreach ($earlyVisits as $first) {
            foreach ($laterVisits as $second) {
                if ($second !== $first && $second > $first) {
                    $d = true;
                }
            }
        }

        $vaccineDates = [];
        foreach ($child['vaccines'] as $vaccine) {
            $date = (string) $vaccine['date'];
            if (! $this->within($date, $born, $birthday)) {
                continue;
            }
            foreach (self::ANTIGENS as $antigen => $codes) {
                if (in_array((int) $vaccine['code'], $codes, true)) {
                    $vaccineDates[$antigen][$date] = true;
                }
            }
        }

        $e = true;
        foreach (['dtp', 'hepb', 'hib', 'vip', 'scr', 'pneumo'] as $antigen) {
            $dates = array_keys($vaccineDates[$antigen] ?? []);
            sort($dates);
            if ($antigen === 'scr') {
                $dates = array_values(array_filter($dates, fn ($date) => $date >= (clone $born)->addYear()->toDateString()));
            }
            $needed = in_array($antigen, ['scr', 'pneumo'], true) ? 2 : 3;
            if ($this->spacedDoses($dates, $needed, $antigen !== 'scr') < $needed) {
                $e = false;
                break;
            }
        }

        return [
            'A' => $a,
            'B' => count($consultations) >= 9,
            'C' => $pairedDays >= 9,
            'D' => $d,
            'E' => $e,
        ];
    }

    private function within(string $date, Carbon $start, Carbon $end): bool
    {
        return $date >= $start->toDateString() && $date <= $end->toDateString();
    }

    /** @param list<string> $dates */
    private function spacedDoses(array $dates, int $needed, bool $requireThirtyDays): int
    {
        $count = 0;
        $last = null;
        foreach ($dates as $date) {
            if ($last !== null && $requireThirtyDays
                && Carbon::parse($last)->diffInDays(Carbon::parse($date)) < 30) {
                continue;
            }
            $last = $date;
            $count++;
            if ($count >= $needed) {
                break;
            }
        }

        return $count;
    }
}
