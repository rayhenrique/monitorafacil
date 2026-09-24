<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

/**
 * Extração do Indicador C7 (Cuidado da Mulher na Prevenção do Câncer) no DW do PEC.
 *
 * Baseado estritamente na Nota Metodológica C7 e na Nota Técnica 06/2025.
 * População: mulheres e homens trans (9 a 69 anos) vinculados às equipes eSF/eAP.
 * Exclusão: mulheres transgênero (sexo biológico feminino com identidade mulher trans).
 *
 * Avalia as 4 boas práticas clínicas normativas somando 100 pontos:
 *  - (A) [20 pts] Rastreamento do câncer do colo do útero (25 a 64 anos):
 *        Ao menos 1 exame citopatológico nos últimos 36 meses OU teste molecular DNA-HPV nos últimos 60 meses.
 *  - (B) [30 pts] Vacina contra o HPV (9 a 14 anos):
 *        Ao menos 1 dose da vacina HPV (imunobiológicos 67 ou 93).
 *  - (C) [30 pts] Saúde sexual e reprodutiva (14 a 69 anos):
 *        Ao menos 1 consulta médica ou de enfermagem nos últimos 12 meses com CIAP-2/CID-10/Procedimento elegível.
 *  - (D) [20 pts] Rastreamento do câncer de mama (50 a 69 anos):
 *        Ao menos 1 mamografia de rastreamento solicitada ou avaliada nos últimos 24 meses.
 */
class C7DwService
{
    public const VERSION = 'dw-c7-2026-09-normative-v1.0';

    private const CHUNK_SIZE = 500;

    // CBOs habilitados para Consultas e Procedimentos de Câncer - Médicos e Enfermeiros
    private const CBOS_MEDICO = ['2251', '2252', '2253', '2231'];
    private const CBOS_ENFERMEIRO = ['2235'];

    // Códigos para Prática A: Colo de Útero (janela 36 meses para citopatológico, 60 meses para teste molecular)
    private const PROCED_COLO_CITO_36M = [
        '0201020033', '02.01.02.003-3',
        '0203010086', '02.03.01.008-6',
        '0203010019', '02.03.01.001-9',
        '0201020076', '02.01.02.007-6',
        '0201020084', '02.01.02.008-4',
        'ABEX001', 'ABP022', 'ABPG010',
    ];

    private const PROCED_COLO_MOLECULAR_60M = [
        '0202100251', '02.02.10.025-1',
    ];

    // Códigos para Prática B: Vacina HPV (imunobiológicos 67 e 93)
    private const VACCINE_HPV_CODES = ['67', '93'];

    // Códigos para Prática C: Saúde Sexual e Reprodutiva (janela 12 meses)
    private const CIAP_SEXUAL_HEALTH = [
        'B25', 'W02', 'W10', 'W11', 'W12', 'W13', 'W14', 'W15', 'W79', 'W82',
        'X01', 'X02', 'X03', 'X04', 'X05', 'X06', 'X07', 'X08', 'X09', 'X10',
        'X11', 'X12', 'X13', 'X23', 'X24', 'X82', 'X89', 'Y14',
    ];

    private const CID_PREFIX_SEXUAL_HEALTH = [
        'N80', 'N91', 'N92', 'N93', 'N94', 'N95', 'N96', 'N97',
        'O03', 'O04', 'R102', 'R10.2', 'T742', 'T74.2',
        'Y05', 'Z123', 'Z12.3', 'Z124', 'Z12.4',
        'Z205', 'Z20.5', 'Z206', 'Z20.6',
        'Z30', 'Z31', 'Z320', 'Z32.0',
        'Z600', 'Z60.0', 'Z630', 'Z63.0', 'Z640', 'Z64.0',
        'Z70', 'Z717', 'Z71.7', 'Z725', 'Z72.5',
    ];

    private const PROCED_SEXUAL_HEALTH = [
        'ABP003', 'ABP022', 'ABP023',
        '0301010030', '03.01.01.003-0',
        '0301010064', '03.01.01.006-4',
        '0301010250', '03.01.01.025-0',
    ];

    // Códigos para Prática D: Rastreamento Câncer de Mama (janela 24 meses)
    private const PROCED_MAMA_24M = [
        '0204030030', '02.04.03.003-0',
        '0204030188', '02.04.03.018-8',
        'ABP023', 'ABEX010', 'ABEX011',
    ];

    /**
     * @param  array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>  $teams
     * @return array<string, mixed>
     */
    public function extract(ConnectionInterface $connection, int $year, int $quarter, array $teams): array
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        if ($teams === []) {
            throw new RuntimeException('C7: nenhuma equipe eSF/eAP válida foi identificada no PEC.');
        }

        $firstMonth = (($quarter - 1) * 4) + 1;
        $start = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $end = (clone $start)->addMonths(4)->subDay()->endOfDay();
        $asOf = Carbon::today();
        $evalDate = $asOf->lt($end) ? $asOf->copy()->endOfDay() : $end;

        // Janelas temporais normativas conforme a Nota Técnica:
        $windowColoCitoStart = (clone $evalDate)->subMonths(36)->startOfDay();
        $windowColoMolecularStart = (clone $evalDate)->subMonths(60)->startOfDay();
        $windowSexualHealthStart = (clone $evalDate)->subMonths(12)->startOfDay();
        $windowMamaStart = (clone $evalDate)->subMonths(24)->startOfDay();

        // 1. Carrega população elegível (mulheres e homens trans de 9 a 69 anos vinculados às equipes)
        $women = $this->loadEligibleWomen($connection, $teams, $evalDate);
        if ($women === []) {
            return $this->emptyResult($asOf);
        }

        // 2. Carrega eventos clínicos das 4 boas práticas (A a D)
        $this->loadClinicalEvents(
            $connection,
            $women,
            $windowColoCitoStart,
            $windowColoMolecularStart,
            $windowSexualHealthStart,
            $windowMamaStart,
            $evalDate
        );

