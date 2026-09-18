<?php

namespace App\Services;

use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\FamilyHealthMonthlySnapshot;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class C2SnapshotService
{
    public function __construct(private readonly C2DwService $dw) {}

    /**
     * @param  array<string, array{ine:string,name:string,type:string}>  $teams
     * @return array{children:int,teams:int,months:int}
     */
    public function process(ConnectionInterface $pec, int $year, int $quarter, array $teams): array
    {
        // Todas as consultas terminam antes de alterar os snapshots locais.
        $data = $this->dw->extract($pec, $year, $quarter, $teams);
        $stats = ['children' => 0, 'teams' => 0, 'months' => 0];

        DB::transaction(function () use ($data, $teams, $year, $quarter, &$stats): void {
            FamilyHealthMonthlySnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c2')->delete();
            FamilyHealthIndicatorSnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c2')->delete();

            $municipal = [];
            $municipalIncomplete = 0;
            $municipalPractices = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
            foreach ($data as $ine => $months) {
                $scores = [];
                $teamPoints = 0;
                $teamChildren = 0;
                $teamIncomplete = 0;
                $practiceTotals = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];

                foreach ($months as $month => $item) {
                    $score = $item['score_percent'];
                    $scores[] = $score;
                    $teamPoints += $item['numerator'];
                    $teamChildren += $item['denominator'];
                    $teamIncomplete += $item['incomplete'];
                    $municipalIncomplete += $item['incomplete'];
                    $stats['months']++;
                    $stats['children'] += $item['denominator'];

                    foreach ($practiceTotals as $key => $_) {
                        $practiceTotals[$key] += $item['practices'][$key];
                        $municipalPractices[$key] += $item['practices'][$key];
                    }

                    FamilyHealthMonthlySnapshot::query()->create([
                        'year' => $year,
                        'month' => $month,
                        'quarter' => $quarter,
                        'month_in_quarter' => (($month - 1) % 4) + 1,
                        'ine' => $ine,
                        'team_name' => $teams[$ine]['name'],
                        'team_type' => $teams[$ine]['type'],
                        'indicator_code' => 'c2',
                        'numerator' => $item['numerator'],
                        'denominator' => $item['denominator'],
                        'score_percent' => $score,
                        'performance_level' => FamilyHealthService::calculatePerformanceLevel('c2', $score),
                    ]);

                    $municipal[$month] ??= ['points' => 0, 'children' => 0];
                    $municipal[$month]['points'] += $item['numerator'];
                    $municipal[$month]['children'] += $item['denominator'];
                }

                if ($scores === []) {
                    continue;
                }

                $stats['teams']++;
                $average = round(array_sum($scores) / count($scores), 2);
                $level = FamilyHealthService::calculatePerformanceLevel('c2', $average);
                FamilyHealthIndicatorSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => $ine,
                    'team_name' => $teams[$ine]['name'],
                    'team_type' => $teams[$ine]['type'],
                    'indicator_code' => 'c2',
                    'numerator' => $teamPoints,
                    'denominator' => $teamChildren,
                    'score_percent' => $average,
                    'performance_level' => $level,
                    'good_practices_breakdown' => [
                        'calculation_version' => C2DwService::VERSION,
                        'source' => 'PEC DW (estimativa local; sem RNDS)',
                        'valid_months' => count($scores),
                        'practices' => $practiceTotals,
                        'component_iii_points' => FamilyHealthService::calculateComponentIIIPoints($level, 2.0),
                    ],
                    'active_search_count' => $teamIncomplete,
                ]);
            }

            $municipalScores = [];
            $municipalPoints = 0;
            $municipalChildren = 0;
            foreach ($municipal as $month => $item) {
                $score = round($item['points'] / $item['children'], 2);
                $municipalScores[] = $score;
                $municipalPoints += $item['points'];
                $municipalChildren += $item['children'];
                FamilyHealthMonthlySnapshot::query()->create([
                    'year' => $year,
                    'month' => $month,
                    'quarter' => $quarter,
                    'month_in_quarter' => (($month - 1) % 4) + 1,
                    'ine' => null,
                    'team_name' => 'Consolidado Municipal',
                    'team_type' => '70',
                    'indicator_code' => 'c2',
                    'numerator' => $item['points'],
                    'denominator' => $item['children'],
                    'score_percent' => $score,
                    'performance_level' => FamilyHealthService::calculatePerformanceLevel('c2', $score),
                ]);
            }

            if ($municipalScores !== []) {
                $average = round(array_sum($municipalScores) / count($municipalScores), 2);
                $level = FamilyHealthService::calculatePerformanceLevel('c2', $average);
                FamilyHealthIndicatorSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => null,
                    'team_name' => 'Consolidado Municipal',
                    'team_type' => '70',
                    'indicator_code' => 'c2',
                    'numerator' => $municipalPoints,
                    'denominator' => $municipalChildren,
                    'score_percent' => $average,
                    'performance_level' => $level,
                    'good_practices_breakdown' => [
                        'calculation_version' => C2DwService::VERSION,
                        'source' => 'PEC DW (estimativa local; sem RNDS)',
                        'valid_months' => count($municipalScores),
                        'teams_count' => $stats['teams'],
                        'practices' => $municipalPractices,
                        'component_iii_points' => FamilyHealthService::calculateComponentIIIPoints($level, 2.0),
                    ],
                    'active_search_count' => $municipalIncomplete,
                ]);
            }
        });

        return $stats;
    }
}
