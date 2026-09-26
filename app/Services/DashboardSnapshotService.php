<?php

namespace App\Services;

use App\Enums\TeamType;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\OralHealth\OralHealthIndicatorSnapshot;
use Illuminate\Support\Facades\Schema;

class DashboardSnapshotService
{
    /**
     * @return array<string, array{optimal:int,good:int,sufficient:int,regular:int,evaluated_teams:int,has_data:bool}>
     */
    public function familyHealthPerformance(int $year, int $quarter, ?string $cnes = null): array
    {
        $versions = [
            'c1' => C1DwService::VERSION,
            'c2' => C2DwService::VERSION,
            'c3' => C3DwService::VERSION,
            'c4' => C4DwService::VERSION,
            'c5' => C5DwService::VERSION,
            'c6' => C6DwService::VERSION,
            'c7' => C7DwService::VERSION,
        ];
        $codes = ['c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7'];
        $distribution = [];

        foreach ($codes as $code) {
            $distribution[$code] = [
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ];
        }

        $snapshotsQuery = FamilyHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereIn('indicator_code', $codes)
            ->whereNotNull('ine')
            ->where(function ($query): void {
                $query->whereIn('team_type', ['70', '76'])
                    ->orWhereNull('team_type');
            });

        if (filled($cnes) && Schema::hasTable('cvat_team_evaluations')) {
            $teamInes = \App\Models\CvatTeamEvaluation::where('cnes', $cnes)->whereNotNull('ine')->pluck('ine')->all();
            $snapshotsQuery->whereIn('ine', $teamInes);
        }

        $snapshots = $snapshotsQuery->get(['indicator_code', 'performance_level', 'good_practices_breakdown']);

        foreach ($snapshots as $snapshot) {
            $code = strtolower((string) $snapshot->indicator_code);

            if (! isset($distribution[$code]) || empty($snapshot->performance_level)) {
                continue;
            }

            if (isset($versions[$code])) {
                $version = $snapshot->good_practices_breakdown['calculation_version'] ?? null;
                if ($version !== $versions[$code]) {
                    continue;
                }
            }

            $classification = match (strtolower((string) $snapshot->performance_level)) {
                'otimo' => 'optimal',
                'bom' => 'good',
                'suficiente' => 'sufficient',
                default => 'regular',
            };

            $distribution[$code][$classification]++;
            $distribution[$code]['evaluated_teams']++;
            $distribution[$code]['has_data'] = true;
        }

        return $distribution;
    }

    /**
     * @return array<string, array{optimal:int,good:int,sufficient:int,regular:int,evaluated_teams:int,has_data:bool}>
     */
    public function oralHealthPerformance(int $year, int $quarter, ?string $cnes = null): array
    {
        $codes = ['b1', 'b2', 'b3', 'b4', 'b5', 'b6'];
        $distribution = [];

        foreach ($codes as $code) {
            $distribution[$code] = [
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ];
        }

        if (! Schema::hasTable('oral_health_indicator_snapshots')) {
            return $distribution;
        }

        $snapshotsQuery = OralHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereIn('indicator_code', $codes)
            ->whereNotNull('ine');

        if (filled($cnes)) {
            $snapshotsQuery->where('cnes', $cnes);
        }

        $snapshots = $snapshotsQuery->get(['indicator_code', 'performance_level']);

        foreach ($snapshots as $snapshot) {
            $code = strtolower((string) $snapshot->indicator_code);

            if (! isset($distribution[$code]) || empty($snapshot->performance_level)) {
                continue;
            }

            $classification = match (strtolower((string) $snapshot->performance_level)) {
                'otimo', 'optimal' => 'optimal',
                'bom', 'good' => 'good',
                'suficiente', 'sufficient' => 'sufficient',
                default => 'regular',
            };

            $distribution[$code][$classification]++;
            $distribution[$code]['evaluated_teams']++;
            $distribution[$code]['has_data'] = true;
        }

        return $distribution;
    }

    public function registrations(int $year, int $quarter): ?ConsolidationRegistration
    {
        return ConsolidationRegistration::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->first();
    }

    /** @return array{esf: int|null, esaude_bucal: int|null, emulti: int|null} */
    public function teamTotals(int $year, int $quarter): array
    {
        $totals = [
            TeamType::Esf->value => null,
            TeamType::SaudeBucal->value => null,
            TeamType::Emulti->value => null,
        ];

        foreach (ConsolidationTeam::query()->where('year', $year)->where('quarter', $quarter)->get() as $team) {
            $totals[$team->type->value] = $team->total_active;
        }

        return $totals;
    }

    /** @return array<int, array{year: int, quarter: int}> */
    public function periods(): array
    {
        $teams = ConsolidationTeam::query()->select('year', 'quarter')->distinct()->get();
        $registrations = ConsolidationRegistration::query()->select('year', 'quarter')->distinct()->get();
        $snapshots = FamilyHealthIndicatorSnapshot::query()->select('year', 'quarter')->distinct()->get();
        $oralSnapshots = Schema::hasTable('oral_health_indicator_snapshots')
            ? OralHealthIndicatorSnapshot::query()->select('year', 'quarter')->distinct()->get()
            : collect();
        $evaluations = Schema::hasTable('cvat_team_evaluations')
            ? \App\Models\CvatTeamEvaluation::query()->select('year', 'quarter')->distinct()->get()
            : collect();
        $distributions = Schema::hasTable('cvat_dimension_distributions')
            ? \App\Models\CvatDimensionDistribution::query()->select('year', 'quarter')->distinct()->get()
            : collect();

        return $teams->concat($registrations)
            ->concat($evaluations)
            ->concat($distributions)
            ->concat($snapshots)
            ->concat($oralSnapshots)
            ->map(static fn ($row): array => [
                'year' => (int) $row->year,
                'quarter' => (int) $row->quarter,
            ])
            ->filter(static fn (array $period): bool => $period['year'] >= 2020 && $period['quarter'] >= 1 && $period['quarter'] <= 3)
            ->unique(static fn (array $period): string => $period['year'].'-'.$period['quarter'])
            ->sort(static fn (array $a, array $b): int => [$b['year'], $b['quarter']] <=> [$a['year'], $a['quarter']])
            ->values()
            ->all();
    }
}
