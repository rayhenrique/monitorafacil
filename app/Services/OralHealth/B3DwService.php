<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;

/**
 * Extração DW do Indicador B3 - Taxa de Exodontia de Dentes Permanentes.
 *
 * Conforme Nota Metodológica B3 (MS/SAPS/DESCO):
 * - Numerador: Exodontias de dentes permanentes realizadas por CD da eSB (SIGTAP 04.14.02.013-8 e 04.14.02.014-6).
 * - Denominador: Total de procedimentos individuais preventivos, curativos e exodontias realizados pela eSB (CD + TSB).
 * - Parâmetros (Polaridade: Menor-Melhor):
 *   Ótimo (≥ 3 e < 10), Bom (≥ 10 e < 12), Suficiente (≥ 12 e < 14), Regular (< 3 ou ≥ 14).
 */
class B3DwService
{
    public const VERSION = 'dw-b3-2026-09-normative-v1.0';

    private const CD_CBOS = [661, 662, 1016]; // 2232-08, 2232-93, 2232-72
    private const TSB_CBOS = [930, 931]; // 3224-05, 3224-25
    private const EXODONTIA_PROCEDS = [1604, 2065]; // 04.14.02.013-8, 04.14.02.014-6

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

        // Extrai soma de procedimentos por equipe e mês
        $rows = $pec->select("
            SELECT
                e.nu_ine,
                t.nu_mes,
                SUM(CASE WHEN faop.co_dim_procedimento IN (1604, 2065) AND faop.co_dim_cbo_1 IN (661, 662, 1016)
                         THEN faop.qt_procedimentos ELSE 0 END) AS num_exodontias,
                SUM(faop.qt_procedimentos) AS den_procedimentos
            FROM tb_fat_atend_odonto_proced faop
            JOIN tb_dim_equipe e ON e.co_seq_dim_equipe = faop.co_dim_equipe_1
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = faop.co_dim_tempo
            WHERE faop.co_dim_tempo BETWEEN ? AND ?
              AND faop.co_dim_cbo_1 IN (661, 662, 1016, 930, 931)
            GROUP BY e.nu_ine, t.nu_mes
        ", [$startTempo, $endTempo]);

        $teamNum = [];
        $teamDen = [];
        $teamMonthlyNum = [];
        $teamMonthlyDen = [];

        foreach ($rows as $r) {
            $ine = trim((string) $r->nu_ine);
            if ($ine === '' || $ine === '-') {
                continue;
            }
            $month = (int) $r->nu_mes;
            $exos = (int) $r->num_exodontias;
            $procs = (int) $r->den_procedimentos;

            $teamNum[$ine] = ($teamNum[$ine] ?? 0) + $exos;
            $teamDen[$ine] = ($teamDen[$ine] ?? 0) + $procs;
            $teamMonthlyNum[$ine][$month] = ($teamMonthlyNum[$ine][$month] ?? 0) + $exos;
            $teamMonthlyDen[$ine][$month] = ($teamMonthlyDen[$ine][$month] ?? 0) + $procs;
        }

        // Consolida por equipe
        $teamsData = [];
        $mNum = 0;
        $mDen = 0;

        foreach ($teams as $ine => $teamInfo) {
            $num = $teamNum[$ine] ?? 0;
            $den = $teamDen[$ine] ?? 0;
            $res = $this->calculator->calculateB3($num, $den);

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

        $mRes = $this->calculator->calculateB3($mNum, $mDen);
        $municipal = [
            'indicator_code' => 'b3',
            'numerator' => $mNum,
            'denominator' => $mDen,
            'rate' => $mRes['rate'],
            'score_percent' => $mRes['rate'],
            'performance_level' => $mRes['performance_level'],
            'points' => $mRes['points'],
        ];

        return [
            'indicator_code' => 'b3',
            'teams_data' => $teamsData,
            'nominals' => [],
            'municipal' => $municipal,
        ];
    }
}
