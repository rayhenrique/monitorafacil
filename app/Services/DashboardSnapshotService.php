<?php

namespace App\Services;

use App\Enums\TeamType;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\FamilyHealthIndicatorSnapshot;

class DashboardSnapshotService
{
    /**
     * @return array<string, array{optimal:int,good:int,sufficient:int,regular:int,evaluated_teams:int,has_data:bool}>
     */
    public function familyHealthPerformance(int $year, int $quarter): array
    {
        $versions = [
            'c1' => C1DwService::VERSION,
            'c2' => C2DwService::VERSION,
            'c3' => C3DwService::VERSION,
        ];
        $distribution = [];

        foreach (array_keys($versions) as $code) {
            $distribution[$code] = [
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
                'evaluated_teams' => 0,
                'has_data' => false,
            ];
        }

        $snapshots = FamilyHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereIn('indicator_code', array_keys($versions))
            ->whereNotNull('ine')
            ->where(function ($query): void {
                $query->whereIn('team_type', ['70', '76'])
                    ->orWhereNull('team_type');
            })
            ->get(['indicator_code', 'performance_level', 'good_practices_breakdown']);

        foreach ($snapshots as $snapshot) {
            $code = strtolower($snapshot->indicator_code);
            $version = $snapshot->good_practices_breakdown['calculation_version'] ?? null;

            if (! isset($versions[$code]) || $version !== $versions[$code]) {
                continue;
            }

            $classification = match (strtolower($snapshot->performance_level)) {
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

        return $teams->concat($registrations)
            ->map(static fn ($row): array => [
                'year' => (int) $row->year,
                'quarter' => (int) $row->quarter,
            ])
            ->unique(static fn (array $period): string => $period['year'].'-'.$period['quarter'])
            ->sort(static fn (array $a, array $b): int => [$b['year'], $b['quarter']] <=> [$a['year'], $a['quarter']])
            ->values()
            ->all();
    }
}
