<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;

/**
 * Extração DW do Indicador B2 - Tratamento Odontológico Concluído.
 *
 * Conforme Nota Metodológica B2 (MS/SAPS/DESCO):
 * - Numerador: Pessoas com tratamento concluído pela eSB (st_conduta_tratamento_concluid = 1) em até 12 meses após a 1ª consulta.
 * - Denominador: Pessoas com primeira consulta odontológica programática no período.
 * - Parâmetros: Ótimo (> 75 e ≤ 100), Bom (> 50 e ≤ 75), Suficiente (> 25 e ≤ 50), Regular (≤ 25).
 */
class B2DwService
{
    public const VERSION = 'dw-b2-2026-09-normative-v1.0';

    public function __construct(
        private readonly OralHealthPracticeCalculator $calculator
    ) {}

    /**
     * @param array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}> $teams
     * @return array{
     *     indicator_code: string,
     *     teams_data: array<string, array<string, mixed>>,
     *     nominals: list<array<string, mixed>>,
     *     municipal: array<string, mixed>
     * }
     */
    public function extract(ConnectionInterface $pec, int $year, int $quarter, array $teams): array
    {
        $firstMonth = (($quarter - 1) * 4) + 1;
        $startDate = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $endDate = (clone $startDate)->addMonths(4)->subDay()->endOfDay();
        $evalDate = Carbon::today()->lt($endDate) ? Carbon::today()->endOfDay() : $endDate;

        $startTempo = (int) $startDate->format('Ymd');
        $endTempo = (int) $evalDate->format('Ymd');

        // Extrai todas as primeiras consultas e status de conclusão no período
        $rows = $pec->select("
            SELECT
                e.nu_ine,
                fao.co_fat_cidadao_pec,
                fao.nu_cpf_cidadao,
                fao.nu_cns,
                fao.dt_nascimento,
                t.nu_mes,
                t.dt_registro,
                fao.st_conduta_tratamento_concluid,
                p.no_profissional AS professional_name,
                cbo.nu_cbo AS professional_cbo,
                u.nu_cnes,
                u.no_unidade_saude AS facility_name,
                e.no_equipe AS team_name
            FROM tb_fat_atendimento_odonto fao
            JOIN tb_dim_equipe e ON e.co_seq_dim_equipe = fao.co_dim_equipe_1
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = fao.co_dim_tempo
            LEFT JOIN tb_dim_profissional p ON p.co_seq_dim_profissional = fao.co_dim_profissional_1
            LEFT JOIN tb_dim_cbo cbo ON cbo.co_seq_dim_cbo = fao.co_dim_cbo_1
            LEFT JOIN tb_dim_unidade_saude u ON u.co_seq_dim_unidade_saude = fao.co_dim_unidade_saude_1
            WHERE fao.co_dim_tempo BETWEEN ? AND ?
              AND fao.co_dim_tipo_consulta = 1
            ORDER BY t.dt_registro ASC
        ", [$startTempo, $endTempo]);

        $teamDenominators = [];
        $teamNumerators = [];
        $teamMonthlyNum = [];
        $teamMonthlyDen = [];
        $seenFirstConsults = [];
        $nominals = [];

        foreach ($rows as $r) {
            $ine = trim((string) $r->nu_ine);
            if ($ine === '' || $ine === '-') {
                continue;
            }

            $cidKey = ! empty($r->nu_cpf_cidadao) ? 'cpf_' . trim((string) $r->nu_cpf_cidadao) :
                     (! empty($r->nu_cns) ? 'cns_' . trim((string) $r->nu_cns) : 'pec_' . (string) $r->co_fat_cidadao_pec);

            // Apenas 1 registro de 1ª consulta por paciente por período
            if (isset($seenFirstConsults[$ine][$cidKey])) {
                continue;
            }
            $seenFirstConsults[$ine][$cidKey] = true;

            $month = (int) $r->nu_mes;
            $teamDenominators[$ine] = ($teamDenominators[$ine] ?? 0) + 1;
            $teamMonthlyDen[$ine][$month] = ($teamMonthlyDen[$ine][$month] ?? 0) + 1;

            $isCompleted = ((int) $r->st_conduta_tratamento_concluid) === 1;
            if ($isCompleted) {
                $teamNumerators[$ine] = ($teamNumerators[$ine] ?? 0) + 1;
                $teamMonthlyNum[$ine][$month] = ($teamMonthlyNum[$ine][$month] ?? 0) + 1;
            }

            $birthDate = ! empty($r->dt_nascimento) ? substr((string) $r->dt_nascimento, 0, 10) : null;
            $ageYears = 0;
            if ($birthDate) {
                $birthYear = (int) substr($birthDate, 0, 4);
                $ageYears = max(0, $year - $birthYear);
            }

            $nominals[] = [
                'indicator_code' => 'b2',
                'cidadao_pec_id' => $r->co_fat_cidadao_pec ? (int) $r->co_fat_cidadao_pec : null,
                'cns' => $r->nu_cns ? trim((string) $r->nu_cns) : null,
                'cpf' => $r->nu_cpf_cidadao ? trim((string) $r->nu_cpf_cidadao) : null,
                'name' => 'Cidadão ' . substr(md5($cidKey), 0, 8),
                'birth_date' => $birthDate ?? '1990-01-01',
                'age_years' => $ageYears,
                'cnes' => $r->nu_cnes ? trim((string) $r->nu_cnes) : null,
                'facility_name' => $r->facility_name ? trim((string) $r->facility_name) : null,
                'ine' => $ine,
                'team_name' => $r->team_name ? trim((string) $r->team_name) : null,
                'professional_name' => $r->professional_name ? trim((string) $r->professional_name) : null,
                'professional_cbo' => $r->professional_cbo ? trim((string) $r->professional_cbo) : null,
                'first_consultation_date' => substr((string) $r->dt_registro, 0, 10),
                'treatment_completed_date' => $isCompleted ? substr((string) $r->dt_registro, 0, 10) : null,
                'treatment_status' => $isCompleted ? 'concluido' : 'em_andamento',
                'has_first_consultation' => true,
                'has_treatment_completed' => $isCompleted,
                'score_percent' => $isCompleted ? 100.0 : 0.0,
            ];
        }

        // Consolida por equipe
        $teamsData = [];
        $mNum = 0;
        $mDen = 0;

        foreach ($teams as $ine => $teamInfo) {
            $num = $teamNumerators[$ine] ?? 0;
            $den = $teamDenominators[$ine] ?? 0;
            $res = $this->calculator->calculateB2($num, $den);

            $mNum += $num;
            $mDen += $den;

            $teamsData[$ine] = [
                'ine' => $ine,
                'team_name' => $teamInfo['name'],
                'cnes' => $teamInfo['cnes'] ?? null,
                'facility_name' => $teamInfo['facility_name'] ?? null,
                'team_type' => $teamInfo['type'],
                'numerator' => $num,
                'denominator' => $den,
                'rate' => $res['rate'],
                'score_percent' => $res['rate'],
                'performance_level' => $res['performance_level'],
                'points' => $res['points'],
                'monthly_num' => $teamMonthlyNum[$ine] ?? [],
                'monthly_den' => $teamMonthlyDen[$ine] ?? [],
            ];
        }

        $mRes = $this->calculator->calculateB2($mNum, $mDen);
        $municipal = [
            'indicator_code' => 'b2',
            'numerator' => $mNum,
            'denominator' => $mDen,
            'rate' => $mRes['rate'],
            'score_percent' => $mRes['rate'],
            'performance_level' => $mRes['performance_level'],
            'points' => $mRes['points'],
        ];

        return [
            'indicator_code' => 'b2',
            'teams_data' => $teamsData,
            'nominals' => $nominals,
            'municipal' => $municipal,
        ];
    }
}
