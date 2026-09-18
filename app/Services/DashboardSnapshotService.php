<?php

namespace App\Services;

use App\Enums\TeamType;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;

class DashboardSnapshotService
{
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