        // 3. Calcula pontuações individuais e consolida por equipe e município
        return $this->compileResults($women, $teams, $year, $quarter, $asOf, $evalDate);
    }

    /**
     * Carrega população elegível no DW:
     * - Sexo feminino exceto mulher trans
     * - Sexo masculino com identidade homem trans
     * - Idade entre 9 e 69 anos na data avaliada
     *
     * @param  array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>  $teams
     * @return array<int, array<string, mixed>>
     */
    private function loadEligibleWomen(ConnectionInterface $connection, array $teams, Carbon $evalDate): array
    {
        $hasLinkedTable = (bool) $connection->selectOne(
            "SELECT 1 FROM information_schema.tables WHERE table_name = 'tb_acomp_cidadaos_vinculados' LIMIT 1"
        );

        if (! $hasLinkedTable) {
            return [];
        }

        // Limites de nascimento para ter entre 9 e 69 anos
        $maxBirthDate = (clone $evalDate)->subYears(9)->toDateString();
        $minBirthDate = (clone $evalDate)->subYears(70)->addDay()->toDateString();

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
            "COALESCE(no_sexo_cidadao::text, '') AS sex",
            "COALESCE(tp_identidade_genero_cidadao::text, '') AS gender",
        ];

        $optional = [
            'no_social_cidadao' => ['social_name', "COALESCE(no_social_cidadao::text, '')"],
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
               AND dt_nascimento_cidadao >= ?
               AND dt_nascimento_cidadao <= ?',
            implode(', ', $select),
            $this->marks(count($ines))
        ), array_merge($ines, [$minBirthDate, $maxBirthDate]));

        $women = [];
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $ine = trim((string) $row->ine);
            $name = trim((string) ($row->name ?? ''));
            $cpf = trim((string) ($row->cpf ?? ''));
            $cns = trim((string) ($row->cns ?? ''));
            $hasValidCpf = strlen((string) preg_replace('/\D+/', '', $cpf)) === 11;
            $hasValidCns = strlen((string) preg_replace('/\D+/', '', $cns)) === 15;

            // Exigência da NT: nome e pelo menos um documento válido
            if ($id <= 0 || ! isset($teams[$ine]) || $name === '' || (! $hasValidCpf && ! $hasValidCns)) {
                continue;
            }

            $sex = strtoupper(trim((string) ($row->sex ?? '')));
            $gender = strtoupper(trim((string) ($row->gender ?? '')));

            // Regra Seções 4.1 e 4.2 da Nota Metodológica C7:
            // 1. Sexo FEMININO: inclui a menos que tenha identidade Mulher Transgênero
            // 2. Sexo MASCULINO: inclui SOMENTE se tiver identidade Homem Transgênero
            $isFemale = str_contains($sex, 'FEM') || $sex === 'F';
            $isMale = str_contains($sex, 'MASC') || $sex === 'M';

            $isMulherTrans = str_contains($gender, 'MULHER_TRANS') || str_contains($gender, 'MULHER TRANS');
            $isHomemTrans = str_contains($gender, 'HOMEM_TRANS') || str_contains($gender, 'HOMEM TRANS');

            if ($isFemale && $isMulherTrans) {
                // Excluída da coorte normativa
                continue;
            }

            if (! $isFemale && ! ($isMale && $isHomemTrans)) {
                // Homens cisgênero e outros sem elegibilidade reprodutiva feminina não entram
                continue;
            }

            $bornStr = substr((string) $row->born, 0, 10);
            $bornCarbon = Carbon::parse($bornStr)->startOfDay();
            $ageYears = (int) $bornCarbon->diffInYears($evalDate);

            if ($ageYears < 9 || $ageYears > 69) {
                continue;
            }

            // Elegibilidade por prática conforme faixa etária oficial
            $eligibleA = ($ageYears >= 25 && $ageYears <= 64);
            $eligibleB = ($ageYears >= 9 && $ageYears <= 14);
            $eligibleC = ($ageYears >= 14 && $ageYears <= 69);
            $eligibleD = ($ageYears >= 50 && $ageYears <= 69);

            $women[$id] = [
                'id' => $id,
                'ine' => $ine,
                'born' => $bornCarbon,
                'age_years' => $ageYears,
                'name' => $name,
                'social_name' => trim((string) ($row->social_name ?? '')),
                'cpf' => $cpf,
                'cns' => $cns,
                'sex' => $sex,
                'gender_identity' => $gender ?: ($isFemale ? 'MULHER_CIS' : 'HOMEM_TRANS'),
                'phone' => trim((string) ($row->phone ?? '')),
                'cnes' => trim((string) ($row->cnes ?? ($teams[$ine]['cnes'] ?? ''))),
                'facility_name' => trim((string) ($teams[$ine]['facility_name'] ?? $teams[$ine]['name'] ?? '')),
                'district' => trim((string) ($row->district ?? 'Sede')),
                'microarea' => trim((string) ($row->microarea ?? '')),
                'race_color' => trim((string) ($row->race_color ?? '')) ?: 'Não informada',
                'team_type' => $teams[$ine]['type'] ?? '70',

                // Elegibilidade das 4 práticas
                'eligible_practice_a' => $eligibleA,
                'practice_a_count' => 0,
                'practice_a_met' => false,
                'last_cervical_exam_date' => null,
                'last_cervical_exam_code' => null,
                'last_cervical_exam_desc' => null,

                'eligible_practice_b' => $eligibleB,
                'practice_b_count' => 0,
                'practice_b_met' => false,
                'last_hpv_vaccine_date' => null,
                'last_hpv_vaccine_code' => null,
                'last_hpv_vaccine_name' => null,

                'eligible_practice_c' => $eligibleC,
                'practice_c_count' => 0,
                'practice_c_met' => false,
                'last_sexual_health_date' => null,
                'last_sexual_health_code' => null,
                'last_sexual_health_detail' => null,

                'eligible_practice_d' => $eligibleD,
                'practice_d_count' => 0,
                'practice_d_met' => false,
                'last_mammogram_date' => null,
                'last_mammogram_code' => null,
                'last_mammogram_desc' => null,

                'score_percent' => 0.0,
                'pending_practices' => [],
            ];
        }

        return $women;
    }

    /**
     * Carrega eventos clínicos das 4 boas práticas com base nas janelas e tabelas de fatos.
     *
     * @param  array<int, array<string, mixed>>  $women
     */
    private function loadClinicalEvents(
        ConnectionInterface $connection,
        array &$women,
        Carbon $windowColoCitoStart,
        Carbon $windowColoMolecularStart,
        Carbon $windowSexualHealthStart,
        Carbon $windowMamaStart,
        Carbon $evalDate
    ): void {
        if ($women === []) {
            return;
        }

        // 1. Resolve mapeamentos de IDs de dimensão
        $citoMap = $this->resolveProcedureDimMap($connection, self::PROCED_COLO_CITO_36M);
        $molMap = $this->resolveProcedureDimMap($connection, self::PROCED_COLO_MOLECULAR_60M);
        $allColoDimIds = array_values(array_unique(array_merge(array_keys($citoMap), array_keys($molMap))));

        $hpvMap = $this->resolveHpvDimMap($connection);
        $hpvDimIds = array_keys($hpvMap);

        $ciapMap = $this->resolveCiapDimMap($connection);
        $ciapDimIds = array_keys($ciapMap);

        $cidMap = $this->resolveCidDimMap($connection);
        $cidDimIds = array_keys($cidMap);

        $sexProcMap = $this->resolveProcedureDimMap($connection, self::PROCED_SEXUAL_HEALTH);
        $sexProcDimIds = array_keys($sexProcMap);

        $mamaMap = $this->resolveProcedureDimMap($connection, self::PROCED_MAMA_24M);
        $mamaDimIds = array_keys($mamaMap);

        $evalDateStr = $evalDate->toDateString();

        // =========================================================================
        // PRÁTICA A: RASTREAMENTO DO CÂNCER DO COLO DO ÚTERO (25 a 64 anos - 20 pts)
        // =========================================================================
        if ($allColoDimIds !== []) {
            $coloIn = implode(',', $allColoDimIds);
            $w60Str = $windowColoMolecularStart->toDateString();

            // A.1: Atendimento individual procedimentos
            $coloAtdRows = $connection->select("
                SELECT p.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_procedimento,
                       p.co_dim_procedimento_avaliado AS dim_av,
                       p.co_dim_procedimento_solicitado AS dim_sol,
                       cbo1.nu_cbo AS cbo1,
                       cbo2.nu_cbo AS cbo2
                FROM tb_fat_atd_ind_procedimentos p
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo1 ON cbo1.co_seq_dim_cbo = p.co_dim_cbo_1
                LEFT JOIN tb_dim_cbo cbo2 ON cbo2.co_seq_dim_cbo = p.co_dim_cbo_2
                WHERE (p.co_dim_procedimento_avaliado IN ({$coloIn}) OR p.co_dim_procedimento_solicitado IN ({$coloIn}))
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                ORDER BY t.dt_registro DESC
            ", [$w60Str, $evalDateStr]);

            foreach ($coloAtdRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($women[$cId]) || ! $women[$cId]['eligible_practice_a']) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                if (! $this->isCboDoctorOrNurse($cbo1) && ! $this->isCboDoctorOrNurse($cbo2)) {
                    continue;
                }

                $dt = Carbon::parse($r->dt_procedimento);
                $dimAv = (int) ($r->dim_av ?? 0);
                $dimSol = (int) ($r->dim_sol ?? 0);

                $isMol = isset($molMap[$dimAv]) || isset($molMap[$dimSol]);
                $isCito = isset($citoMap[$dimAv]) || isset($citoMap[$dimSol]);

                $validMol = $isMol && $dt->gte($windowColoMolecularStart);
                $validCito = $isCito && $dt->gte($windowColoCitoStart);

                if ($validMol || $validCito) {
                    $women[$cId]['practice_a_count']++;
                    $women[$cId]['practice_a_met'] = true;
                    if (! $women[$cId]['last_cervical_exam_date'] || $dt->gt($women[$cId]['last_cervical_exam_date'])) {
                        $women[$cId]['last_cervical_exam_date'] = $dt;
                        $procInfo = $isMol
                            ? ($molMap[$dimAv] ?? $molMap[$dimSol] ?? ['code' => '0202100251', 'desc' => 'Exame Molecular DNA-HPV'])
                            : ($citoMap[$dimAv] ?? $citoMap[$dimSol] ?? ['code' => '0201020033', 'desc' => 'Citopatológico de colo']);
                        $women[$cId]['last_cervical_exam_code'] = $procInfo['code'];
                        $women[$cId]['last_cervical_exam_desc'] = $procInfo['desc'];
                    }
                }
            }

            // A.2: Ficha de procedimentos (tb_fat_proced_atend_proced)
            $coloFichaRows = $connection->select("
                SELECT p.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_procedimento,
                       p.co_dim_procedimento AS dim_proc,
                       cbo.nu_cbo
                FROM tb_fat_proced_atend_proced p
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo ON cbo.co_seq_dim_cbo = p.co_dim_cbo
                WHERE p.co_dim_procedimento IN ({$coloIn})
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                ORDER BY t.dt_registro DESC
            ", [$w60Str, $evalDateStr]);

            foreach ($coloFichaRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($women[$cId]) || ! $women[$cId]['eligible_practice_a']) {
                    continue;
                }

                $cbo = trim((string) ($r->nu_cbo ?? ''));
                if (! $this->isCboDoctorOrNurse($cbo)) {
                    continue;
                }

                $dt = Carbon::parse($r->dt_procedimento);
                $dimProc = (int) ($r->dim_proc ?? 0);

                $isMol = isset($molMap[$dimProc]);
                $isCito = isset($citoMap[$dimProc]);

                $validMol = $isMol && $dt->gte($windowColoMolecularStart);
                $validCito = $isCito && $dt->gte($windowColoCitoStart);

                if ($validMol || $validCito) {
                    $women[$cId]['practice_a_count']++;
                    $women[$cId]['practice_a_met'] = true;
                    if (! $women[$cId]['last_cervical_exam_date'] || $dt->gt($women[$cId]['last_cervical_exam_date'])) {
                        $women[$cId]['last_cervical_exam_date'] = $dt;
                        $procInfo = $isMol ? $molMap[$dimProc] : $citoMap[$dimProc];
                        $women[$cId]['last_cervical_exam_code'] = $procInfo['code'];
                        $women[$cId]['last_cervical_exam_desc'] = $procInfo['desc'];
                    }
                }
            }

            // A.3: Atendimento individual exames (tb_fat_atd_ind_exames)
            $coloExamesRows = $connection->select("
                SELECT e.co_fat_cidadao_pec AS cidadao_id,
                       COALESCE(e.dt_resultado, e.dt_realizacao, e.dt_solicitacao, t.dt_registro) AS dt_exame,
                       e.co_dim_procedimento AS dim_proc,
                       cbo1.nu_cbo AS cbo1,
                       cbo2.nu_cbo AS cbo2
                FROM tb_fat_atd_ind_exames e
                LEFT JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = e.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo1 ON cbo1.co_seq_dim_cbo = e.co_dim_cbo_1
                LEFT JOIN tb_dim_cbo cbo2 ON cbo2.co_seq_dim_cbo = e.co_dim_cbo_2
                WHERE e.co_dim_procedimento IN ({$coloIn})
                  AND COALESCE(e.dt_resultado, e.dt_realizacao, e.dt_solicitacao, t.dt_registro) >= ?
                  AND COALESCE(e.dt_resultado, e.dt_realizacao, e.dt_solicitacao, t.dt_registro) <= ?
                ORDER BY dt_exame DESC
            ", [$w60Str, $evalDateStr]);

            foreach ($coloExamesRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($women[$cId]) || ! $women[$cId]['eligible_practice_a'] || empty($r->dt_exame)) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                if (! $this->isCboDoctorOrNurse($cbo1) && ! $this->isCboDoctorOrNurse($cbo2)) {
                    continue;
                }

                $dt = Carbon::parse($r->dt_exame);
                $dimProc = (int) ($r->dim_proc ?? 0);

                $isMol = isset($molMap[$dimProc]);
                $isCito = isset($citoMap[$dimProc]);

                $validMol = $isMol && $dt->gte($windowColoMolecularStart);
                $validCito = $isCito && $dt->gte($windowColoCitoStart);

                if ($validMol || $validCito) {
                    $women[$cId]['practice_a_count']++;
                    $women[$cId]['practice_a_met'] = true;
                    if (! $women[$cId]['last_cervical_exam_date'] || $dt->gt($women[$cId]['last_cervical_exam_date'])) {
                        $women[$cId]['last_cervical_exam_date'] = $dt;
                        $procInfo = $isMol ? $molMap[$dimProc] : $citoMap[$dimProc];
                        $women[$cId]['last_cervical_exam_code'] = $procInfo['code'];
                        $women[$cId]['last_cervical_exam_desc'] = $procInfo['desc'];
                    }
                }
            }
        }

        // =========================================================================
        // PRÁTICA B: VACINA CONTRA O HPV (9 a 14 anos - 30 pts)
        // =========================================================================
        if ($hpvDimIds !== []) {
            $hpvIn = implode(',', $hpvDimIds);
            $hpvRows = $connection->select("
                SELECT v.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_vacina,
                       dose.co_dim_imunobiologico AS dim_bio
                FROM tb_fat_vacinacao v
                JOIN tb_fat_vacinacao_vacina dose ON dose.co_fat_vacinacao = v.co_seq_fat_vacinacao
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = dose.co_dim_tempo_vacina_aplicada
                WHERE dose.co_dim_imunobiologico IN ({$hpvIn})
                  AND t.dt_registro <= ?
                ORDER BY t.dt_registro DESC
            ", [$evalDateStr]);

            foreach ($hpvRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($women[$cId]) || ! $women[$cId]['eligible_practice_b']) {
                    continue;
                }

                $dimBio = (int) ($r->dim_bio ?? 0);
                $bioInfo = $hpvMap[$dimBio] ?? ['code' => '67', 'name' => 'Vacina HPV'];

                $women[$cId]['practice_b_count']++;
                $women[$cId]['practice_b_met'] = true;
                if (! $women[$cId]['last_hpv_vaccine_date']) {
                    $women[$cId]['last_hpv_vaccine_date'] = Carbon::parse($r->dt_vacina);
                    $women[$cId]['last_hpv_vaccine_code'] = $bioInfo['code'];
                    $women[$cId]['last_hpv_vaccine_name'] = $bioInfo['name'];
                }
            }
        }

        // =========================================================================
        // PRÁTICA C: SAÚDE SEXUAL E REPRODUTIVA (14 a 69 anos - 30 pts)
        // =========================================================================
        $w12Str = $windowSexualHealthStart->toDateString();

        // C.1: Problemas / Condições avaliadas (CIAP-2 / CID-10)
        $whereProbParts = [];
        if ($ciapDimIds !== []) {
            $whereProbParts[] = 'p.co_dim_ciap IN ('.implode(',', $ciapDimIds).')';
        }
        if ($cidDimIds !== []) {
            $whereProbParts[] = 'p.co_dim_cid IN ('.implode(',', $cidDimIds).')';
        }

        if ($whereProbParts !== []) {
            $probWhereSql = '('.implode(' OR ', $whereProbParts).')';
            $probRows = $connection->select("
                SELECT p.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_atendimento,
                       p.co_dim_ciap AS dim_ciap,
                       p.co_dim_cid AS dim_cid,
                       cbo1.nu_cbo AS cbo1,
                       cbo2.nu_cbo AS cbo2
                FROM tb_fat_atd_ind_problemas p
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo1 ON cbo1.co_seq_dim_cbo = p.co_dim_cbo_1
                LEFT JOIN tb_dim_cbo cbo2 ON cbo2.co_seq_dim_cbo = p.co_dim_cbo_2
                WHERE {$probWhereSql}
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                ORDER BY t.dt_registro DESC
            ", [$w12Str, $evalDateStr]);

            foreach ($probRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($women[$cId]) || ! $women[$cId]['eligible_practice_c']) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                if (! $this->isCboDoctorOrNurse($cbo1) && ! $this->isCboDoctorOrNurse($cbo2)) {
                    continue;
                }

                $dimCiap = (int) ($r->dim_ciap ?? 0);
                $dimCid = (int) ($r->dim_cid ?? 0);

                $ciapValid = isset($ciapMap[$dimCiap]);
                $cidValid = isset($cidMap[$dimCid]);

                if ($ciapValid || $cidValid) {
                    $dt = Carbon::parse($r->dt_atendimento);
                    $women[$cId]['practice_c_count']++;
                    $women[$cId]['practice_c_met'] = true;
                    if (! $women[$cId]['last_sexual_health_date'] || $dt->gt($women[$cId]['last_sexual_health_date'])) {
                        $women[$cId]['last_sexual_health_date'] = $dt;
                        $diagInfo = $ciapValid ? $ciapMap[$dimCiap] : $cidMap[$dimCid];
                        $women[$cId]['last_sexual_health_code'] = $diagInfo['code'];
                        $women[$cId]['last_sexual_health_detail'] = $diagInfo['name'];
                    }
                }
            }
        }

        // C.2: Procedimentos de Saúde Sexual (atendimento individual)
        if ($sexProcDimIds !== []) {
            $sexProcIn = implode(',', $sexProcDimIds);
            $procSexRows = $connection->select("
                SELECT p.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_procedimento,
                       p.co_dim_procedimento_avaliado AS dim_av,
                       p.co_dim_procedimento_solicitado AS dim_sol,
                       cbo1.nu_cbo AS cbo1,
                       cbo2.nu_cbo AS cbo2
                FROM tb_fat_atd_ind_procedimentos p
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo1 ON cbo1.co_seq_dim_cbo = p.co_dim_cbo_1
                LEFT JOIN tb_dim_cbo cbo2 ON cbo2.co_seq_dim_cbo = p.co_dim_cbo_2
                WHERE (p.co_dim_procedimento_avaliado IN ({$sexProcIn}) OR p.co_dim_procedimento_solicitado IN ({$sexProcIn}))
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                ORDER BY t.dt_registro DESC
            ", [$w12Str, $evalDateStr]);

            foreach ($procSexRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($women[$cId]) || ! $women[$cId]['eligible_practice_c']) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                if (! $this->isCboDoctorOrNurse($cbo1) && ! $this->isCboDoctorOrNurse($cbo2)) {
                    continue;
                }

                $dimAv = (int) ($r->dim_av ?? 0);
                $dimSol = (int) ($r->dim_sol ?? 0);
                $isAv = isset($sexProcMap[$dimAv]);
                $isSol = isset($sexProcMap[$dimSol]);

                if ($isAv || $isSol) {
                    $dt = Carbon::parse($r->dt_procedimento);
                    $women[$cId]['practice_c_count']++;
                    $women[$cId]['practice_c_met'] = true;
                    if (! $women[$cId]['last_sexual_health_date'] || $dt->gt($women[$cId]['last_sexual_health_date'])) {
                        $women[$cId]['last_sexual_health_date'] = $dt;
                        $procInfo = $isAv ? $sexProcMap[$dimAv] : $sexProcMap[$dimSol];
                        $women[$cId]['last_sexual_health_code'] = $procInfo['code'];
                        $women[$cId]['last_sexual_health_detail'] = $procInfo['desc'];
                    }
                }
            }
        }

        // =========================================================================
        // PRÁTICA D: RASTREAMENTO DO CÂNCER DE MAMA (50 a 69 anos - 20 pts)
        // =========================================================================
        if ($mamaDimIds !== []) {
            $mamaIn = implode(',', $mamaDimIds);
            $w24Str = $windowMamaStart->toDateString();

            // D.1: Atendimento individual procedimentos
            $mamaProcRows = $connection->select("
                SELECT p.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_procedimento,
                       p.co_dim_procedimento_avaliado AS dim_av,
                       p.co_dim_procedimento_solicitado AS dim_sol,
                       cbo1.nu_cbo AS cbo1,
                       cbo2.nu_cbo AS cbo2
                FROM tb_fat_atd_ind_procedimentos p
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo1 ON cbo1.co_seq_dim_cbo = p.co_dim_cbo_1
                LEFT JOIN tb_dim_cbo cbo2 ON cbo2.co_seq_dim_cbo = p.co_dim_cbo_2
                WHERE (p.co_dim_procedimento_avaliado IN ({$mamaIn}) OR p.co_dim_procedimento_solicitado IN ({$mamaIn}))
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                ORDER BY t.dt_registro DESC
            ", [$w24Str, $evalDateStr]);

            foreach ($mamaProcRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($women[$cId]) || ! $women[$cId]['eligible_practice_d']) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                if (! $this->isCboDoctorOrNurse($cbo1) && ! $this->isCboDoctorOrNurse($cbo2)) {
                    continue;
                }

                $dimAv = (int) ($r->dim_av ?? 0);
                $dimSol = (int) ($r->dim_sol ?? 0);
                $isMamaAv = isset($mamaMap[$dimAv]);
                $isMamaSol = isset($mamaMap[$dimSol]);

                if ($isMamaAv || $isMamaSol) {
                    $dt = Carbon::parse($r->dt_procedimento);
                    $women[$cId]['practice_d_count']++;
                    $women[$cId]['practice_d_met'] = true;
                    if (! $women[$cId]['last_mammogram_date'] || $dt->gt($women[$cId]['last_mammogram_date'])) {
                        $women[$cId]['last_mammogram_date'] = $dt;
                        $procInfo = $isMamaAv ? $mamaMap[$dimAv] : $mamaMap[$dimSol];
                        $women[$cId]['last_mammogram_code'] = $procInfo['code'];
                        $women[$cId]['last_mammogram_desc'] = $procInfo['desc'];
                    }
                }
            }

            // D.2: Ficha de procedimentos
            $mamaFichaRows = $connection->select("
                SELECT p.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_procedimento,
                       p.co_dim_procedimento AS dim_proc,
                       cbo.nu_cbo
                FROM tb_fat_proced_atend_proced p
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo ON cbo.co_seq_dim_cbo = p.co_dim_cbo
                WHERE p.co_dim_procedimento IN ({$mamaIn})
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                ORDER BY t.dt_registro DESC
            ", [$w24Str, $evalDateStr]);

            foreach ($mamaFichaRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($women[$cId]) || ! $women[$cId]['eligible_practice_d']) {
                    continue;
                }

                $cbo = trim((string) ($r->nu_cbo ?? ''));
                if (! $this->isCboDoctorOrNurse($cbo)) {
                    continue;
                }

                $dimProc = (int) ($r->dim_proc ?? 0);
                if (isset($mamaMap[$dimProc])) {
                    $dt = Carbon::parse($r->dt_procedimento);
                    $women[$cId]['practice_d_count']++;
                    $women[$cId]['practice_d_met'] = true;
                    if (! $women[$cId]['last_mammogram_date'] || $dt->gt($women[$cId]['last_mammogram_date'])) {
                        $women[$cId]['last_mammogram_date'] = $dt;
                        $procInfo = $mamaMap[$dimProc];
                        $women[$cId]['last_mammogram_code'] = $procInfo['code'];
                        $women[$cId]['last_mammogram_desc'] = $procInfo['desc'];
                    }
                }
            }

            // D.3: Atendimento individual exames
            $mamaExamesRows = $connection->select("
                SELECT e.co_fat_cidadao_pec AS cidadao_id,
                       COALESCE(e.dt_resultado, e.dt_realizacao, e.dt_solicitacao, t.dt_registro) AS dt_exame,
                       e.co_dim_procedimento AS dim_proc,
                       cbo1.nu_cbo AS cbo1,
                       cbo2.nu_cbo AS cbo2
                FROM tb_fat_atd_ind_exames e
                LEFT JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = e.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo1 ON cbo1.co_seq_dim_cbo = e.co_dim_cbo_1
                LEFT JOIN tb_dim_cbo cbo2 ON cbo2.co_seq_dim_cbo = e.co_dim_cbo_2
                WHERE e.co_dim_procedimento IN ({$mamaIn})
                  AND COALESCE(e.dt_resultado, e.dt_realizacao, e.dt_solicitacao, t.dt_registro) >= ?
                  AND COALESCE(e.dt_resultado, e.dt_realizacao, e.dt_solicitacao, t.dt_registro) <= ?
                ORDER BY dt_exame DESC
            ", [$w24Str, $evalDateStr]);

            foreach ($mamaExamesRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($women[$cId]) || ! $women[$cId]['eligible_practice_d'] || empty($r->dt_exame)) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                if (! $this->isCboDoctorOrNurse($cbo1) && ! $this->isCboDoctorOrNurse($cbo2)) {
                    continue;
                }

                $dimProc = (int) ($r->dim_proc ?? 0);
                if (isset($mamaMap[$dimProc])) {
                    $dt = Carbon::parse($r->dt_exame);
                    $women[$cId]['practice_d_count']++;
                    $women[$cId]['practice_d_met'] = true;
                    if (! $women[$cId]['last_mammogram_date'] || $dt->gt($women[$cId]['last_mammogram_date'])) {
                        $women[$cId]['last_mammogram_date'] = $dt;
                        $procInfo = $mamaMap[$dimProc];
                        $women[$cId]['last_mammogram_code'] = $procInfo['code'];
                        $women[$cId]['last_mammogram_desc'] = $procInfo['desc'];
                    }
                }
            }
        }
    }

    /**
     * @param  list<string>  $codes
     * @return array<int, array{code: string, desc: string}>
     */
    private function resolveProcedureDimMap(ConnectionInterface $connection, array $codes): array
    {
        $rows = $connection->table('tb_dim_procedimento')
            ->whereIn('co_proced', $codes)
            ->get(['co_seq_dim_procedimento', 'co_proced', 'ds_proced']);

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r->co_seq_dim_procedimento] = [
                'code' => trim((string) $r->co_proced),
                'desc' => trim((string) ($r->ds_proced ?? '')),
            ];
        }

        return $map;
    }

    /**
     * @return array<int, array{code: string, name: string}>
     */
    private function resolveHpvDimMap(ConnectionInterface $connection): array
    {
        $rows = $connection->table('tb_dim_imunobiologico')
            ->where(function ($q) {
                $q->whereIn('nu_identificador', self::VACCINE_HPV_CODES)
                    ->orWhereRaw("LOWER(no_imunobiologico) LIKE '%hpv%'");
            })
            ->get(['co_seq_dim_imunobiologico', 'nu_identificador', 'no_imunobiologico']);

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r->co_seq_dim_imunobiologico] = [
                'code' => trim((string) ($r->nu_identificador ?? '67')),
                'name' => trim((string) ($r->no_imunobiologico ?? 'Vacina HPV')),
            ];
        }

        return $map;
    }

    /**
     * @return array<int, array{code: string, name: string}>
     */
    private function resolveCiapDimMap(ConnectionInterface $connection): array
    {
        $rows = $connection->table('tb_dim_ciap')
            ->whereIn('nu_ciap', self::CIAP_SEXUAL_HEALTH)
            ->get(['co_seq_dim_ciap', 'nu_ciap', 'no_ciap']);

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r->co_seq_dim_ciap] = [
                'code' => strtoupper(trim((string) $r->nu_ciap)),
                'name' => trim((string) ($r->no_ciap ?? '')),
            ];
        }

        return $map;
    }

    /**
     * @return array<int, array{code: string, name: string}>
     */
    private function resolveCidDimMap(ConnectionInterface $connection): array
    {
        $query = $connection->table('tb_dim_cid');
        $query->where(function ($q) {
            foreach (self::CID_PREFIX_SEXUAL_HEALTH as $p) {
                $q->orWhere('nu_cid', 'LIKE', $p.'%');
            }
        });

        $rows = $query->get(['co_seq_dim_cid', 'nu_cid', 'no_cid']);

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r->co_seq_dim_cid] = [
                'code' => strtoupper(trim((string) $r->nu_cid)),
                'name' => trim((string) ($r->no_cid ?? '')),
            ];
        }

        return $map;
    }

    /**
     * Consolida os resultados individuais, as pontuações das equipes e as taxas municipais.
     *
     * @param  array<int, array<string, mixed>>  $women
     * @param  array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>  $teams
     * @return array<string, mixed>
     */
    private function compileResults(
        array $women,
        array $teams,
        int $year,
        int $quarter,
        Carbon $asOf,
        Carbon $evalDate
    ): array {
        $nominalList = [];
        $teamScores = [];
        $cohortCounts = [];

        // Inicializa contadores por equipe
        foreach ($teams as $ine => $t) {
            $teamScores[$ine] = [
                'ine' => $ine,
                'team_name' => $t['name'],
                'team_type' => $t['type'],
                'total_cohort' => 0,
                'evaluated_total' => 0,

                // Prática A (Colo de Útero - 20 pts)
                'practice_a_eligible' => 0,
                'practice_a_compliant' => 0,
                'practice_a_score' => 0.0,

                // Prática B (Vacina HPV - 30 pts)
                'practice_b_eligible' => 0,
                'practice_b_compliant' => 0,
                'practice_b_score' => 0.0,

                // Prática C (Saúde Sexual - 30 pts)
                'practice_c_eligible' => 0,
                'practice_c_compliant' => 0,
                'practice_c_score' => 0.0,

                // Prática D (Mama - 20 pts)
                'practice_d_eligible' => 0,
                'practice_d_compliant' => 0,
                'practice_d_score' => 0.0,

                'final_score' => 0.0,
            ];
            $cohortCounts[$ine] = 0;
        }

        $practicesCitywide = [
            'A_eligible' => 0,
            'A_compliant' => 0,
            'B_eligible' => 0,
            'B_compliant' => 0,
            'C_eligible' => 0,
            'C_compliant' => 0,
            'D_eligible' => 0,
            'D_compliant' => 0,
        ];

        foreach ($women as $d) {
            $ine = $d['ine'];
            if (! isset($teamScores[$ine])) {
                continue;
            }

            $teamScores[$ine]['total_cohort']++;
            $cohortCounts[$ine]++;

            // Acumula elegibilidade e cumprimento por prática na equipe
            if ($d['eligible_practice_a']) {
                $teamScores[$ine]['practice_a_eligible']++;
                $practicesCitywide['A_eligible']++;
                if ($d['practice_a_met']) {
                    $teamScores[$ine]['practice_a_compliant']++;
                    $practicesCitywide['A_compliant']++;
                }
            }

            if ($d['eligible_practice_b']) {
                $teamScores[$ine]['practice_b_eligible']++;
                $practicesCitywide['B_eligible']++;
                if ($d['practice_b_met']) {
                    $teamScores[$ine]['practice_b_compliant']++;
                    $practicesCitywide['B_compliant']++;
                }
            }

            if ($d['eligible_practice_c']) {
                $teamScores[$ine]['practice_c_eligible']++;
                $practicesCitywide['C_eligible']++;
                if ($d['practice_c_met']) {
                    $teamScores[$ine]['practice_c_compliant']++;
                    $practicesCitywide['C_compliant']++;
                }
            }

            if ($d['eligible_practice_d']) {
                $teamScores[$ine]['practice_d_eligible']++;
                $practicesCitywide['D_eligible']++;
                if ($d['practice_d_met']) {
                    $teamScores[$ine]['practice_d_compliant']++;
                    $practicesCitywide['D_compliant']++;
                }
            }

            // Pontuação individual da mulher (proporcional às práticas que ela é elegível)
            $individualMaxPts = 0.0;
            $individualEarnedPts = 0.0;
            $pending = [];

            if ($d['eligible_practice_a']) {
                $individualMaxPts += 20.0;
                if ($d['practice_a_met']) {
                    $individualEarnedPts += 20.0;
                } else {
                    $pending[] = 'A';
                }
            }

            if ($d['eligible_practice_b']) {
                $individualMaxPts += 30.0;
                if ($d['practice_b_met']) {
                    $individualEarnedPts += 30.0;
                } else {
                    $pending[] = 'B';
                }
            }

            if ($d['eligible_practice_c']) {
                $individualMaxPts += 30.0;
                if ($d['practice_c_met']) {
                    $individualEarnedPts += 30.0;
                } else {
                    $pending[] = 'C';
                }
            }

            if ($d['eligible_practice_d']) {
                $individualMaxPts += 20.0;
                if ($d['practice_d_met']) {
                    $individualEarnedPts += 20.0;
                } else {
                    $pending[] = 'D';
                }
            }

            $indScore = $individualMaxPts > 0
                ? round(($individualEarnedPts / $individualMaxPts) * 100.0, 2)
                : 100.0;

            if ($individualEarnedPts > 0) {
                $teamScores[$ine]['evaluated_total']++;
            }

            $d['score_percent'] = $indScore;
            $d['pending_practices'] = $pending;

            $nominalList[] = $d;
        }

        // Consolida pontuação de cada equipe conforme as fórmulas oficiais da NT C7
        foreach ($teamScores as $ine => &$ts) {
            // Prática A [20 pts]: (a / b) * 20
            $ptsA = $ts['practice_a_eligible'] > 0
                ? ($ts['practice_a_compliant'] / $ts['practice_a_eligible']) * 20.0
                : 20.0;

            // Prática B [30 pts]: (c / d) * 30
            $ptsB = $ts['practice_b_eligible'] > 0
                ? ($ts['practice_b_compliant'] / $ts['practice_b_eligible']) * 30.0
                : 30.0;

            // Prática C [30 pts]: (e / f) * 30
            $ptsC = $ts['practice_c_eligible'] > 0
                ? ($ts['practice_c_compliant'] / $ts['practice_c_eligible']) * 30.0
                : 30.0;

            // Prática D [20 pts]: (g / h) * 20
            $ptsD = $ts['practice_d_eligible'] > 0
                ? ($ts['practice_d_compliant'] / $ts['practice_d_eligible']) * 20.0
                : 20.0;

            $ts['practice_a_score'] = round($ptsA, 2);
            $ts['practice_b_score'] = round($ptsB, 2);
            $ts['practice_c_score'] = round($ptsC, 2);
            $ts['practice_d_score'] = round($ptsD, 2);
            $ts['final_score'] = round($ptsA + $ptsB + $ptsC + $ptsD, 2);
        }
        unset($ts);

        // Pontuação Municipal Consolidada
        $cityPtsA = $practicesCitywide['A_eligible'] > 0
            ? ($practicesCitywide['A_compliant'] / $practicesCitywide['A_eligible']) * 20.0
            : 20.0;

        $cityPtsB = $practicesCitywide['B_eligible'] > 0
            ? ($practicesCitywide['B_compliant'] / $practicesCitywide['B_eligible']) * 30.0
            : 30.0;

        $cityPtsC = $practicesCitywide['C_eligible'] > 0
            ? ($practicesCitywide['C_compliant'] / $practicesCitywide['C_eligible']) * 30.0
            : 30.0;

        $cityPtsD = $practicesCitywide['D_eligible'] > 0
            ? ($practicesCitywide['D_compliant'] / $practicesCitywide['D_eligible']) * 20.0
            : 20.0;

        $municipalScore = round($cityPtsA + $cityPtsB + $cityPtsC + $cityPtsD, 2);
        $totalWomenCount = count($nominalList);

        return [
            'version' => self::VERSION,
            'as_of' => $asOf->toDateString(),
            'year' => $year,
            'quarter' => $quarter,
            'total_women' => $totalWomenCount,
            'municipal_score' => $municipalScore,
            'practices_citywide' => [
                'A_score' => round($cityPtsA, 2),
                'A_compliant' => $practicesCitywide['A_compliant'],
                'A_eligible' => $practicesCitywide['A_eligible'],
                'B_score' => round($cityPtsB, 2),
                'B_compliant' => $practicesCitywide['B_compliant'],
                'B_eligible' => $practicesCitywide['B_eligible'],
                'C_score' => round($cityPtsC, 2),
                'C_compliant' => $practicesCitywide['C_compliant'],
                'C_eligible' => $practicesCitywide['C_eligible'],
                'D_score' => round($cityPtsD, 2),
                'D_compliant' => $practicesCitywide['D_compliant'],
                'D_eligible' => $practicesCitywide['D_eligible'],
            ],
            'teams' => $teamScores,
            'nominal' => $nominalList,
        ];
    }

    private function isCboDoctorOrNurse(string $cbo): bool
    {
        $clean = preg_replace('/\D+/', '', $cbo);
        if ($clean === '') {
            return false;
        }

        foreach (self::CBOS_MEDICO as $prefix) {
            if (str_starts_with($clean, $prefix)) {
                return true;
            }
        }

        foreach (self::CBOS_ENFERMEIRO as $prefix) {
            if (str_starts_with($clean, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, bool>
     */
    private function columnList(ConnectionInterface $connection, string $table): array
    {
        try {
            $rows = $connection->select(
                'SELECT column_name FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ?',
                [$table]
            );

            $out = [];
            foreach ($rows as $r) {
                $out[(string) $r->column_name] = true;
            }

            return $out;
        } catch (Throwable) {
            return [];
        }
    }

    private function marks(int $count): string
    {
        return $count > 0 ? implode(', ', array_fill(0, $count, '?')) : '';
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(Carbon $asOf): array
    {
        return [
            'version' => self::VERSION,
            'as_of' => $asOf->toDateString(),
            'total_women' => 0,
            'municipal_score' => 0.0,
            'practices_citywide' => [
                'A_score' => 0.0,
                'A_compliant' => 0,
                'A_eligible' => 0,
                'B_score' => 0.0,
                'B_compliant' => 0,
                'B_eligible' => 0,
                'C_score' => 0.0,
                'C_compliant' => 0,
                'C_eligible' => 0,
                'D_score' => 0.0,
                'D_compliant' => 0,
                'D_eligible' => 0,
            ],
            'teams' => [],
            'nominal' => [],
        ];
    }
}
