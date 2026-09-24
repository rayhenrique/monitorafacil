<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

/**
 * Extração do Indicador C4 (Cuidado da Pessoa com Diabetes) no DW do PEC.
 *
 * Baseado estritamente na Nota Metodológica C4 e na Nota Técnica 08/2026.
 * Avalia o acompanhamento contínuo e as 6 boas práticas de cuidado (A a F).
 */
class C4DwService
{
    public const VERSION = 'dw-c4-2026-09-normative-v1.0';

    private const CHUNK_SIZE = 100;

    // CIAP-2 de Diabetes Mellitus
    private const DIABETES_CIAPS = ['T89', 'T90'];

    // CID-10 de Diabetes Mellitus (E10, E11, E14 e seus subcódigos)
    private const DIABETES_CID_PREFIXES = ['E10', 'E11', 'E14'];

    // Códigos SIGTAP / ABEX das Boas Práticas
    private const SIGTAP_PA = ['0301100039'];
    private const SIGTAP_ANTROPOMETRIA = ['0101040024'];
    private const SIGTAP_PESO = ['0101040083'];
    private const SIGTAP_ALTURA = ['0101040075'];
    private const SIGTAP_HBA1C = ['0202010503', 'ABEX008'];
    private const SIGTAP_PE_DIABETICO = ['0301040095', 'ABPG011'];
    private const SIGTAP_CONSULTA = ['0301010064', '0301010030', '0301010250'];

    // CBOs habilitados
    private const CBOS_CONSULTA_MEDICO = ['2251', '2252', '2253', '2231'];
    private const CBOS_CONSULTA_ENFERMEIRO = ['2235'];
    private const CBOS_ACS = ['515105', '5151-05', '322255', '3222-55'];

    /**
     * @param  array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>  $teams
     * @return array<string, mixed>
     */
    public function extract(ConnectionInterface $connection, int $year, int $quarter, array $teams): array
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        if ($teams === []) {
            throw new RuntimeException('C4: nenhuma equipe eSF/eAP válida foi identificada no PEC.');
        }

        $firstMonth = (($quarter - 1) * 4) + 1;
        $start = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $end = (clone $start)->addMonths(4)->subDay()->endOfDay();
        $asOf = Carbon::today();
        $evalDate = $asOf->lt($end) ? $asOf->copy()->endOfDay() : $end;

        // Janelas regulamentares de boas práticas:
        // 6 meses para Consulta (A) e Pressão Arterial (B)
        $sixMonthsStart = (clone $evalDate)->subMonths(6)->startOfDay();
        // 12 meses para Peso/Altura (C), Visitas ACS (D), Hemoglobina Glicada (E) e Avaliação dos Pés (F)
        $twelveMonthsStart = (clone $evalDate)->subMonths(12)->startOfDay();

        // 1. Carrega cidadãos vinculados às equipes
        $citizens = $this->loadLinkedCitizens($connection, $teams, $asOf);
        if ($citizens === []) {
            return $this->emptyResult($asOf);
        }

        // 2. Identifica pessoas com condição avaliada de Diabetes (desde 2013)
        $diabetics = $this->identifyDiabetics($connection, $citizens, $evalDate);
        unset($citizens);
        if ($diabetics === []) {
            return $this->emptyResult($asOf);
        }

        // 3. Carrega os eventos clínicos das 6 boas práticas
        $this->loadClinicalEvents($connection, $diabetics, $sixMonthsStart, $twelveMonthsStart, $evalDate);

