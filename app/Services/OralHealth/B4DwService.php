<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;

/**
 * Extração DW do Indicador B4 - Escovação Dental Supervisionada.
 *
 * Conforme Nota Metodológica B4 (MS/SAPS/DESCO):
 * - Numerador: Crianças de 6 a 12 anos participantes de ação coletiva de escovação supervisionada realizada pela eSB.
 * - Denominador: População de 6 a 12 anos vinculada à eSF/eAP de referência da eSB.
 * - Parâmetros: Ótimo (> 1), Bom (> 0,5 e ≤ 1), Suficiente (> 0,25 e ≤ 0,5), Regular (≤ 0,25).
 */
class B4DwService
{
    public const VERSION = 'dw-b4-2026-09-normative-v1.0';

    public function __construct(
        private readonly OralHealthPracticeCalculator $calculator
    ) {}

    /**
     * @param array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}> $teams
     * @param array<string, int> $linkedChildrenPopulations
     * @return array{
     *     indicator_code: string,
     *     teams_data: array<string, array<string, mixed>>,
     *     nominals: list<array<string, mixed>>,
     *     municipal: array<string, mixed>
     * }
     */
    public function extract(ConnectionInterface $pec, int $year, int $quarter, array $teams, array $linkedChildrenPopulations): array
    {
        $firstMonth = (($quarter - 1) * 4) + 1;
        $startDate = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $endDate = (clone $startDate)->addMonths(4)->subDay()->endOfDay();
        $evalDate = Carbon::today()->lt($endDate) ? Carbon::today()->endOfDay() : $endDate;

        $startTempo = (int) $startDate->format('Ymd');
        $endTempo = (int) $evalDate->format('Ymd');

        // Extrai participantes de ações coletivas de escovação dental supervisionada
        $rows = $pec->select("
            SELECT
                e.nu_ine,
                t.nu_mes,
                fac.nu_participantes,
                fac.nu_participantes_registrados,
                t.dt_registro,
                u.nu_cnes,
                u.no_unidade_saude AS facility_name,
                e.no_equipe AS team_name
            FROM tb_fat_atividade_coletiva fac
            JOIN tb_dim_equipe e ON e.co_seq_dim_equipe = fac.co_dim_equipe
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = fac.co_dim_tempo
            LEFT JOIN tb_dim_unidade_saude u ON u.co_seq_dim_unidade_saude = fac.co_dim_unidade_saude
            WHERE fac.co_dim_tempo BETWEEN ? AND ?
              AND (
                  fac.ds_filtro_pratica_em_saude LIKE '%|9|%'
                  OR fac.ds_filtro_pratica_em_saude LIKE '%|04|%'
                  OR fac.co_dim_procedimento = 1930 -- 01.01.02.003-1
                  OR EXISTS (
                      SELECT 1 FROM rl_ativ_col_pratica_saude ps
                      WHERE ps.co_pratica_saude = 9
                  )
              )
            ORDER BY t.dt_registro ASC
        ", [$startTempo, $endTempo]);

        $teamNum = [];
        $teamMonthlyNum = [];
        $nominals = [];

        foreach ($rows as $r) {
            $ine = trim((string) $r->nu_ine);
            if ($ine === '' || $ine === '-') {
                continue;
            }

            $count = (int) ($r->nu_participantes_registrados ?: $r->nu_participantes ?: 1);
            $month = (int) $r->nu_mes;

            $teamNum[$ine] = ($teamNum[$ine] ?? 0) + $count;
            $teamMonthlyNum[$ine][$month] = ($teamMonthlyNum[$ine][$month] ?? 0) + $count;
        }

        // Consolida por equipe
        $teamsData = [];
        $mNum = 0;
        $mDen = 0;

        foreach ($teams as $ine => $teamInfo) {
            $num = $teamNum[$ine] ?? 0;
            $den = $linkedChildrenPopulations[$ine] ?? 350; // População de 6 a 12 anos de referência
            $res = $this->calculator->calculateB4($num, $den);

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
                'score_percent' => round($res['rate'] * 100, 2),
                'performance_level' => $res['performance_level'],
                'points' => $res['points'],
                'monthly_num' => $teamMonthlyNum[$ine] ?? [],
            ];
        }

        $mRes = $this->calculator->calculateB4($mNum, $mDen);
        $municipal = [
            'indicator_code' => 'b4',
            'numerator' => $mNum,
            'denominator' => $mDen,
            'rate' => $mRes['rate'],
            'score_percent' => round($mRes['rate'] * 100, 2),
            'performance_level' => $mRes['performance_level'],
            'points' => $mRes['points'],
        ];

        return [
            'indicator_code' => 'b4',
            'teams_data' => $teamsData,
            'nominals' => $nominals,
            'municipal' => $municipal,
        ];
    }
}
