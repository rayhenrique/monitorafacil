<?php

namespace App\Services;

use App\Models\C4CohortSnapshot;
use App\Models\C4NominalDiabetic;
use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\FamilyHealthMonthlySnapshot;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class C4SnapshotService
{
    public function __construct(private readonly C4DwService $dw) {}

    /**
     * @param  array<string, array{ine:string,name:string,type:string}>  $teams
     * @return array{diabetics:int,cohort_diabetics:int,teams:int,months:int}
     */
    public function process(ConnectionInterface $pec, int $year, int $quarter, array $teams): array
    {
        // 1. Extração DW PEC
        $extraction = $this->dw->extract($pec, $year, $quarter, $teams);
        $scores = $extraction['scores'] ?? [];
        $nominalDiabetics = $extraction['diabetics'] ?? [];
        $cohortCounts = $extraction['cohort'] ?? [];
        $stats = ['diabetics' => count($nominalDiabetics), 'cohort_diabetics' => count($nominalDiabetics), 'teams' => count($teams), 'months' => 4];

        DB::transaction(function () use ($scores, $nominalDiabetics, $cohortCounts, $extraction, $teams, $year, $quarter): void {
            // Remove snapshots existentes do período
            C4CohortSnapshot::query()->where('year', $year)->where('quarter', $quarter)->delete();
            C4NominalDiabetic::query()->where('year', $year)->where('quarter', $quarter)->delete();
            FamilyHealthMonthlySnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c4')->delete();
            FamilyHealthIndicatorSnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c4')->delete();

            // Grava lista nominal de diabéticos
            if (! empty($nominalDiabetics)) {
                foreach (array_chunk($nominalDiabetics, 100) as $chunk) {
                    $records = array_map(function ($d) use ($year, $quarter) {
                        return array_merge($d, [
                            'year' => $year,
                            'quarter' => $quarter,
                            'calculation_version' => C4DwService::VERSION,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }, $chunk);
                    C4NominalDiabetic::query()->insert($records);
                }
            }

            // Grava Coorte por Equipe e Consolidado Municipal
            $municipalTotalCohort = 0;
            $municipalScoreSum = 0.0;
            $monthsInQuarter = match ($quarter) {
                1 => [1, 2, 3, 4],
                2 => [5, 6, 7, 8],
                default => [9, 10, 11, 12],
            };

            foreach ($scores as $ine => $scoreData) {
                $total = (int) ($scoreData['cohort_total'] ?? 0);
                $municipalTotalCohort += $total;
                $municipalScoreSum += ($scoreData['score_percent'] * $total);

                C4CohortSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => $ine,
                    'team_name' => $scoreData['team_name'],
                    'team_type' => $scoreData['team_type'],
                    'cohort_total' => $total,
                    'evaluated_total' => $total,
                    'monthly_counts' => $scoreData['monthly_counts'] ?? [],
                    'as_of' => $extraction['as_of'],
                    'calculation_version' => C4DwService::VERSION,
                ]);

                // Grava Snapshots Mensais (4 meses)
                foreach ($monthsInQuarter as $m) {
                    FamilyHealthMonthlySnapshot::query()->create([
                        'year' => $year,
                        'quarter' => $quarter,
                        'month' => $m,
                        'month_in_quarter' => (($m - 1) % 4) + 1,
                        'indicator_code' => 'c4',
                        'ine' => $ine,
                        'team_name' => $scoreData['team_name'],
                        'team_type' => $teams[$ine]['type'] ?? '70',
                        'numerator' => (int) round(($scoreData['score_percent'] * $total) / 100),
                        'denominator' => $total,
                        'score_percent' => $scoreData['score_percent'],
                        'performance_level' => $scoreData['performance_level'],
                        'component_iii_points' => $scoreData['component_iii_points'],
                        'is_evaluated' => true,
                        'calculation_version' => C4DwService::VERSION,
                    ]);
                }

                // Grava Snapshot Quadrimestral do Indicador por Equipe
                FamilyHealthIndicatorSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'indicator_code' => 'c4',
                    'ine' => $ine,
                    'team_name' => $scoreData['team_name'],
                    'numerator' => (int) round(($scoreData['score_percent'] * $total) / 100),
                    'denominator' => $total,
                    'score_percent' => $scoreData['score_percent'],
                    'performance_level' => $scoreData['performance_level'],
                    'component_iii_points' => $scoreData['component_iii_points'],
                    'is_evaluated' => true,
                    'as_of' => $extraction['as_of'],
                    'calculation_version' => C4DwService::VERSION,
                ]);
            }

            // Consolidado Municipal
            $municipalAvgScore = $municipalTotalCohort > 0 ? round($municipalScoreSum / $municipalTotalCohort, 2) : 0.0;
            $municipalLevel = FamilyHealthService::calculatePerformanceLevel('c4', $municipalAvgScore);
            $municipalC3Points = FamilyHealthService::calculateComponentIIIPoints($municipalLevel, 1.0);

            $municipalMonthly = [];
            foreach ($monthsInQuarter as $m) {
                $municipalMonthly[$m] = $municipalTotalCohort;
            }

            C4CohortSnapshot::query()->create([
                'year' => $year,
                'quarter' => $quarter,
                'ine' => null,
                'team_name' => 'Consolidado Municipal',
                'team_type' => '70',
                'cohort_total' => $municipalTotalCohort,
                'evaluated_total' => $municipalTotalCohort,
                'monthly_counts' => $municipalMonthly,
                'as_of' => $extraction['as_of'],
                'calculation_version' => C4DwService::VERSION,
            ]);

            // Snapshots Mensais Municipais
            foreach ($monthsInQuarter as $m) {
                FamilyHealthMonthlySnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'month' => $m,
                    'month_in_quarter' => (($m - 1) % 4) + 1,
                    'indicator_code' => 'c4',
                    'ine' => null,
                    'team_name' => 'Consolidado Municipal',
                    'team_type' => '70',
                    'numerator' => (int) round(($municipalAvgScore * $municipalTotalCohort) / 100),
                    'denominator' => $municipalTotalCohort,
                    'score_percent' => $municipalAvgScore,
                    'performance_level' => $municipalLevel,
                    'component_iii_points' => $municipalC3Points,
                    'is_evaluated' => true,
                    'calculation_version' => C4DwService::VERSION,
                ]);
            }

            // Snapshot Quadrimestral Municipal
            FamilyHealthIndicatorSnapshot::query()->create([
                'year' => $year,
                'quarter' => $quarter,
                'indicator_code' => 'c4',
                'ine' => null,
                'team_name' => 'Consolidado Municipal',
                'numerator' => (int) round(($municipalAvgScore * $municipalTotalCohort) / 100),
                'denominator' => $municipalTotalCohort,
                'score_percent' => $municipalAvgScore,
                'performance_level' => $municipalLevel,
                'component_iii_points' => $municipalC3Points,
                'is_evaluated' => true,
                'as_of' => $extraction['as_of'],
                'calculation_version' => C4DwService::VERSION,
            ]);
        });

        return $stats;
    }
}
