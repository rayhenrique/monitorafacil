<?php

declare(strict_types=1);

namespace App\Livewire\OralHealth;

use App\Models\OralHealth\OralHealthIndicatorSnapshot;
use App\Services\DashboardSnapshotService;
use App\Services\OralHealth\OralHealthService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Overview extends Component
{
    #[Url(as: 'ano', history: true)]
    public int $year = 0;

    #[Url(as: 'quadrimestre', history: true)]
    public int $quarter = 0;

    public bool $advancedSearchOpen = false;
    public string $searchTeamQuery = '';

    public function mount(DashboardSnapshotService $snapshots): void
    {
        $evaluatedYear = (int) now()->year;
        $evaluatedQuarter = min(3, max(1, (int) ceil(now()->month / 4)));

        $hasCurrentData = OralHealthIndicatorSnapshot::query()
            ->where('year', $evaluatedYear)
            ->where('quarter', $evaluatedQuarter)
            ->exists();

        if (! $hasCurrentData) {
            $latestWithData = OralHealthIndicatorSnapshot::query()
                ->where('year', '<=', $evaluatedYear)
                ->orderByDesc('year')
                ->orderByDesc('quarter')
                ->first();

            if ($latestWithData) {
                $evaluatedYear = (int) $latestWithData->year;
                $evaluatedQuarter = (int) $latestWithData->quarter;
            }
        }

        if ($this->year < 2020 || $this->year > 2100) {
            $this->year = $evaluatedYear;
        }

        if ($this->quarter < 1 || $this->quarter > 3) {
            $this->quarter = $evaluatedQuarter;
        }
    }

    public function toggleAdvancedSearch(): void
    {
        $this->advancedSearchOpen = ! $this->advancedSearchOpen;
    }

    public function closeAdvancedSearch(): void
    {
        $this->advancedSearchOpen = false;
        $this->searchTeamQuery = '';
    }

    public function selectPeriod(int $year, int $quarter): void
    {
        $this->year = $year;
        $this->quarter = $quarter;
        $this->advancedSearchOpen = false;
    }

    public function render(OralHealthService $service, DashboardSnapshotService $snapshots): View
    {
        $overview = $service->getMunicipalOverview($this->year, $this->quarter);

        $periods = collect($snapshots->periods())
            ->filter(fn ($p) => $p['year'] <= (int) now()->year)
            ->values()
            ->all();

        if (empty($periods)) {
            $periods = $snapshots->periods();
        }

        return view('livewire.oral-health.overview', [
            'overview' => $overview,
            'periods' => $periods,
        ]);
    }
}
