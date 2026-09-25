<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;

/**
 * Extração DW do Indicador B5 - Procedimentos Odontológicos Preventivos Individuais.
 *
 * Conforme Nota Metodológica B5 (MS/SAPS/DESCO):
 * - Numerador: Procedimentos preventivos individuais realizados pela eSB (CD + TSB).
 * - Denominador: Total de procedimentos odontológicos individuais realizados pela eSB.
 * - Parâmetros: Ótimo (≥ 65 e ≤ 85), Bom (≥ 55 e < 65), Suficiente (≥ 40 e < 55), Regular (< 40 ou > 85).
 */
class B5DwService
{
    public const VERSION = 'dw-b5-2026-09-normative-v1.0';

    private const PREVENTIVE_PROCEDS = [2172, 2023, 1995, 2049, 2561, 2523, 1603];
    private const VALID_CBOS = [661, 662, 1016, 930, 931];

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

        $rows = $pec->select("
            SELECT
                e.nu_ine,
                t.nu_mes,
                SUM(CASE WHEN faop.co_dim_procedimento IN (2172, 2023, 1995, 2049, 2561, 2523, 1603)
                         THEN faop.qt_procedimentos ELSE 0 END) AS num_preventivos,
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
            $prevs = (int) $r->num_preventivos;
            $procs = (int) $r->den_procedimentos;

            $teamNum[$ine] = ($teamNum[$ine] ?? 0) + $prevs;
            $teamDen[$ine] = ($teamDen[$ine] ?? 0) + $procs;
            $teamMonthlyNum[$ine][$month] = ($teamMonthlyNum[$ine][$month] ?? 0) + $prevs;
            $teamMonthlyDen[$ine][$month] = ($teamMonthlyDen[$ine][$month] ?? 0) + $procs;
        }

        // Consolida por equipe
        $teamsData = [];
        $mNum = 0;
        $mDen = 0;

        foreach ($teams as $ine => $teamInfo) {
            $num = $teamNum[$ine] ?? 0;
            $den = $teamDen[$ine] ?? 0;
            $res = $this->calculator->calculateB5($num, $den);

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

        $mRes = $this->calculator->calculateB5($mNum, $mDen);
        $municipal = [
            'indicator_code' => 'b5',
            'numerator' => $mNum,
            'denominator' => $mDen,
            'rate' => $mRes['rate'],
            'score_percent' => $mRes['rate'],
            'performance_level' => $mRes['performance_level'],
            'points' => $mRes['points'],
        ];

        return [
            'indicator_code' => 'b5',
            'teams_data' => $teamsData,
            'nominals' => [],
            'municipal' => $municipal,
        ];
    }
}
