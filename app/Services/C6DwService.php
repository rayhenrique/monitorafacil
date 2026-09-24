<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

/**
 * Extração do Indicador C6 (Cuidado da Pessoa Idosa na APS) no DW do PEC.
 *
 * Baseado estritamente na Nota Metodológica C6 e na Nota Técnica 06/2025.
 * População: todas as pessoas com idade igual ou superior a 60 anos (>= 60 anos) vinculadas às equipes.
 * Avalia as 4 boas práticas oficiais de cuidado (A a D) somando 100 pontos (25 pts cada):
 *  - (A) [25 pts] Consulta médica ou de enfermagem nos últimos 12 meses
 *  - (B) [25 pts] Registro simultâneo de Peso e Altura no mesmo dia nos últimos 12 meses (antropometria)
 *  - (C) [25 pts] Ao menos 2 visitas domiciliares por ACS/TACS com intervalo >= 30 dias nos últimos 12 meses (eAP tipo 76 normalizada)
 *  - (D) [25 pts] Ao menos 1 dose da vacina contra Influenza nos últimos 12 meses (imunobiológicos 33 e 77)
 */
class C6DwService
{
    public const VERSION = 'dw-c6-2026-09-normative-v1.0';

    private const CHUNK_SIZE = 500;

    // Códigos SIGTAP de Procedimentos
    private const SIGTAP_ANTROPOMETRIA = ['0101040024'];
    private const SIGTAP_PESO = ['0101040083'];
    private const SIGTAP_ALTURA = ['0101040075'];
    private const SIGTAP_CONSULTA = ['0301010064', '0301010030', '0301010250'];

    // CBOs habilitados para Consultas (A) - Médicos e Enfermeiros
    private const CBOS_CONSULTA_MEDICO = ['2251', '2252', '2253', '2231'];
    private const CBOS_CONSULTA_ENFERMEIRO = ['2235'];

    // CBOs habilitados para Antropometria (B) - Quadro 03 da Nota C6 e Nota de Rodapé 4
    private const CBOS_ANTROPOMETRIA = [
        '2251', '2252', '2253', '2231', // Médicos
        '2235',                         // Enfermeiros
        '3222',                         // Técnicos/Auxiliares de Enfermagem e TACS (3222-55)
        '5151',                         // ACS (5151-05)
        '2232',                         // Cirurgiões-dentistas
        '2234',                         // Farmacêuticos
        '2236',                         // Fisioterapeutas
        '2238',                         // Fonoaudiólogos
        '2237',                         // Nutricionistas
        '2241',                         // Profissionais de Educação Física
        '2239',                         // Terapeutas Ocupacionais, Ortoptistas e Psicomotricistas
    ];

    // CBOs habilitados para Visitas Domiciliares (C)
    private const CBOS_ACS = ['515105', '5151-05', '322255', '3222-55'];

    // Códigos de Imunobiológicos de Influenza (D)
    private const VACCINE_INFLUENZA_CODES = ['33', '77'];

    /**
     * @param  array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>  $teams
     * @return array<string, mixed>
     */
    public function extract(ConnectionInterface $connection, int $year, int $quarter, array $teams): array
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        if ($teams === []) {
            throw new RuntimeException('C6: nenhuma equipe eSF/eAP válida foi identificada no PEC.');
        }

        $firstMonth = (($quarter - 1) * 4) + 1;
        $start = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $end = (clone $start)->addMonths(4)->subDay()->endOfDay();
        $asOf = Carbon::today();
        $evalDate = $asOf->lt($end) ? $asOf->copy()->endOfDay() : $end;

        // Janela regulamentar de acompanhamento: 12 meses (365 dias) para todas as 4 práticas
        $twelveMonthsStart = (clone $evalDate)->subMonths(12)->startOfDay();

        // 1. Carrega pessoas idosas (idade >= 60 anos) vinculadas às equipes
        $elders = $this->loadLinkedElders($connection, $teams, $evalDate);
        if ($elders === []) {
            return $this->emptyResult($asOf);
        }

        // 2. Carrega eventos clínicos das 4 boas práticas (A a D)
        $this->loadClinicalEvents($connection, $elders, $twelveMonthsStart, $evalDate);

