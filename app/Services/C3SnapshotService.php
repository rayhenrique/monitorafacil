<?php

namespace App\Services;

use App\Models\C3CohortSnapshot;
use App\Models\C3NominalPregnancy;
use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\FamilyHealthMonthlySnapshot;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class C3SnapshotService
{
    public function __construct(private readonly C3DwService $dw) {}

    /**
     * @param  array<string, array{ine:string,name:string,type:string}>  $teams
     * @return array{pregnancies:int,cohort_pregnancies:int,completed_pregnancies:int,teams:int,months:int}
     */
    public function process(ConnectionInterface $pec, int $year, int $quarter, array $teams): array
    {
        // 1. Extração DW PEC
        $extraction = $this->dw->extract($pec, $year, $quarter, $teams);
        $data = $extraction['scores'];
        $cohort = $extraction['cohort'];
        $stats = ['pregnancies' => 0, 'cohort_pregnancies' => 0, 'completed_pregnancies' => 0, 'teams' => 0, 'months' => 0];

        DB::transaction(function () use ($data, $cohort, $extraction, $teams, $year, $quarter, &$stats): void {
            // Remove snapshots existentes do período
            C3CohortSnapshot::query()->where('year', $year)->where('quarter', $quarter)->delete();
            C3NominalPregnancy::query()->where('year', $year)->where('quarter', $quarter)->delete();
            FamilyHealthMonthlySnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c3')->delete();
            FamilyHealthIndicatorSnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c3')->delete();

            // Grava lista nominal de gestantes e puérperas
            $nominalPregnancies = $extraction['pregnancies'] ?? [];
            if (! empty($nominalPregnancies)) {
                foreach (array_chunk($nominalPregnancies, 100) as $chunk) {
                    $records = array_map(function ($p) use ($year, $quarter) {
                        return array_merge($p, [
                            'year' => $year,
                            'quarter' => $quarter,
                            'calculation_version' => C3DwService::VERSION,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }, $chunk);
                    C3NominalPregnancy::query()->insert($records);
                }
            }

            // Grava Coorte por Equipe e Consolidado Municipal
            $municipalCohort = [];
            $municipalEvaluated = 0;
            foreach ($cohort as $ine => $monthlyCounts) {
                $total = array_sum($monthlyCounts);
                $evaluated = array_sum($extraction['completed'][$ine] ?? []);
                $stats['cohort_pregnancies'] += $total;
                $stats['completed_pregnancies'] += $evaluated;
                $municipalEvaluated += $evaluated;
                foreach ($monthlyCounts as $month => $count) {
                    $municipalCohort[$month] = ($municipalCohort[$month] ?? 0) + $count;
                }
                C3CohortSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => $ine,
                    'team_name' => $teams[$ine]['name'],
                    'team_type' => $teams[$ine]['type'],
                    'cohort_total' => $total,
                    'evaluated_total' => $evaluated,
                    'monthly_counts' => $monthlyCounts,
                    'as_of' => $extraction['as_of'],
                    'calculation_version' => C3DwService::VERSION,
                ]);
            }

            if ($municipalCohort !== []) {
                C3CohortSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => null,
                    'team_name' => 'Consolidado Municipal',
                    'team_type' => '70',
                    'cohort_total' => $stats['cohort_pregnancies'],
                    'evaluated_total' => $municipalEvaluated,
                    'monthly_counts' => $municipalCohort,
                    'as_of' => $extraction['as_of'],
                    'calculation_version' => C3DwService::VERSION,
                ]);
            }

            // Grava Snapshots Mensais e Indicador por Equipe
            $municipal = [];
            $municipalIncomplete = 0;
            $municipalPractices = [
                'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0,
                'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0,
            ];

            foreach ($data as $ine => $months) {
                $scores = [];
                $teamPoints = 0;
                $teamPregnancies = 0;
                $teamIncomplete = 0;
                $practiceTotals = [
                    'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0,
                    'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0,
                ];

                foreach ($months as $month => $item) {
                    $score = $item['score_percent'];
                    $scores[] = $score;
                    $teamPoints += $item['numerator'];
                    $teamPregnancies += $item['denominator'];
                    $teamIncomplete += $item['incomplete'];
                    $municipalIncomplete += $item['incomplete'];
                    $stats['months']++;
                    $stats['pregnancies'] += $item['denominator'];

                    foreach ($practiceTotals as $key => $_) {
                        $practiceTotals[$key] += ($item['practices'][$key] ?? 0);
                        $municipalPractices[$key] += ($item['practices'][$key] ?? 0);
                    }

                    FamilyHealthMonthlySnapshot::query()->create([
                        'year' => $year,
                        'month' => $month,
                        'quarter' => $quarter,
                        'month_in_quarter' => (($month - 1) % 4) + 1,
                        'ine' => $ine,
                        'team_name' => $teams[$ine]['name'],
                        'team_type' => $teams[$ine]['type'],
                        'indicator_code' => 'c3',
                        'numerator' => $item['numerator'],
                        'denominator' => $item['denominator'],
                        'score_percent' => $score,
                        'performance_level' => FamilyHealthService::calculatePerformanceLevel('c3', $score),
                    ]);

                    $municipal[$month] ??= ['points' => 0, 'pregnancies' => 0];
                    $municipal[$month]['points'] += $item['numerator'];
                    $municipal[$month]['pregnancies'] += $item['denominator'];
                }

                if ($scores === []) {
                    continue;
                }

                $stats['teams']++;
                $average = round(array_sum($scores) / count($scores), 2);
                $level = FamilyHealthService::calculatePerformanceLevel('c3', $average);
                FamilyHealthIndicatorSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => $ine,
                    'team_name' => $teams[$ine]['name'],
                    'team_type' => $teams[$ine]['type'],
                    'indicator_code' => 'c3',
                    'numerator' => $teamPoints,
                    'denominator' => $teamPregnancies,
                    'score_percent' => $average,
                    'performance_level' => $level,
                    'good_practices_breakdown' => [
                        'calculation_version' => C3DwService::VERSION,
                        'source' => 'PEC DW (prévia local; sem RNDS)',
                        'valid_months' => count($scores),
                        'practices' => $practiceTotals,
                        'component_iii_points' => FamilyHealthService::calculateComponentIIIPoints($level, 2.0),
                    ],
                    'active_search_count' => $teamIncomplete,
                ]);
            }

            // Grava Snapshots Mensais e Indicador Consolidado Municipal
            $municipalScores = [];
            $municipalPoints = 0;
            $municipalPregnancies = 0;
            foreach ($municipal as $month => $item) {
                $score = $item['pregnancies'] > 0 ? round($item['points'] / $item['pregnancies'], 2) : 0.0;
                $municipalScores[] = $score;
                $municipalPoints += $item['points'];
                $municipalPregnancies += $item['pregnancies'];
                FamilyHealthMonthlySnapshot::query()->create([
                    'year' => $year,
                    'month' => $month,
                    'quarter' => $quarter,
                    'month_in_quarter' => (($month - 1) % 4) + 1,
                    'ine' => null,
                    'team_name' => 'Consolidado Municipal',
                    'team_type' => '70',
                    'indicator_code' => 'c3',
                    'numerator' => $item['points'],
                    'denominator' => $item['pregnancies'],
                    'score_percent' => $score,
                    'performance_level' => FamilyHealthService::calculatePerformanceLevel('c3', $score),
                ]);
            }

            if ($municipalScores !== []) {
                $average = round(array_sum($municipalScores) / count($municipalScores), 2);
                $level = FamilyHealthService::calculatePerformanceLevel('c3', $average);
                FamilyHealthIndicatorSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => null,
                    'team_name' => 'Consolidado Municipal',
                    'team_type' => '70',
                    'indicator_code' => 'c3',
                    'numerator' => $municipalPoints,
                    'denominator' => $municipalPregnancies,
                    'score_percent' => $average,
                    'performance_level' => $level,
                    'good_practices_breakdown' => [
                        'calculation_version' => C3DwService::VERSION,
                        'source' => 'PEC DW (prévia local; sem RNDS)',
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
