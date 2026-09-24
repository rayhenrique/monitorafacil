<?php

namespace App\Services;

use App\Models\C6CohortSnapshot;
use App\Models\C6NominalElderly;
use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\FamilyHealthMonthlySnapshot;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class C6SnapshotService
{
    public function __construct(private readonly C6DwService $dw) {}

    /**
     * @param  array<string, array{ine:string,name:string,type:string}>  $teams
     * @return array{elders:int,cohort_elders:int,teams:int,months:int}
     */
    public function process(ConnectionInterface $pec, int $year, int $quarter, array $teams): array
    {
        // 1. Extração DW PEC
        $extraction = $this->dw->extract($pec, $year, $quarter, $teams);
        $scores = $extraction['scores'] ?? [];
        $nominalElders = $extraction['elders'] ?? [];
        $cohortCounts = $extraction['cohort'] ?? [];
        $stats = [
            'elderly' => count($nominalElders),
            'elders' => count($nominalElders),
            'cohort_elders' => count($nominalElders),
            'teams' => count($teams),
            'months' => 4,
        ];

        DB::transaction(function () use ($scores, $nominalElders, $cohortCounts, $extraction, $teams, $year, $quarter): void {
            // Remove snapshots existentes do período
            C6CohortSnapshot::query()->where('year', $year)->where('quarter', $quarter)->delete();
            C6NominalElderly::query()->where('year', $year)->where('quarter', $quarter)->delete();
            FamilyHealthMonthlySnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c6')->delete();
            FamilyHealthIndicatorSnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c6')->delete();

            // Grava lista nominal de idosos
            if (! empty($nominalElders)) {
                foreach (array_chunk($nominalElders, 100) as $chunk) {
                    $records = array_map(function ($d) use ($year, $quarter) {
                        return array_merge($d, [
                            'year' => $year,
                            'quarter' => $quarter,
                            'calculation_version' => C6DwService::VERSION,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }, $chunk);
                    C6NominalElderly::query()->insert($records);
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

                C6CohortSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => $ine,
                    'team_name' => $scoreData['team_name'],
                    'team_type' => $scoreData['team_type'],
                    'cohort_total' => $total,
                    'evaluated_total' => $total,
                    'monthly_counts' => $scoreData['monthly_counts'] ?? [],
                    'as_of' => $extraction['as_of'],
                    'calculation_version' => C6DwService::VERSION,
                ]);

                // Grava Snapshots Mensais (4 meses)
                foreach ($monthsInQuarter as $m) {
                    FamilyHealthMonthlySnapshot::query()->create([
                        'year' => $year,
                        'quarter' => $quarter,
                        'month' => $m,
                        'month_in_quarter' => (($m - 1) % 4) + 1,
                        'indicator_code' => 'c6',
                        'ine' => $ine,
                        'team_name' => $scoreData['team_name'],
                        'team_type' => $teams[$ine]['type'] ?? '70',
                        'numerator' => (int) round(($scoreData['score_percent'] * $total) / 100),
                        'denominator' => $total,
                        'score_percent' => $scoreData['score_percent'],
                        'performance_level' => $scoreData['performance_level'],
                        'calculation_version' => C6DwService::VERSION,
                    ]);
                }
            }

            // Grava o Consolidado Municipal no Quadrimestre
            $municipalAvg = $municipalTotalCohort > 0 ? round($municipalScoreSum / $municipalTotalCohort, 2) : 0.0;
            $municipalLevel = FamilyHealthService::calculatePerformanceLevel('c6', $municipalAvg);

            FamilyHealthIndicatorSnapshot::query()->create([
                'year' => $year,
                'quarter' => $quarter,
                'indicator_code' => 'c6',
                'ine' => null,
                'team_name' => null,
                'numerator' => (int) round(($municipalAvg * $municipalTotalCohort) / 100),
                'denominator' => $municipalTotalCohort,
                'score_percent' => $municipalAvg,
                'performance_level' => $municipalLevel,
                'calculation_version' => C6DwService::VERSION,
            ]);
        });

        return $stats;
    }
}