        // 3. Calcula pontuações individuais e consolida por equipe
        return $this->compileResults($elders, $teams, $year, $quarter, $asOf, $evalDate);
    }

    /**
     * Carrega cidadãos vinculados às equipes com idade >= 60 anos na data de avaliação.
     *
     * @param  array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>  $teams
     * @return array<int, array<string, mixed>>
     */
    private function loadLinkedElders(ConnectionInterface $connection, array $teams, Carbon $evalDate): array
    {
        $hasLinkedTable = (bool) $connection->selectOne(
            "SELECT 1 FROM information_schema.tables WHERE table_name = 'tb_acomp_cidadaos_vinculados' LIMIT 1"
        );

        if (! $hasLinkedTable) {
            return [];
        }

        // Data limite de nascimento: ter 60 anos completos na data avaliada
        $cutoffBirthDate = (clone $evalDate)->subYears(60)->toDateString();

        $columns = $this->columnList($connection, 'tb_acomp_cidadaos_vinculados');
        $idExpr = isset($columns['co_fat_cidadao_pec'])
            ? 'co_fat_cidadao_pec'
            : (isset($columns['co_cidadao']) ? 'co_cidadao' : 'co_seq_acomp_cidadaos_vinc');

        $select = [
            "{$idExpr} AS id",
            'nu_ine_vinc_equipe AS ine',
            'dt_nascimento_cidadao AS born',
            'no_cidadao AS name',
            "COALESCE(nu_cpf_cidadao::text, '') AS cpf",
            "COALESCE(nu_cns_cidadao::text, '') AS cns",
        ];

        $optional = [
            'nu_telefone_celular' => ['phone', "COALESCE(nu_telefone_celular::text, '')"],
            'nu_micro_area_domicilio' => ['microarea', "COALESCE(nu_micro_area_domicilio::text, '')"],
            'nu_micro_area_tb_cidadao' => ['microarea', "COALESCE(nu_micro_area_tb_cidadao::text, '')"],
            'nu_cnes_vinc_equipe' => ['cnes', "COALESCE(nu_cnes_vinc_equipe::text, '')"],
            'no_equipe_vinc_equipe' => ['team_name', "COALESCE(no_equipe_vinc_equipe::text, '')"],
            'no_raca_cor' => ['race_color', "COALESCE(no_raca_cor::text, '')"],
            'no_bairro_tb_cidadao' => ['district', "COALESCE(no_bairro_tb_cidadao::text, '')"],
            'no_bairro_domicilio' => ['district', "COALESCE(no_bairro_domicilio::text, '')"],
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
            'SELECT %s FROM tb_acomp_cidadaos_vinculados 
             WHERE nu_ine_vinc_equipe IN (%s) 
               AND dt_nascimento_cidadao IS NOT NULL 
               AND dt_nascimento_cidadao <= ?',
            implode(', ', $select),
            $this->marks(count($ines))
        ), array_merge($ines, [$cutoffBirthDate]));

        $elders = [];
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $ine = trim((string) $row->ine);
            $name = trim((string) ($row->name ?? ''));
            $cpf = trim((string) ($row->cpf ?? ''));
            $cns = trim((string) ($row->cns ?? ''));
            $hasValidCpf = strlen((string) preg_replace('/\D+/', '', $cpf)) === 11;
            $hasValidCns = strlen((string) preg_replace('/\D+/', '', $cns)) === 15;

            // A nota técnica exige nome, nascimento e ao menos CPF ou CNS com formato válido.
            if ($id <= 0 || ! isset($teams[$ine]) || $name === '' || (! $hasValidCpf && ! $hasValidCns)) {
                continue;
            }

            $bornStr = substr((string) $row->born, 0, 10);
            $bornCarbon = Carbon::parse($bornStr)->startOfDay();
            $ageYears = (int) $bornCarbon->diffInYears($evalDate);

            if ($ageYears < 60) {
                continue;
            }

            $elders[$id] = [
                'id' => $id,
                'ine' => $ine,
                'born' => $bornCarbon,
                'age_years' => $ageYears,
                'name' => $name,
                'cpf' => $cpf,
                'cns' => $cns,
                'phone' => trim((string) ($row->phone ?? '')),
                'cnes' => trim((string) ($row->cnes ?? ($teams[$ine]['cnes'] ?? ''))),
                'facility_name' => trim((string) ($teams[$ine]['facility_name'] ?? $teams[$ine]['name'] ?? '')),
                'district' => trim((string) ($row->district ?? 'Sede')),
                'microarea' => trim((string) ($row->microarea ?? '')),
                'race_color' => trim((string) ($row->race_color ?? '')) ?: 'Não informada',
                'team_type' => $teams[$ine]['type'] ?? '70',
                // Inicialização das 4 boas práticas
                'practice_a_count' => 0,
                'practice_a_met' => false,
                'last_consultation_date' => null,
                'practice_b_count' => 0,
                'practice_b_met' => false,
                'last_anthropometry_date' => null,
                'last_weight' => null,
                'last_height' => null,
                'practice_c_count' => 0,
                'practice_c_met' => false,
                'last_visit_date' => null,
                'practice_d_count' => 0,
                'practice_d_met' => false,
                'last_vaccine_date' => null,
                'last_vaccine_name' => null,
                'score_percent' => 0.0,
            ];
        }

        return $elders;
    }

    /**
     * Carrega eventos clínicos das 4 boas práticas no intervalo de 12 meses.
     *
     * @param  array<int, array<string, mixed>>  $elders
     */
    private function loadClinicalEvents(
        ConnectionInterface $connection,
        array &$elders,
        Carbon $twelveMonthsStart,
        Carbon $evalDate
    ): void {
        $citizenIds = array_keys($elders);
        if ($citizenIds === []) {
            return;
        }

        foreach (array_chunk($citizenIds, self::CHUNK_SIZE) as $chunk) {
            $chunkMarks = $this->marks(count($chunk));

            // =========================================================================
            // PRÁTICA A: Consulta médica ou de enfermagem nos últimos 12 meses (25 pts)
            // =========================================================================
            $consultationSql = <<<SQL
                SELECT a.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_atendimento,
                       cbo1.nu_cbo AS cbo1,
                       cbo2.nu_cbo AS cbo2
                FROM tb_fat_atendimento_individual a
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = a.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo1 ON cbo1.co_seq_dim_cbo = a.co_dim_cbo_1
                LEFT JOIN tb_dim_cbo cbo2 ON cbo2.co_seq_dim_cbo = a.co_dim_cbo_2
                WHERE a.co_fat_cidadao_pec IN ({$chunkMarks})
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                ORDER BY t.dt_registro DESC
            SQL;

            $cRows = $connection->select($consultationSql, array_merge($chunk, [
                $twelveMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($cRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($elders[$cId])) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                $isDoctorOrNurse = $this->isCboDoctorOrNurse($cbo1) || $this->isCboDoctorOrNurse($cbo2);

                if (! $isDoctorOrNurse) {
                    continue;
                }

                $elders[$cId]['practice_a_count']++;
                $elders[$cId]['practice_a_met'] = true;
                if (! $elders[$cId]['last_consultation_date']) {
                    $elders[$cId]['last_consultation_date'] = Carbon::parse($r->dt_atendimento);
                }
            }

            // =========================================================================
            // PRÁTICA B: Antropometria (Peso e Altura no mesmo dia) nos últimos 12 meses (25 pts)
            // =========================================================================
            $antropoSql = <<<SQL
                SELECT a.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_atendimento,
                       a.nu_peso,
                       a.nu_altura
                FROM tb_fat_atendimento_individual a
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = a.co_dim_tempo
                WHERE a.co_fat_cidadao_pec IN ({$chunkMarks})
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                  AND a.nu_peso > 0 AND a.nu_altura > 0
                ORDER BY t.dt_registro DESC
            SQL;

            $antropoRows = $connection->select($antropoSql, array_merge($chunk, [
                $twelveMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($antropoRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($elders[$cId])) {
                    continue;
                }

                $elders[$cId]['practice_b_count']++;
                $elders[$cId]['practice_b_met'] = true;
                if (! $elders[$cId]['last_anthropometry_date']) {
                    $elders[$cId]['last_anthropometry_date'] = Carbon::parse($r->dt_atendimento);
                    $elders[$cId]['last_weight'] = round((float) $r->nu_peso, 2);
                    $elders[$cId]['last_height'] = round((float) $r->nu_altura, 2);
                }
            }

            // Antropometria via procedimentos no atendimento individual (tb_fat_atd_ind_procedimentos)
            $indProcSql = <<<SQL
                SELECT p.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_procedimento,
                       dp_av.co_proced AS proced_avaliado,
                       dp_sol.co_proced AS proced_solicitado
                FROM tb_fat_atd_ind_procedimentos p
                LEFT JOIN tb_dim_procedimento dp_av ON dp_av.co_seq_dim_procedimento = p.co_dim_procedimento_avaliado
                LEFT JOIN tb_dim_procedimento dp_sol ON dp_sol.co_seq_dim_procedimento = p.co_dim_procedimento_solicitado
                LEFT JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                WHERE p.co_fat_cidadao_pec IN ({$chunkMarks})
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                  AND (
                      dp_av.co_proced = '0101040024'
                      OR dp_sol.co_proced = '0101040024'
                  )
                ORDER BY t.dt_registro DESC
            SQL;

            $indProcRows = $connection->select($indProcSql, array_merge($chunk, [
                $twelveMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($indProcRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($elders[$cId]) || empty($r->dt_procedimento)) {
                    continue;
                }

                $dt = Carbon::parse($r->dt_procedimento);
                $elders[$cId]['practice_b_count']++;
                $elders[$cId]['practice_b_met'] = true;
                if (! $elders[$cId]['last_anthropometry_date'] || $dt->gt($elders[$cId]['last_anthropometry_date'])) {
                    $elders[$cId]['last_anthropometry_date'] = $dt;
                }
            }

            // =========================================================================
            // PRÁTICA C: Visitas Domiciliares de ACS/TACS nos últimos 12 meses (25 pts)
            // Pelo menos 2 visitas com intervalo >= 30 dias entre si.
            // =========================================================================
            $visitSql = <<<SQL
                SELECT v.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_visita,
                       cbo.nu_cbo
                FROM tb_fat_visita_domiciliar v
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = v.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo ON cbo.co_seq_dim_cbo = v.co_dim_cbo
                WHERE v.co_fat_cidadao_pec IN ({$chunkMarks})
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                ORDER BY v.co_fat_cidadao_pec, t.dt_registro ASC
            SQL;

            $visitRows = $connection->select($visitSql, array_merge($chunk, [
                $twelveMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            $visitsByCitizen = [];
            foreach ($visitRows as $r) {
                $cId = (int) $r->cidadao_id;
                $cbo = trim((string) ($r->nu_cbo ?? ''));
                if ($this->isCboAcsOrTacs($cbo)) {
                    $visitsByCitizen[$cId][] = Carbon::parse($r->dt_visita);
                }
            }

            foreach ($visitsByCitizen as $cId => $dates) {
                if (! isset($elders[$cId])) {
                    continue;
                }

                $countVisits = count($dates);
                $elders[$cId]['practice_c_count'] = $countVisits;
                $elders[$cId]['last_visit_date'] = end($dates);

                if ($countVisits >= 2) {
                    for ($i = 0; $i < $countVisits - 1; $i++) {
                        for ($j = $i + 1; $j < $countVisits; $j++) {
                            if ($dates[$i]->diffInDays($dates[$j]) >= 30) {
                                $elders[$cId]['practice_c_met'] = true;
                                break 2;
                            }
                        }
                    }
                }
            }

            // =========================================================================
            // PRÁTICA D: Vacina contra Influenza nos últimos 12 meses (25 pts)
            // Imunobiológicos códigos 33 e 77 ou nome contendo influenza
            // =========================================================================
            $vaccineSql = <<<SQL
                SELECT v.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_vacina,
                       bio.nu_identificador AS vaccine_code,
                       bio.no_imunobiologico AS vaccine_name
                FROM tb_fat_vacinacao v
                JOIN tb_fat_vacinacao_vacina dose ON dose.co_fat_vacinacao = v.co_seq_fat_vacinacao
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = dose.co_dim_tempo_vacina_aplicada
                JOIN tb_dim_imunobiologico bio ON bio.co_seq_dim_imunobiologico = dose.co_dim_imunobiologico
                WHERE v.co_fat_cidadao_pec IN ({$chunkMarks})
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                  AND (
                      bio.nu_identificador IN ('33', '77')
                      OR LOWER(bio.no_imunobiologico) LIKE '%influenza%'
                  )
                ORDER BY t.dt_registro DESC
            SQL;

            $vaccineRows = $connection->select($vaccineSql, array_merge($chunk, [
                $twelveMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($vaccineRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($elders[$cId])) {
                    continue;
                }

                $elders[$cId]['practice_d_count']++;
                $elders[$cId]['practice_d_met'] = true;
                if (! $elders[$cId]['last_vaccine_date']) {
                    $elders[$cId]['last_vaccine_date'] = Carbon::parse($r->dt_vacina);
                    $elders[$cId]['last_vaccine_name'] = trim((string) ($r->vaccine_name ?? 'Vacina influenza trivalente'));
                }
            }
        }
    }

    /**
     * Consolida os resultados individuais, as pontuações das equipes e as taxas municipais.
     *
     * @param  array<int, array<string, mixed>>  $elders
     * @param  array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>  $teams
     * @return array<string, mixed>
     */
    private function compileResults(
        array $elders,
        array $teams,
        int $year,
        int $quarter,
        Carbon $asOf,
        Carbon $evalDate
    ): array {
        $nominalList = [];
        $teamScores = [];
        $cohortCounts = [];

        $practicesCitywide = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
        ];

        foreach ($elders as $d) {
            $ine = $d['ine'];
            $isEap = ($d['team_type'] ?? '70') === '76';

            // Pontos oficiais conforme Quadro 01 da Nota Metodológica C6:
            // Cada boa prática pontua exatamente 25 pontos
            $ptsA = $d['practice_a_met'] ? 25.0 : 0.0;
            $ptsB = $d['practice_b_met'] ? 25.0 : 0.0;
            $ptsC = $d['practice_c_met'] ? 25.0 : 0.0;
            $ptsD = $d['practice_d_met'] ? 25.0 : 0.0;

            if ($isEap) {
                // eAP tipo 76: A boa prática (C) não é condicionante de pontuação (Portaria GM/MS nº 3.493/2024 e PRC GM/MS nº 02/2017).
                // Pontuação calculada sobre 75 pontos e normalizada para a base 100:
                $rawScore = $ptsA + $ptsB + $ptsD;
                $score = round(min(100.0, $rawScore * (100.0 / 75.0)), 2);
            } else {
                $score = round($ptsA + $ptsB + $ptsC + $ptsD, 2);
            }

            if ($d['practice_a_met']) $practicesCitywide['A']++;
            if ($d['practice_b_met']) $practicesCitywide['B']++;
            if ($d['practice_c_met']) $practicesCitywide['C']++;
            if ($d['practice_d_met']) $practicesCitywide['D']++;

            $nominalRecord = [
                'cidadao_pec_id' => $d['id'],
                'cns' => $d['cns'],
                'cpf' => $d['cpf'],
                'name' => $d['name'],
                'social_name' => null,
                'birth_date' => $d['born']->toDateString(),
                'age_years' => $d['age_years'],
                'phone' => $d['phone'],
                'race_color' => $d['race_color'],
                'cnes' => ! empty($teams[$ine]['cnes']) ? $teams[$ine]['cnes'] : ($d['cnes'] ?: '—'),
                'facility_name' => ! empty($teams[$ine]['facility_name']) ? $teams[$ine]['facility_name'] : ($d['facility_name'] ?: 'Unidade Básica de Saúde'),
                'district' => $d['district'] ?: 'Sede',
                'ine' => $ine,
                'team_name' => $teams[$ine]['name'] ?? 'Equipe APS',
                'team_type' => $d['team_type'] ?? '70',
                'professional_cns' => null,
                'professional_name' => null,
                'microarea' => $d['microarea'] ?: '—',
                'month_ref' => sprintf('%02d/%d', $asOf->month, $year),
                'mici_updated' => true,
                'is_accompanied' => $score >= 50.0,
                'practice_a' => $d['practice_a_count'],
                'practice_a_met' => $d['practice_a_met'],
                'last_consultation_date' => $d['last_consultation_date']?->toDateString(),
                'practice_b' => $d['practice_b_count'],
                'practice_b_met' => $d['practice_b_met'],
                'last_anthropometry_date' => $d['last_anthropometry_date']?->toDateString(),
                'last_weight' => $d['last_weight'],
                'last_height' => $d['last_height'],
                'practice_c' => $d['practice_c_count'],
                'practice_c_met' => $d['practice_c_met'],
                'last_visit_date' => $d['last_visit_date']?->toDateString(),
                'practice_d' => $d['practice_d_count'],
                'practice_d_met' => $d['practice_d_met'],
                'last_vaccine_date' => $d['last_vaccine_date']?->toDateString(),
                'last_vaccine_name' => $d['last_vaccine_name'],
                'score_percent' => $score,
            ];

            $nominalList[] = $nominalRecord;

            if (! isset($teamScores[$ine])) {
                $teamScores[$ine] = ['scores' => [], 'total' => 0];
            }
            $teamScores[$ine]['scores'][] = $score;
            $teamScores[$ine]['total']++;

            $cohortCounts[$ine] = ($cohortCounts[$ine] ?? 0) + 1;
        }

        // Consolidação por equipe
        $scores = [];
        $monthsInQuarter = match ($quarter) {
            1 => [1, 2, 3, 4],
            2 => [5, 6, 7, 8],
            default => [9, 10, 11, 12],
        };

        foreach ($teams as $ine => $t) {
            $hasData = isset($teamScores[$ine]) && $teamScores[$ine]['total'] > 0;
            $teamTotal = $hasData ? $teamScores[$ine]['total'] : 0;
            $avgScore = $hasData ? round(array_sum($teamScores[$ine]['scores']) / $teamTotal, 2) : 0.0;
            $level = FamilyHealthService::calculatePerformanceLevel('c6', $avgScore);

            $monthlyCounts = [];
            foreach ($monthsInQuarter as $m) {
                $monthlyCounts[$m] = $teamTotal;
            }

            $scores[$ine] = [
                'ine' => $ine,
                'team_name' => $t['name'],
                'team_type' => $t['type'],
                'cnes' => $t['cnes'] ?? '',
                'facility_name' => $t['facility_name'] ?? '',
                'cohort_total' => $teamTotal,
                'score_percent' => $avgScore,
                'performance_level' => $level,
                'monthly_counts' => $monthlyCounts,
            ];
        }

        return [
            'scores' => $scores,
            'elders' => $nominalList,
            'cohort' => $cohortCounts,
            'practices_citywide' => $practicesCitywide,
            'total_elders' => count($nominalList),
            'as_of' => $asOf->toDateString(),
            'eval_date' => $evalDate->toDateString(),
        ];
    }

    private function isCboDoctorOrNurse(string $cbo): bool
    {
        $cboClean = str_replace('-', '', trim($cbo));
        $prefix = substr($cboClean, 0, 4);

        return in_array($prefix, self::CBOS_CONSULTA_MEDICO, true)
            || in_array($prefix, self::CBOS_CONSULTA_ENFERMEIRO, true);
    }

    private function isCboAcsOrTacs(string $cbo): bool
    {
        $cboClean = str_replace('-', '', trim($cbo));

        return in_array($cboClean, self::CBOS_ACS, true)
            || str_starts_with($cboClean, '5151')
            || str_starts_with($cboClean, '322255');
    }

    /**
     * @return array<string, bool>
     */
    private function columnList(ConnectionInterface $connection, string $table): array
    {
        $cols = $connection->select(
            "SELECT column_name FROM information_schema.columns WHERE table_name = ? AND table_schema = 'public'",
            [$table]
        );
        $map = [];
        foreach ($cols as $c) {
            $map[(string) $c->column_name] = true;
        }

        return $map;
    }

    private function marks(int $count): string
    {
        return implode(',', array_fill(0, max(1, $count), '?'));
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(Carbon $asOf): array
    {
        return [
            'scores' => [],
            'elders' => [],
            'cohort' => [],
            'practices_citywide' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
            'total_elders' => 0,
            'as_of' => $asOf->toDateString(),
        ];
    }
}