        // 4. Calcula pontuações individuais e consolida por equipe e município
        return $this->consolidate($diabetics, $teams, $year, $quarter, $start, $end, $asOf);
    }

    /** @return array<string, mixed> */
    private function emptyResult(Carbon $asOf): array
    {
        return [
            'scores' => [],
            'cohort' => [],
            'as_of' => $asOf->toDateString(),
            'diabetics' => [],
            'municipal_summary' => [],
        ];
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
                throw new RuntimeException("C4: a visão tb_acomp_cidadaos_vinculados não contém a coluna obrigatória {$required}.");
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

            $bornStr = substr((string) $row->born, 0, 10);
            $birthYear = (int) substr($bornStr, 0, 4);
            $citizens[$id] = [
                'id' => $id,
                'ine' => $ine,
                'born' => $bornStr,
                'age_years' => max(0, $asOf->year - $birthYear),
                'name' => $name,
                'cpf' => $cpf,
                'cns' => $cns,
                'phone' => trim((string) ($row->phone ?? '')),
                'cnes' => trim((string) ($row->cnes ?? ($teams[$ine]['cnes'] ?? ''))),
                'facility_name' => trim((string) ($row->facility_name ?? ($teams[$ine]['facility_name'] ?? $teams[$ine]['name'] ?? ''))),
                'microarea' => trim((string) ($row->microarea ?? '')),
                'race_color' => trim((string) ($row->race_color ?? '')) ?: 'Não informada',
                'team_type' => $teams[$ine]['type'] ?? '70',
            ];
        }

        return $citizens;
    }

    /**
     * Identifica pessoas com Diabetes elegíveis na coorte.
     *
     * @param  array<int, array<string, mixed>>  $citizens
     * @return array<int, array<string, mixed>>
     */
    private function identifyDiabetics(ConnectionInterface $connection, array $citizens, Carbon $evalDate): array
    {
        $citizenIds = array_keys($citizens);
        $diabetics = [];

        // Recupera IDs de CIAP (T89, T90)
        $ciapRows = $connection->select("SELECT co_seq_dim_ciap AS id, nu_ciap FROM tb_dim_ciap WHERE nu_ciap IN ('T89', 'T90')");
        $ciapIds = array_map(fn ($r) => (int) $r->id, $ciapRows);

        // Recupera IDs de CID (E10%, E11%, E14%)
        $cidRows = $connection->select("SELECT co_seq_dim_cid AS id, nu_cid FROM tb_dim_cid WHERE nu_cid LIKE 'E10%' OR nu_cid LIKE 'E11%' OR nu_cid LIKE 'E14%'");
        $cidIds = array_map(fn ($r) => (int) $r->id, $cidRows);

        $hasCiap = ! empty($ciapIds);
        $hasCid = ! empty($cidIds);

        if (! $hasCiap && ! $hasCid) {
            return [];
        }

        // Consulta diagnósticos na tabela tb_fat_atd_ind_problemas
        foreach (array_chunk($citizenIds, self::CHUNK_SIZE) as $chunk) {
            $conditions = [];
            $bindings = [];

            if ($hasCiap) {
                $conditions[] = 'co_dim_ciap IN (' . $this->marks(count($ciapIds)) . ')';
                $bindings = array_merge($bindings, $ciapIds);
            }
            if ($hasCid) {
                $conditions[] = 'co_dim_cid IN (' . $this->marks(count($cidIds)) . ')';
                $bindings = array_merge($bindings, $cidIds);
            }

            $condSql = implode(' OR ', $conditions);
            $chunkMarks = $this->marks(count($chunk));
            $allBindings = array_merge($chunk, $bindings);

            $sql = <<<SQL
                SELECT p.co_fat_cidadao_pec AS cidadao_id,
                       p.co_dim_ciap,
                       p.co_dim_cid,
                       p.co_dim_situacao_problema,
                       COALESCE(t.dt_registro, DATE '2013-01-01') AS dt_atendimento
                FROM tb_fat_atd_ind_problemas p
                LEFT JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
                WHERE p.co_fat_cidadao_pec IN ({$chunkMarks})
                  AND ({$condSql})
                ORDER BY p.co_fat_cidadao_pec, dt_atendimento ASC
            SQL;

            $rows = $connection->select($sql, $allBindings);

            foreach ($rows as $row) {
                $cId = (int) $row->cidadao_id;
                if (! isset($citizens[$cId])) {
                    continue;
                }

                // Verifica se a condição foi marcada como resolvida (id 3 = Resolvido)
                // Se o cidadão só tem registros como "Resolvido", ele é desconsiderado
                $isResolved = ((int) $row->co_dim_situacao_problema) === 3;
                $dt = Carbon::parse($row->dt_atendimento)->startOfDay();

                if (! isset($diabetics[$cId])) {
                    $bornCarbon = Carbon::parse($citizens[$cId]['born'])->startOfDay();
                    $diabetics[$cId] = array_merge($citizens[$cId], [
                        'born' => $bornCarbon,
                        'age_years' => (int) $bornCarbon->diffInYears($evalDate),
                        'first_diagnosis_date' => $dt,
                        'last_diagnosis_date' => $dt,
                        'has_active' => ! $isResolved,
                        'ciap_codes' => [],
                        'cid_codes' => [],
                        // Inicializa dados das 6 práticas
                        'practice_a_count' => 0,
                        'practice_a_met' => false,
                        'last_consultation_date' => null,
                        'practice_b_count' => 0,
                        'practice_b_met' => false,
                        'last_pa_date' => null,
                        'last_pa_value' => null,
                        'practice_c_count' => 0,
                        'practice_c_met' => false,
                        'last_anthropometry_date' => null,
                        'last_weight' => null,
                        'last_height' => null,
                        'practice_d_count' => 0,
                        'practice_d_met' => false,
                        'last_visit_date' => null,
                        'practice_e_count' => 0,
                        'practice_e_met' => false,
                        'last_hba1c_date' => null,
                        'last_hba1c_type' => null,
                        'practice_f_count' => 0,
                        'practice_f_met' => false,
                        'last_foot_exam_date' => null,
                        'score_percent' => 0.0,
                    ]);
                } else {
                    if (! $isResolved) {
                        $diabetics[$cId]['has_active'] = true;
                    }
                    if ($dt->gt($diabetics[$cId]['last_diagnosis_date'])) {
                        $diabetics[$cId]['last_diagnosis_date'] = $dt;
                    }
                }
            }
        }

        // Filtra apenas aqueles com condição ativa (não apenas resolvidos)
        return array_filter($diabetics, fn ($d) => $d['has_active'] === true);
    }

    /**
     * Carrega os eventos clínicos das 6 boas práticas para os diabéticos.
     *
     * @param  array<int, array<string, mixed>>  $diabetics
     */
    private function loadClinicalEvents(
        ConnectionInterface $connection,
        array &$diabetics,
        Carbon $sixMonthsStart,
        Carbon $twelveMonthsStart,
        Carbon $evalDate
    ): void {
        $citizenIds = array_keys($diabetics);
        if ($citizenIds === []) {
            return;
        }

        foreach (array_chunk($citizenIds, self::CHUNK_SIZE) as $chunk) {
            $chunkMarks = $this->marks(count($chunk));

            // =========================================================================
            // PRÁTICA A: Consultas médicas ou de enfermagem nos últimos 6 meses (20 pts)
            // CBOs: Médicos (2251, 2252, 2253, 2231) ou Enfermeiros (2235)
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
                $sixMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($cRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($diabetics[$cId])) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                $isDoctorOrNurse = $this->isCboDoctorOrNurse($cbo1) || $this->isCboDoctorOrNurse($cbo2);

                if ($isDoctorOrNurse) {
                    $diabetics[$cId]['practice_a_count']++;
                    $diabetics[$cId]['practice_a_met'] = true;
                    if (! $diabetics[$cId]['last_consultation_date']) {
                        $diabetics[$cId]['last_consultation_date'] = Carbon::parse($r->dt_atendimento);
                    }
                }
            }

            // =========================================================================
            // PRÁTICA B: Aferição de Pressão Arterial nos últimos 6 meses (15 pts)
            // =========================================================================
            $paSql = <<<SQL
                SELECT a.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_atendimento,
                       a.nu_pressao_sistolica,
                       a.nu_pressao_diastolica
                FROM tb_fat_atendimento_individual a
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = a.co_dim_tempo
                WHERE a.co_fat_cidadao_pec IN ({$chunkMarks})
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                  AND (a.nu_pressao_sistolica > 0 OR a.nu_pressao_diastolica > 0)
                ORDER BY t.dt_registro DESC
            SQL;

            $paRows = $connection->select($paSql, array_merge($chunk, [
                $sixMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($paRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($diabetics[$cId])) {
                    continue;
                }

                $diabetics[$cId]['practice_b_count']++;
                $diabetics[$cId]['practice_b_met'] = true;
                if (! $diabetics[$cId]['last_pa_date']) {
                    $diabetics[$cId]['last_pa_date'] = Carbon::parse($r->dt_atendimento);
                    $pas = round((float) $r->nu_pressao_sistolica);
                    $pad = round((float) $r->nu_pressao_diastolica);
                    $diabetics[$cId]['last_pa_value'] = ($pas > 0 && $pad > 0) ? "{$pas}/{$pad}" : null;
                }
            }

            // =========================================================================
            // PRÁTICA C: Antropometria (Peso e Altura Simultâneos) em 12 meses (15 pts)
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
                if (! isset($diabetics[$cId])) {
                    continue;
                }

                $diabetics[$cId]['practice_c_count']++;
                $diabetics[$cId]['practice_c_met'] = true;
                if (! $diabetics[$cId]['last_anthropometry_date']) {
                    $diabetics[$cId]['last_anthropometry_date'] = Carbon::parse($r->dt_atendimento);
                    $diabetics[$cId]['last_weight'] = round((float) $r->nu_peso, 2);
                    $diabetics[$cId]['last_height'] = round((float) $r->nu_altura, 2);
                }
            }

            // =========================================================================
            // PRÁTICA D: Visitas domiciliares de ACS/TACS nos últimos 12 meses (20 pts)
            // Mínimo de 2 visitas com intervalo >= 30 dias entre si.
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
                $visitsByCitizen[$cId][] = Carbon::parse($r->dt_visita);
            }

            foreach ($visitsByCitizen as $cId => $dates) {
                if (! isset($diabetics[$cId])) {
                    continue;
                }

                $countVisits = count($dates);
                $diabetics[$cId]['practice_d_count'] = $countVisits;
                $diabetics[$cId]['last_visit_date'] = end($dates);

                // Verifica se há pelo menos duas visitas com intervalo >= 30 dias
                if ($countVisits >= 2) {
                    for ($i = 0; $i < $countVisits - 1; $i++) {
                        for ($j = $i + 1; $j < $countVisits; $j++) {
                            if ($dates[$i]->diffInDays($dates[$j]) >= 30) {
                                $diabetics[$cId]['practice_d_met'] = true;
                                break 2;
                            }
                        }
                    }
                }
            }

            // =========================================================================
            // PRÁTICA E: Exames Laboratoriais de Hemoglobina Glicada (tb_fat_atd_ind_exames)
            // Procedimentos: 0202010503 (SIGTAP) e ABEX008 (AB)
            // =========================================================================
            $examSql = <<<SQL
                SELECT e.co_fat_cidadao_pec AS cidadao_id,
                       COALESCE(e.dt_resultado, e.dt_realizacao, e.dt_solicitacao, t.dt_registro) AS dt_exame,
                       e.nu_resultado_valor AS valor_resultado,
                       dp.co_proced
                FROM tb_fat_atd_ind_exames e
                JOIN tb_dim_procedimento dp ON dp.co_seq_dim_procedimento = e.co_dim_procedimento
                LEFT JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = e.co_dim_tempo
                WHERE e.co_fat_cidadao_pec IN ({$chunkMarks})
                  AND dp.co_proced IN ('0202010503', 'ABEX008')
                  AND (
                      (e.dt_resultado >= ? AND e.dt_resultado <= ?)
                      OR (e.dt_realizacao >= ? AND e.dt_realizacao <= ?)
                      OR (e.dt_solicitacao >= ? AND e.dt_solicitacao <= ?)
                      OR (t.dt_registro >= ? AND t.dt_registro <= ?)
                  )
                ORDER BY dt_exame DESC
            SQL;

            $examRows = $connection->select($examSql, array_merge(
                $chunk,
                [
                    $twelveMonthsStart->toDateString(), $evalDate->toDateString(),
                    $twelveMonthsStart->toDateString(), $evalDate->toDateString(),
                    $twelveMonthsStart->toDateString(), $evalDate->toDateString(),
                    $twelveMonthsStart->toDateString(), $evalDate->toDateString(),
                ]
            ));

            foreach ($examRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($diabetics[$cId]) || empty($r->dt_exame)) {
                    continue;
                }

                $dt = Carbon::parse($r->dt_exame);
                if ($dt->lt($twelveMonthsStart) || $dt->gt($evalDate)) {
                    continue;
                }

                $diabetics[$cId]['practice_e_count']++;
                $diabetics[$cId]['practice_e_met'] = true;

                if (! $diabetics[$cId]['last_hba1c_date'] || $dt->gt($diabetics[$cId]['last_hba1c_date'])) {
                    $diabetics[$cId]['last_hba1c_date'] = $dt;
                    $val = (float) ($r->valor_resultado ?? 0);
                    $code = trim((string) $r->co_proced);
                    if ($val > 0) {
                        $diabetics[$cId]['last_hba1c_type'] = number_format($val, 2, ',', '.') . '% (' . ($code === 'ABEX008' ? 'ABEX008' : 'SIGTAP') . ')';
                    } else {
                        $diabetics[$cId]['last_hba1c_type'] = $code === 'ABEX008' ? 'ABEX008' : 'SIGTAP 02.02.01.050-3';
                    }
                }
            }

            // =========================================================================
            // PRÁTICA E & F: Procedimentos no Atendimento Individual (tb_fat_atd_ind_procedimentos)
            // HbA1c (0202010503, ABEX008) e Avaliação do Pé Diabético (0301040095, ABPG011)
            // =========================================================================
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
                      dp_av.co_proced IN ('0202010503', 'ABEX008', '0301040095', 'ABPG011')
                      OR dp_sol.co_proced IN ('0202010503', 'ABEX008', '0301040095', 'ABPG011')
                  )
                ORDER BY t.dt_registro DESC
            SQL;

            $indProcRows = $connection->select($indProcSql, array_merge($chunk, [
                $twelveMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($indProcRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($diabetics[$cId]) || empty($r->dt_procedimento)) {
                    continue;
                }

                $dt = Carbon::parse($r->dt_procedimento);
                $procedAv = trim((string) ($r->proced_avaliado ?? ''));
                $procedSol = trim((string) ($r->proced_solicitado ?? ''));

                // Hemoglobina Glicada
                if (in_array($procedAv, self::SIGTAP_HBA1C, true) || in_array($procedSol, self::SIGTAP_HBA1C, true)) {
                    $code = in_array($procedAv, self::SIGTAP_HBA1C, true) ? $procedAv : $procedSol;
                    $diabetics[$cId]['practice_e_count']++;
                    $diabetics[$cId]['practice_e_met'] = true;
                    if (! $diabetics[$cId]['last_hba1c_date'] || $dt->gt($diabetics[$cId]['last_hba1c_date'])) {
                        $diabetics[$cId]['last_hba1c_date'] = $dt;
                        $diabetics[$cId]['last_hba1c_type'] = $code === 'ABEX008' ? 'ABEX008' : 'SIGTAP 02.02.01.050-3';
                    }
                }

                // Avaliação do Pé Diabético
                if (in_array($procedAv, self::SIGTAP_PE_DIABETICO, true) || in_array($procedSol, self::SIGTAP_PE_DIABETICO, true)) {
                    $diabetics[$cId]['practice_f_count']++;
                    $diabetics[$cId]['practice_f_met'] = true;
                    if (! $diabetics[$cId]['last_foot_exam_date'] || $dt->gt($diabetics[$cId]['last_foot_exam_date'])) {
                        $diabetics[$cId]['last_foot_exam_date'] = $dt;
                    }
                }
            }

            // =========================================================================
            // PRÁTICAS B, C, E, F via Ficha CDS de Procedimentos (tb_fat_proced_atend_proced)
            // SIGTAP: PA (0301100039), HbA1c (0202010503, ABEX008), Pé (0301040095, ABPG011), Antropo
            // =========================================================================
            $procSql = <<<SQL
                SELECT pp.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_procedimento,
                       dp.co_proced
                FROM tb_fat_proced_atend_proced pp
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = pp.co_dim_tempo
                JOIN tb_dim_procedimento dp ON dp.co_seq_dim_procedimento = pp.co_dim_procedimento
                WHERE pp.co_fat_cidadao_pec IN ({$chunkMarks})
                  AND t.dt_registro >= ? AND t.dt_registro <= ?
                  AND dp.co_proced IN ('0301100039', '0202010503', 'ABEX008', '0301040095', 'ABPG011', '0101040024')
                ORDER BY t.dt_registro DESC
            SQL;

            $procRows = $connection->select($procSql, array_merge($chunk, [
                $twelveMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($procRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($diabetics[$cId])) {
                    continue;
                }

                $code = trim((string) $r->co_proced);
                $dt = Carbon::parse($r->dt_procedimento);

                // Hemoglobina Glicada (Prática E - 15 pts) em 12 meses
                if (in_array($code, self::SIGTAP_HBA1C, true)) {
                    $diabetics[$cId]['practice_e_count']++;
                    $diabetics[$cId]['practice_e_met'] = true;
                    if (! $diabetics[$cId]['last_hba1c_date'] || $dt->gt($diabetics[$cId]['last_hba1c_date'])) {
                        $diabetics[$cId]['last_hba1c_date'] = $dt;
                        $diabetics[$cId]['last_hba1c_type'] = $code === 'ABEX008' ? 'ABEX008' : 'SIGTAP 02.02.01.050-3';
                    }
                }

                // Avaliação dos Pés (Prática F - 15 pts) em 12 meses
                if (in_array($code, self::SIGTAP_PE_DIABETICO, true)) {
                    $diabetics[$cId]['practice_f_count']++;
                    $diabetics[$cId]['practice_f_met'] = true;
                    if (! $diabetics[$cId]['last_foot_exam_date'] || $dt->gt($diabetics[$cId]['last_foot_exam_date'])) {
                        $diabetics[$cId]['last_foot_exam_date'] = $dt;
                    }
                }

                // PA via procedimento nos últimos 6 meses (se ainda não cumprida)
                if (in_array($code, self::SIGTAP_PA, true) && $dt->gte($sixMonthsStart)) {
                    $diabetics[$cId]['practice_b_count']++;
                    $diabetics[$cId]['practice_b_met'] = true;
                    if (! $diabetics[$cId]['last_pa_date'] || $dt->gt($diabetics[$cId]['last_pa_date'])) {
                        $diabetics[$cId]['last_pa_date'] = $dt;
                    }
                }

                // Antropometria via procedimento nos últimos 12 meses
                if (in_array($code, self::SIGTAP_ANTROPOMETRIA, true)) {
                    $diabetics[$cId]['practice_c_count']++;
                    $diabetics[$cId]['practice_c_met'] = true;
                    if (! $diabetics[$cId]['last_anthropometry_date'] || $dt->gt($diabetics[$cId]['last_anthropometry_date'])) {
                        $diabetics[$cId]['last_anthropometry_date'] = $dt;
                    }
                }
            }
        }
    }

    /**
     * Calcula as pontuações e consolida os dados de C4.
     *
     * @param  array<int, array<string, mixed>>  $diabetics
     * @param  array<string, array<string, mixed>>  $teams
     * @return array<string, mixed>
     */
    private function consolidate(
        array $diabetics,
        array $teams,
        int $year,
        int $quarter,
        Carbon $start,
        Carbon $end,
        Carbon $asOf
    ): array {
        $nominalList = [];
        $teamScores = [];
        $cohortCounts = [];
        $practicesCitywide = [
            'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0,
        ];

        foreach ($diabetics as $d) {
            $ine = $d['ine'];
            $isEap = ($d['team_type'] ?? '70') === '76';

            // Pontos oficiais conforme Quadro 01 da Nota Metodológica
            $ptsA = $d['practice_a_met'] ? 20.0 : 0.0;
            $ptsB = $d['practice_b_met'] ? 15.0 : 0.0;
            $ptsC = $d['practice_c_met'] ? 15.0 : 0.0;
            $ptsD = $d['practice_d_met'] ? 20.0 : 0.0;
            $ptsE = $d['practice_e_met'] ? 15.0 : 0.0;
            $ptsF = $d['practice_f_met'] ? 15.0 : 0.0;

            if ($isEap) {
                // eAP tipo 76: Não possui ACS; pontuação calculada sobre 80 pts e normalizada para base 100
                $rawScore = $ptsA + $ptsB + $ptsC + $ptsE + $ptsF;
                $score = round(min(100.0, $rawScore * 1.25), 2);
            } else {
                $score = round($ptsA + $ptsB + $ptsC + $ptsD + $ptsE + $ptsF, 2);
            }

            if ($d['practice_a_met']) $practicesCitywide['A']++;
            if ($d['practice_b_met']) $practicesCitywide['B']++;
            if ($d['practice_c_met']) $practicesCitywide['C']++;
            if ($d['practice_d_met']) $practicesCitywide['D']++;
            if ($d['practice_e_met']) $practicesCitywide['E']++;
            if ($d['practice_f_met']) $practicesCitywide['F']++;

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
                'district' => 'Sede',
                'ine' => $ine,
                'team_name' => $teams[$ine]['name'] ?? 'Equipe APS',
                'professional_cns' => null,
                'professional_name' => null,
                'microarea' => $d['microarea'] ?: '—',
                'ciap_codes' => 'T89, T90',
                'cid_codes' => 'E10, E11, E14',
                'first_diagnosis_date' => $d['first_diagnosis_date']?->toDateString(),
                'last_diagnosis_date' => $d['last_diagnosis_date']?->toDateString(),
                'condition_status' => 'ativo',
                'month_ref' => sprintf('%02d/%d', $asOf->month, $year),
                'mici_updated' => true,
                'is_accompanied' => $score >= 50.0,
                'practice_a' => $d['practice_a_count'],
                'practice_a_met' => $d['practice_a_met'],
                'last_consultation_date' => $d['last_consultation_date']?->toDateString(),
                'practice_b' => $d['practice_b_count'],
                'practice_b_met' => $d['practice_b_met'],
                'last_pa_date' => $d['last_pa_date']?->toDateString(),
                'last_pa_value' => $d['last_pa_value'],
                'practice_c' => $d['practice_c_count'],
                'practice_c_met' => $d['practice_c_met'],
                'last_anthropometry_date' => $d['last_anthropometry_date']?->toDateString(),
                'last_weight' => $d['last_weight'],
                'last_height' => $d['last_height'],
                'practice_d' => $d['practice_d_count'],
                'practice_d_met' => $d['practice_d_met'],
                'last_visit_date' => $d['last_visit_date']?->toDateString(),
                'practice_e' => $d['practice_e_count'],
                'practice_e_met' => $d['practice_e_met'],
                'last_hba1c_date' => $d['last_hba1c_date']?->toDateString(),
                'last_hba1c_type' => $d['last_hba1c_type'],
                'practice_f' => $d['practice_f_count'],
                'practice_f_met' => $d['practice_f_met'],
                'last_foot_exam_date' => $d['last_foot_exam_date']?->toDateString(),
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
        $scoresReport = [];
        $monthsInQuarter = match ($quarter) {
            1 => [1, 2, 3, 4],
            2 => [5, 6, 7, 8],
            default => [9, 10, 11, 12],
        };

        foreach ($teams as $ine => $t) {
            $count = $teamScores[$ine]['total'] ?? 0;
            $avgScore = $count > 0 ? round(array_sum($teamScores[$ine]['scores']) / $count, 2) : 0.0;
            $level = FamilyHealthService::calculatePerformanceLevel('c4', $avgScore);
            $c3Points = FamilyHealthService::calculateComponentIIIPoints($level, 1.0);

            // Coorte distribuída nos meses do quadrimestre
            $monthlyArray = [];
            foreach ($monthsInQuarter as $m) {
                $monthlyArray[$m] = $count;
            }

            $scoresReport[$ine] = [
                'ine' => $ine,
                'team_name' => $t['name'],
                'team_type' => $t['type'],
                'cnes' => $t['cnes'] ?? '—',
                'facility_name' => $t['facility_name'] ?? 'Unidade Básica de Saúde',
                'cohort_total' => $count,
                'evaluated_total' => $count,
                'score_percent' => $avgScore,
                'performance_level' => $level,
                'component_iii_points' => $c3Points,
                'monthly_counts' => $monthlyArray,
            ];
        }

        return [
            'scores' => $scoresReport,
            'cohort' => $cohortCounts,
            'as_of' => $asOf->toDateString(),
            'diabetics' => $nominalList,
            'practices_citywide' => $practicesCitywide,
        ];
    }

    private function isCboDoctorOrNurse(string $cbo): bool
    {
        $clean = preg_replace('/\D+/', '', $cbo);
        foreach (self::CBOS_CONSULTA_MEDICO as $prefix) {
            if (str_starts_with($clean, $prefix)) return true;
        }
        foreach (self::CBOS_CONSULTA_ENFERMEIRO as $prefix) {
            if (str_starts_with($clean, $prefix)) return true;
        }

        return false;
    }

    /** @return array<string, bool> */
    private function tableColumns(ConnectionInterface $connection, string $table): array
    {
        $rows = $connection->select(
            "SELECT column_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ?",
            [$table]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row->column_name] = true;
        }

        return $result;
    }

    private function marks(int $count): string
    {
        return implode(',', array_fill(0, max(1, $count), '?'));
    }
}
