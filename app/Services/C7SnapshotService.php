<?php

namespace App\Services;

use App\Models\C7CohortSnapshot;
use App\Models\C7NominalWoman;
use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\FamilyHealthMonthlySnapshot;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class C7SnapshotService
{
    public function __construct(private readonly C7DwService $dw) {}

    /**
     * @param  array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>  $teams
     * @return array{women:int,cohort_women:int,teams:int,months:int}
     */
    public function process(ConnectionInterface $pec, int $year, int $quarter, array $teams): array
    {
        // 1. Extração no DW do PEC
        $extraction = $this->dw->extract($pec, $year, $quarter, $teams);
        $nominalWomen = $extraction['nominal'] ?? [];
        $teamScores = $extraction['teams'] ?? [];
        $asOf = $extraction['as_of'];
        $municipalScore = (float) ($extraction['municipal_score'] ?? 0.0);

        $stats = [
            'women' => count($nominalWomen),
            'cohort_women' => count($nominalWomen),
            'teams' => count($teams),
            'months' => 4,
        ];

        DB::transaction(function () use ($nominalWomen, $teamScores, $extraction, $teams, $year, $quarter, $asOf, $municipalScore): void {
            // Remove snapshots anteriores do período
            C7CohortSnapshot::query()->where('year', $year)->where('quarter', $quarter)->delete();
            C7NominalWoman::query()->where('year', $year)->where('quarter', $quarter)->delete();
            FamilyHealthMonthlySnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c7')->delete();
            FamilyHealthIndicatorSnapshot::query()
                ->where('year', $year)->where('quarter', $quarter)->where('indicator_code', 'c7')->delete();

            // Grava lista nominal de mulheres
            if (! empty($nominalWomen)) {
                foreach (array_chunk($nominalWomen, 100) as $chunk) {
                    $records = array_map(function ($d) use ($year, $quarter) {
                        return [
                            'year' => $year,
                            'quarter' => $quarter,
                            'cidadao_pec_id' => $d['id'],
                            'cns' => $d['cns'],
                            'cpf' => $d['cpf'],
                            'name' => $d['name'],
                            'social_name' => $d['social_name'],
                            'birth_date' => $d['born']->toDateString(),
                            'age_years' => $d['age_years'],
                            'sex' => $d['sex'],
                            'gender_identity' => $d['gender_identity'],
                            'phone' => $d['phone'],
                            'race_color' => $d['race_color'],
                            'cnes' => $d['cnes'],
                            'facility_name' => $d['facility_name'],
                            'district' => $d['district'],
                            'ine' => $d['ine'],
                            'team_name' => $teams[$d['ine']]['name'] ?? $d['ine'],
                            'team_type' => $d['team_type'],
                            'professional_cns' => null,
                            'professional_name' => null,
                            'microarea' => $d['microarea'],
                            'month_ref' => null,
                            'mici_updated' => false,
                            'is_accompanied' => false,

                            // Prática A: Colo do Útero
                            'eligible_practice_a' => $d['eligible_practice_a'],
                            'practice_a_met' => $d['practice_a_met'],
                            'practice_a_count' => $d['practice_a_count'],
                            'last_cervical_exam_date' => $d['last_cervical_exam_date']?->toDateString(),
                            'last_cervical_exam_code' => $d['last_cervical_exam_code'],
                            'last_cervical_exam_desc' => $d['last_cervical_exam_desc'],

                            // Prática B: Vacina HPV
                            'eligible_practice_b' => $d['eligible_practice_b'],
                            'practice_b_met' => $d['practice_b_met'],
                            'practice_b_count' => $d['practice_b_count'],
                            'last_hpv_vaccine_date' => $d['last_hpv_vaccine_date']?->toDateString(),
                            'last_hpv_vaccine_code' => $d['last_hpv_vaccine_code'],
                            'last_hpv_vaccine_name' => $d['last_hpv_vaccine_name'],

                            // Prática C: Saúde Sexual e Reprodutiva
                            'eligible_practice_c' => $d['eligible_practice_c'],
                            'practice_c_met' => $d['practice_c_met'],
                            'practice_c_count' => $d['practice_c_count'],
                            'last_sexual_health_date' => $d['last_sexual_health_date']?->toDateString(),
                            'last_sexual_health_code' => $d['last_sexual_health_code'],
                            'last_sexual_health_detail' => $d['last_sexual_health_detail'],

                            // Prática D: Câncer de Mama
                            'eligible_practice_d' => $d['eligible_practice_d'],
                            'practice_d_met' => $d['practice_d_met'],
                            'practice_d_count' => $d['practice_d_count'],
                            'last_mammogram_date' => $d['last_mammogram_date']?->toDateString(),
                            'last_mammogram_code' => $d['last_mammogram_code'],
                            'last_mammogram_desc' => $d['last_mammogram_desc'],

                            'score_percent' => $d['score_percent'],
                            'pending_practices' => json_encode($d['pending_practices'], JSON_UNESCAPED_UNICODE),
                            'calculation_version' => C7DwService::VERSION,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }, $chunk);
                    C7NominalWoman::query()->insert($records);
                }
            }

            // Grava Coorte por Equipe e Consolidado Municipal
            $monthsInQuarter = match ($quarter) {
                1 => [1, 2, 3, 4],
                2 => [5, 6, 7, 8],
                default => [9, 10, 11, 12],
            };

            $municipalTotalCohort = 0;
            $municipalEvaluatedSum = 0;

            foreach ($teamScores as $ine => $scoreData) {
                $total = (int) ($scoreData['total_cohort'] ?? 0);
                $evalTotal = (int) ($scoreData['evaluated_total'] ?? 0);
                $municipalTotalCohort += $total;
                $municipalEvaluatedSum += $evalTotal;

                $finalScore = (float) ($scoreData['final_score'] ?? 0.0);
                $level = FamilyHealthService::calculatePerformanceLevel('c7', $finalScore);
                // Nota Técnica 06/2025: Peso 2.0x no Componente III
                $compPoints = FamilyHealthService::calculateComponentIIIPoints($level, 2.0);

                $monthlyCounts = [];
                foreach ($monthsInQuarter as $m) {
                    $monthlyCounts[$m] = $total;
                }

                C7CohortSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => $ine,
                    'team_name' => $scoreData['team_name'],
                    'team_type' => $scoreData['team_type'],
                    'cohort_total' => $total,
                    'evaluated_total' => $evalTotal,
                    'practice_a_eligible' => $scoreData['practice_a_eligible'],
                    'practice_a_compliant' => $scoreData['practice_a_compliant'],
                    'practice_a_score' => $scoreData['practice_a_score'],
                    'practice_b_eligible' => $scoreData['practice_b_eligible'],
                    'practice_b_compliant' => $scoreData['practice_b_compliant'],
                    'practice_b_score' => $scoreData['practice_b_score'],
                    'practice_c_eligible' => $scoreData['practice_c_eligible'],
                    'practice_c_compliant' => $scoreData['practice_c_compliant'],
                    'practice_c_score' => $scoreData['practice_c_score'],
                    'practice_d_eligible' => $scoreData['practice_d_eligible'],
                    'practice_d_compliant' => $scoreData['practice_d_compliant'],
                    'practice_d_score' => $scoreData['practice_d_score'],
                    'final_score' => $finalScore,
                    'monthly_counts' => $monthlyCounts,
                    'as_of' => $asOf,
                    'calculation_version' => C7DwService::VERSION,
                ]);

                // Snapshots Mensais por Equipe (4 meses)
                foreach ($monthsInQuarter as $m) {
                    FamilyHealthMonthlySnapshot::query()->create([
                        'year' => $year,
                        'quarter' => $quarter,
                        'month' => $m,
                        'month_in_quarter' => (($m - 1) % 4) + 1,
                        'indicator_code' => 'c7',
                        'ine' => $ine,
                        'team_name' => $scoreData['team_name'],
                        'team_type' => $teams[$ine]['type'] ?? '70',
                        'numerator' => (int) round(($finalScore * $total) / 100),
                        'denominator' => $total,
                        'score_percent' => $finalScore,
                        'performance_level' => $level,
                        'component_iii_points' => $compPoints,
                        'is_evaluated' => true,
                        'calculation_version' => C7DwService::VERSION,
                    ]);
                }

                // Snapshot Quadrimestral do Indicador por Equipe
                FamilyHealthIndicatorSnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'indicator_code' => 'c7',
                    'ine' => $ine,
                    'team_name' => $scoreData['team_name'],
                    'team_type' => $teams[$ine]['type'] ?? '70',
                    'numerator' => (int) round(($finalScore * $total) / 100),
                    'denominator' => $total,
                    'score_percent' => $finalScore,
                    'performance_level' => $level,
                    'component_iii_points' => $compPoints,
                    'good_practices_breakdown' => [
                        'calculation_version' => C7DwService::VERSION,
                        'as_of' => $asOf,
                        'practice_a_score' => $scoreData['practice_a_score'],
                        'practice_b_score' => $scoreData['practice_b_score'],
                        'practice_c_score' => $scoreData['practice_c_score'],
                        'practice_d_score' => $scoreData['practice_d_score'],
                        'practice_a_eligible' => $scoreData['practice_a_eligible'],
                        'practice_a_compliant' => $scoreData['practice_a_compliant'],
                        'practice_b_eligible' => $scoreData['practice_b_eligible'],
                        'practice_b_compliant' => $scoreData['practice_b_compliant'],
                        'practice_c_eligible' => $scoreData['practice_c_eligible'],
                        'practice_c_compliant' => $scoreData['practice_c_compliant'],
                        'practice_d_eligible' => $scoreData['practice_d_eligible'],
                        'practice_d_compliant' => $scoreData['practice_d_compliant'],
                    ],
                    'is_evaluated' => true,
                    'as_of' => $asOf,
                    'calculation_version' => C7DwService::VERSION,
                ]);
            }

            // Consolidado Municipal
            $municipalLevel = FamilyHealthService::calculatePerformanceLevel('c7', $municipalScore);
            $municipalC3Points = FamilyHealthService::calculateComponentIIIPoints($municipalLevel, 2.0);

            $cityPractices = $extraction['practices_citywide'] ?? [];

            $municipalMonthly = [];
            foreach ($monthsInQuarter as $m) {
                $municipalMonthly[$m] = $municipalTotalCohort;
            }

            C7CohortSnapshot::query()->create([
                'year' => $year,
                'quarter' => $quarter,
                'ine' => null,
                'team_name' => 'Consolidado Municipal',
                'team_type' => '70',
                'cohort_total' => $municipalTotalCohort,
                'evaluated_total' => $municipalEvaluatedSum,
                'practice_a_eligible' => $cityPractices['A_eligible'] ?? 0,
                'practice_a_compliant' => $cityPractices['A_compliant'] ?? 0,
                'practice_a_score' => $cityPractices['A_score'] ?? 0.0,
                'practice_b_eligible' => $cityPractices['B_eligible'] ?? 0,
                'practice_b_compliant' => $cityPractices['B_compliant'] ?? 0,
                'practice_b_score' => $cityPractices['B_score'] ?? 0.0,
                'practice_c_eligible' => $cityPractices['C_eligible'] ?? 0,
                'practice_c_compliant' => $cityPractices['C_compliant'] ?? 0,
                'practice_c_score' => $cityPractices['C_score'] ?? 0.0,
                'practice_d_eligible' => $cityPractices['D_eligible'] ?? 0,
                'practice_d_compliant' => $cityPractices['D_compliant'] ?? 0,
                'practice_d_score' => $cityPractices['D_score'] ?? 0.0,
                'final_score' => $municipalScore,
                'monthly_counts' => $municipalMonthly,
                'as_of' => $asOf,
                'calculation_version' => C7DwService::VERSION,
            ]);

            // Snapshots Mensais Municipais
            foreach ($monthsInQuarter as $m) {
                FamilyHealthMonthlySnapshot::query()->create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'month' => $m,
                    'month_in_quarter' => (($m - 1) % 4) + 1,
                    'indicator_code' => 'c7',
                    'ine' => null,
                    'team_name' => 'Consolidado Municipal',
                    'team_type' => '70',
                    'numerator' => (int) round(($municipalScore * $municipalTotalCohort) / 100),
                    'denominator' => $municipalTotalCohort,
                    'score_percent' => $municipalScore,
                    'performance_level' => $municipalLevel,
                    'component_iii_points' => $municipalC3Points,
                    'is_evaluated' => true,
                    'calculation_version' => C7DwService::VERSION,
                ]);
            }

            // Snapshot Quadrimestral Municipal
            FamilyHealthIndicatorSnapshot::query()->create([
                'year' => $year,
                'quarter' => $quarter,
                'indicator_code' => 'c7',
                'ine' => null,
                'team_name' => 'Consolidado Municipal',
                'team_type' => '70',
                'numerator' => (int) round(($municipalScore * $municipalTotalCohort) / 100),
                'denominator' => $municipalTotalCohort,
                'score_percent' => $municipalScore,
                'performance_level' => $municipalLevel,
                'component_iii_points' => $municipalC3Points,
                'good_practices_breakdown' => [
                    'calculation_version' => C7DwService::VERSION,
                    'as_of' => $asOf,
                    'practices_citywide' => $cityPractices,
                ],
                'is_evaluated' => true,
                'as_of' => $asOf,
                'calculation_version' => C7DwService::VERSION,
            ]);
        });

        return $stats;
    }
}
