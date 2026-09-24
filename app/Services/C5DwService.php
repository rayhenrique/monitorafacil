<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

/**
 * Extração do Indicador C5 (Cuidado da Pessoa com Hipertensão) no DW do PEC.
 *
 * Baseado estritamente na Nota Metodológica C5 e na Nota Técnica 08/2026.
 * Avalia o acompanhamento contínuo e as 4 boas práticas de cuidado (A a D) somando 100 pontos:
 *  - (A) [25 pts] Consulta médica ou de enfermagem nos últimos 6 meses
 *  - (B) [25 pts] Aferição de Pressão Arterial nos últimos 6 meses (CBOs habilitados)
 *  - (C) [25 pts] Registro simultâneo de Peso e Altura no mesmo dia nos últimos 12 meses
 *  - (D) [25 pts] Ao menos 2 visitas domiciliares por ACS/TACS com intervalo >= 30 dias nos últimos 12 meses (eAP tipo 76 normalizada)
 */
class C5DwService
{
    public const VERSION = 'dw-c5-2026-09-normative-v1.0';

    private const CHUNK_SIZE = 500;

    // CIAP-2 de Hipertensão Arterial
    private const HYPERTENSION_CIAPS = ['K86', 'K87'];

    // CID-10 de Hipertensão Arterial (I10, I11, I12, I13, I15, O10, O11 e seus subcódigos)
    private const HYPERTENSION_CID_PREFIXES = ['I10', 'I11', 'I12', 'I13', 'I15', 'O10', 'O11'];

    // Códigos SIGTAP das Boas Práticas
    private const SIGTAP_PA = ['0301100039'];
    private const SIGTAP_ANTROPOMETRIA = ['0101040024'];
    private const SIGTAP_PESO = ['0101040083'];
    private const SIGTAP_ALTURA = ['0101040075'];
    private const SIGTAP_CONSULTA = ['0301010064', '0301010030', '0301010250'];

    // CBOs habilitados para Consultas (A)
    private const CBOS_CONSULTA_MEDICO = ['2251', '2252', '2253', '2231'];
    private const CBOS_CONSULTA_ENFERMEIRO = ['2235'];

    // CBOs habilitados para Aferição de Pressão Arterial (B) - Nota Metodológica Quadro 03 e Nota de Rodapé 4
    // CBO 5151-05 (ACS) NÃO é permitido para pontuação de PA
    private const CBOS_PA = [
        '2251', '2252', '2253', '2231', // Médicos
        '2235',                         // Enfermeiros
        '3222',                         // Técnico/Auxiliar de Enfermagem e Técnico em ACS (3222-55)
        '2232',                         // Cirurgiões-dentistas
        '2234',                         // Farmacêuticos
        '2236',                         // Fisioterapeutas
        '2238',                         // Fonoaudiólogos
        '2237',                         // Nutricionistas
        '2241',                         // Profissionais de Educação Física
        '2239',                         // Terapeutas Ocupacionais, Ortoptistas e Psicomotricistas
        '3224',                         // Técnicos em Saúde Bucal
    ];

    // CBOs habilitados para Visitas Domiciliares (D)
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
            throw new RuntimeException('C5: nenhuma equipe eSF/eAP válida foi identificada no PEC.');
        }

        $firstMonth = (($quarter - 1) * 4) + 1;
        $start = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $end = (clone $start)->addMonths(4)->subDay()->endOfDay();
        $asOf = Carbon::today();
        $evalDate = $asOf->lt($end) ? $asOf->copy()->endOfDay() : $end;

        // Janelas regulamentares de boas práticas:
        // 6 meses para Consulta (A) e Pressão Arterial (B)
        $sixMonthsStart = (clone $evalDate)->subMonths(6)->startOfDay();
        // 12 meses para Peso/Altura (C) e Visitas ACS (D)
        $twelveMonthsStart = (clone $evalDate)->subMonths(12)->startOfDay();

        // 1. Carrega cidadãos vinculados às equipes
        $citizens = $this->loadLinkedCitizens($connection, $teams, $asOf);
        if ($citizens === []) {
            return $this->emptyResult($asOf);
        }

        // 2. Identifica pessoas com condição avaliada de Hipertensão (desde 2013)
        $hypertensives = $this->identifyHypertensives($connection, $citizens, $evalDate);
        unset($citizens);
        if ($hypertensives === []) {
            return $this->emptyResult($asOf);
        }

        // 3. Carrega os eventos clínicos das 4 boas práticas
        $this->loadClinicalEvents($connection, $hypertensives, $sixMonthsStart, $twelveMonthsStart, $evalDate);

        // 4. Calcula pontuações individuais e consolida por equipe e município
        return $this->consolidate($hypertensives, $teams, $year, $quarter, $start, $end, $asOf);
    }

    /** @return array<string, mixed> */
    private function emptyResult(Carbon $asOf): array
    {
        return [
            'scores' => [],
            'cohort' => [],
            'as_of' => $asOf->toDateString(),
            'hypertensives' => [],
            'municipal_summary' => [],
            'practices_citywide' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
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
                throw new RuntimeException("C5: a visão tb_acomp_cidadaos_vinculados não contém a coluna obrigatória {$required}.");
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

            // A nota técnica exige nome, nascimento e ao menos CPF ou CNS com formato válido.
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
     * Identifica pessoas com Hipertensão elegíveis na coorte.
     *
     * @param  array<int, array<string, mixed>>  $citizens
     * @return array<int, array<string, mixed>>
     */
    private function identifyHypertensives(ConnectionInterface $connection, array $citizens, Carbon $evalDate): array
    {
        $citizenIds = array_keys($citizens);
        $hypertensives = [];

        // Recupera IDs de CIAP (K86, K87)
        $ciapRows = $connection->select("SELECT co_seq_dim_ciap AS id, nu_ciap FROM tb_dim_ciap WHERE nu_ciap IN ('K86', 'K87')");
        $ciapIds = array_map(fn ($r) => (int) $r->id, $ciapRows);

        // Recupera IDs de CID (I10, I11, I12, I13, I15, O10, O11 e seus subcódigos)
        $cidRows = $connection->select("
            SELECT co_seq_dim_cid AS id, nu_cid
            FROM tb_dim_cid
            WHERE nu_cid LIKE 'I10%'
               OR nu_cid LIKE 'I11%'
               OR nu_cid LIKE 'I12%'
               OR nu_cid LIKE 'I13%'
               OR nu_cid LIKE 'I15%'
               OR nu_cid LIKE 'O10%'
               OR nu_cid LIKE 'O11%'
        ");
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

                if (! isset($hypertensives[$cId])) {
                    $bornCarbon = Carbon::parse($citizens[$cId]['born'])->startOfDay();
                    $hypertensives[$cId] = array_merge($citizens[$cId], [
                        'born' => $bornCarbon,
                        'age_years' => (int) $bornCarbon->diffInYears($evalDate),
                        'first_diagnosis_date' => $dt,
                        'last_diagnosis_date' => $dt,
                        'has_active' => ! $isResolved,
                        'ciap_codes' => [],
                        'cid_codes' => [],
                        // Inicializa dados das 4 práticas
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
                        'score_percent' => 0.0,
                    ]);
                } else {
                    if (! $isResolved) {
                        $hypertensives[$cId]['has_active'] = true;
                    }
                    if ($dt->gt($hypertensives[$cId]['last_diagnosis_date'])) {
                        $hypertensives[$cId]['last_diagnosis_date'] = $dt;
                    }
                }
            }
        }

        // Filtra apenas aqueles com condição ativa (não apenas resolvidos)
        return array_filter($hypertensives, fn ($d) => $d['has_active'] === true);
    }

    /**
     * Carrega os eventos clínicos das 4 boas práticas para os hipertensos.
     *
     * @param  array<int, array<string, mixed>>  $hypertensives
     */
    private function loadClinicalEvents(
        ConnectionInterface $connection,
        array &$hypertensives,
        Carbon $sixMonthsStart,
        Carbon $twelveMonthsStart,
        Carbon $evalDate
    ): void {
        $citizenIds = array_keys($hypertensives);
        if ($citizenIds === []) {
            return;
        }

        foreach (array_chunk($citizenIds, self::CHUNK_SIZE) as $chunk) {
            $chunkMarks = $this->marks(count($chunk));

            // =========================================================================
            // PRÁTICA A: Consultas médicas ou de enfermagem nos últimos 6 meses (25 pts)
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
                if (! isset($hypertensives[$cId])) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                $isDoctorOrNurse = $this->isCboDoctorOrNurse($cbo1) || $this->isCboDoctorOrNurse($cbo2);

                if ($isDoctorOrNurse) {
                    $hypertensives[$cId]['practice_a_count']++;
                    $hypertensives[$cId]['practice_a_met'] = true;
                    if (! $hypertensives[$cId]['last_consultation_date']) {
                        $hypertensives[$cId]['last_consultation_date'] = Carbon::parse($r->dt_atendimento);
                    }
                }
            }

            // =========================================================================
            // PRÁTICA B: Aferição de Pressão Arterial nos últimos 6 meses (25 pts)
            // CBOs habilitados: Médicos, Enfermeiros, Téc/Aux Enfermagem, Dentistas,
            // Farmacêuticos, Fisioterapeutas, Fonoaudiólogos, Nutricionistas, Ed. Física,
            // TO, TSB, TACS (3222-55). OBS: Excluído ACS 5151-05 conforme NT.
            // =========================================================================
            $paSql = <<<SQL
                SELECT a.co_fat_cidadao_pec AS cidadao_id,
                       t.dt_registro AS dt_atendimento,
                       a.nu_pressao_sistolica,
                       a.nu_pressao_diastolica,
                       cbo1.nu_cbo AS cbo1,
                       cbo2.nu_cbo AS cbo2
                FROM tb_fat_atendimento_individual a
                JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = a.co_dim_tempo
                LEFT JOIN tb_dim_cbo cbo1 ON cbo1.co_seq_dim_cbo = a.co_dim_cbo_1
                LEFT JOIN tb_dim_cbo cbo2 ON cbo2.co_seq_dim_cbo = a.co_dim_cbo_2
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
                if (! isset($hypertensives[$cId])) {
                    continue;
                }

                $cbo1 = trim((string) ($r->cbo1 ?? ''));
                $cbo2 = trim((string) ($r->cbo2 ?? ''));
                if (! $this->isCboAllowedForPa($cbo1) && ! $this->isCboAllowedForPa($cbo2)) {
                    continue;
                }

                $hypertensives[$cId]['practice_b_count']++;
                $hypertensives[$cId]['practice_b_met'] = true;
                if (! $hypertensives[$cId]['last_pa_date']) {
                    $hypertensives[$cId]['last_pa_date'] = Carbon::parse($r->dt_atendimento);
                    $pas = round((float) $r->nu_pressao_sistolica);
                    $pad = round((float) $r->nu_pressao_diastolica);
                    $hypertensives[$cId]['last_pa_value'] = ($pas > 0 && $pad > 0) ? "{$pas}/{$pad}" : null;
                }
            }

            // =========================================================================
            // PRÁTICA C: Antropometria (Peso e Altura Simultâneos no mesmo dia) em 12 meses (25 pts)
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
                if (! isset($hypertensives[$cId])) {
                    continue;
                }

                $hypertensives[$cId]['practice_c_count']++;
                $hypertensives[$cId]['practice_c_met'] = true;
                if (! $hypertensives[$cId]['last_anthropometry_date']) {
                    $hypertensives[$cId]['last_anthropometry_date'] = Carbon::parse($r->dt_atendimento);
                    $hypertensives[$cId]['last_weight'] = round((float) $r->nu_peso, 2);
                    $hypertensives[$cId]['last_height'] = round((float) $r->nu_altura, 2);
                }
            }

            // =========================================================================
            // PRÁTICA D: Visitas domiciliares de ACS/TACS nos últimos 12 meses (25 pts)
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
                $cbo = trim((string) ($r->nu_cbo ?? ''));
                if ($this->isCboAcsOrTacs($cbo)) {
                    $visitsByCitizen[$cId][] = Carbon::parse($r->dt_visita);
                }
            }

            foreach ($visitsByCitizen as $cId => $dates) {
                if (! isset($hypertensives[$cId])) {
                    continue;
                }

                $countVisits = count($dates);
                $hypertensives[$cId]['practice_d_count'] = $countVisits;
                $hypertensives[$cId]['last_visit_date'] = end($dates);

                // Verifica se há pelo menos duas visitas com intervalo >= 30 dias
                if ($countVisits >= 2) {
                    for ($i = 0; $i < $countVisits - 1; $i++) {
                        for ($j = $i + 1; $j < $countVisits; $j++) {
                            if ($dates[$i]->diffInDays($dates[$j]) >= 30) {
                                $hypertensives[$cId]['practice_d_met'] = true;
                                break 2;
                            }
                        }
                    }
                }
            }

            // =========================================================================
            // PRÁTICAS B e C via Procedimentos no Atendimento Individual (tb_fat_atd_ind_procedimentos)
            // PA (0301100039), Antropometria (0101040024)
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
                      dp_av.co_proced IN ('0301100039', '0101040024')
                      OR dp_sol.co_proced IN ('0301100039', '0101040024')
                  )
                ORDER BY t.dt_registro DESC
            SQL;

            $indProcRows = $connection->select($indProcSql, array_merge($chunk, [
                $twelveMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($indProcRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($hypertensives[$cId]) || empty($r->dt_procedimento)) {
                    continue;
                }

                $dt = Carbon::parse($r->dt_procedimento);
                $procedAv = trim((string) ($r->proced_avaliado ?? ''));
                $procedSol = trim((string) ($r->proced_solicitado ?? ''));

                // PA nos últimos 6 meses
                if ((in_array($procedAv, self::SIGTAP_PA, true) || in_array($procedSol, self::SIGTAP_PA, true)) && $dt->gte($sixMonthsStart)) {
                    $hypertensives[$cId]['practice_b_count']++;
                    $hypertensives[$cId]['practice_b_met'] = true;
                    if (! $hypertensives[$cId]['last_pa_date'] || $dt->gt($hypertensives[$cId]['last_pa_date'])) {
                        $hypertensives[$cId]['last_pa_date'] = $dt;
                    }
                }

                // Antropometria nos últimos 12 meses
                if (in_array($procedAv, self::SIGTAP_ANTROPOMETRIA, true) || in_array($procedSol, self::SIGTAP_ANTROPOMETRIA, true)) {
                    $hypertensives[$cId]['practice_c_count']++;
                    $hypertensives[$cId]['practice_c_met'] = true;
                    if (! $hypertensives[$cId]['last_anthropometry_date'] || $dt->gt($hypertensives[$cId]['last_anthropometry_date'])) {
                        $hypertensives[$cId]['last_anthropometry_date'] = $dt;
                    }
                }
            }

            // =========================================================================
            // PRÁTICAS B e C via Ficha CDS de Procedimentos (tb_fat_proced_atend_proced)
            // SIGTAP: PA (0301100039), Antropometria (0101040024)
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
                  AND dp.co_proced IN ('0301100039', '0101040024')
                ORDER BY t.dt_registro DESC
            SQL;

            $procRows = $connection->select($procSql, array_merge($chunk, [
                $twelveMonthsStart->toDateString(),
                $evalDate->toDateString(),
            ]));

            foreach ($procRows as $r) {
                $cId = (int) $r->cidadao_id;
                if (! isset($hypertensives[$cId])) {
                    continue;
                }

                $code = trim((string) $r->co_proced);
                $dt = Carbon::parse($r->dt_procedimento);

                // PA nos últimos 6 meses
                if (in_array($code, self::SIGTAP_PA, true) && $dt->gte($sixMonthsStart)) {
                    $hypertensives[$cId]['practice_b_count']++;
                    $hypertensives[$cId]['practice_b_met'] = true;
                    if (! $hypertensives[$cId]['last_pa_date'] || $dt->gt($hypertensives[$cId]['last_pa_date'])) {
                        $hypertensives[$cId]['last_pa_date'] = $dt;
                    }
                }

                // Antropometria nos últimos 12 meses
                if (in_array($code, self::SIGTAP_ANTROPOMETRIA, true)) {
                    $hypertensives[$cId]['practice_c_count']++;
                    $hypertensives[$cId]['practice_c_met'] = true;
                    if (! $hypertensives[$cId]['last_anthropometry_date'] || $dt->gt($hypertensives[$cId]['last_anthropometry_date'])) {
                        $hypertensives[$cId]['last_anthropometry_date'] = $dt;
                    }
                }
            }
        }
    }

    /**
     * Calcula as pontuações e consolida os dados de C5.
     *
     * @param  array<int, array<string, mixed>>  $hypertensives
     * @param  array<string, array<string, mixed>>  $teams
     * @return array<string, mixed>
     */
    private function consolidate(
        array $hypertensives,
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
            'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0,
        ];

        foreach ($hypertensives as $d) {
            $ine = $d['ine'];
            $isEap = ($d['team_type'] ?? '70') === '76';

            // Pontos oficiais conforme Quadro 01 da Nota Metodológica C5:
            // Cada boa prática pontua exatamente 25 pontos
            $ptsA = $d['practice_a_met'] ? 25.0 : 0.0;
            $ptsB = $d['practice_b_met'] ? 25.0 : 0.0;
            $ptsC = $d['practice_c_met'] ? 25.0 : 0.0;
            $ptsD = $d['practice_d_met'] ? 25.0 : 0.0;

            if ($isEap) {
                // eAP tipo 76: A boa prática (D) não é condicionante de pontuação (Portaria GM/MS nº 3.493/2024 e PRC GM/MS nº 02/2017).
                // Pontuação calculada sobre 75 pontos e normalizada para a base 100:
                $rawScore = $ptsA + $ptsB + $ptsC;
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
                'district' => 'Sede',
                'ine' => $ine,
                'team_name' => $teams[$ine]['name'] ?? 'Equipe APS',
                'professional_cns' => null,
                'professional_name' => null,
                'microarea' => $d['microarea'] ?: '—',
                'ciap_codes' => 'K86, K87',
                'cid_codes' => 'I10 a I15, O10, O11',
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
            $level = FamilyHealthService::calculatePerformanceLevel('c5', $avgScore);
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
            'hypertensives' => $nominalList,
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

    private function isCboAllowedForPa(string $cbo): bool
    {
        $clean = preg_replace('/\D+/', '', $cbo);
        // Exclusão estrita do Agente Comunitário de Saúde (5151-05 / 515105)
        if (str_starts_with($clean, '5151')) {
            return false;
        }

        foreach (self::CBOS_PA as $prefix) {
            if (str_starts_with($clean, $prefix)) return true;
        }

        return false;
    }

    private function isCboAcsOrTacs(string $cbo): bool
    {
        $clean = preg_replace('/\D+/', '', $cbo);
        foreach (self::CBOS_ACS as $target) {
            $targetClean = preg_replace('/\D+/', '', $target);
            if (str_starts_with($clean, $targetClean)) return true;
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
