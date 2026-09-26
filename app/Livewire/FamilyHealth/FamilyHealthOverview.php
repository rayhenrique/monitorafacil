<?php

namespace App\Livewire\FamilyHealth;

use App\Models\FamilyHealthIndicatorSnapshot;
use App\Services\DashboardSnapshotService;
use App\Services\FamilyHealthService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class FamilyHealthOverview extends Component
{
    #[Url(as: 'ano', history: true)]
    public int $year = 0;

    #[Url(as: 'quadrimestre', history: true)]
    public int $quarter = 0;

    public function mount(DashboardSnapshotService $snapshots): void
    {
        // Define por padrão o quadrimestre avaliado atual do calendário
        $evaluatedYear = (int) now()->year;
        $evaluatedQuarter = min(3, max(1, (int) ceil(now()->month / 4)));

        // Se o período do calendário não tiver snapshots, busca o período avaliado mais recente disponível
        $hasCurrentData = FamilyHealthIndicatorSnapshot::query()
            ->where('year', $evaluatedYear)
            ->where('quarter', $evaluatedQuarter)
            ->exists();

        if (! $hasCurrentData) {
            $latestWithData = FamilyHealthIndicatorSnapshot::query()
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

    public bool $advancedSearchOpen = false;
    public string $searchTeamQuery = '';

    public function toggleAdvancedSearch(): void
    {
        $this->advancedSearchOpen = ! $this->advancedSearchOpen;
    }

    public function closeAdvancedSearch(): void
    {
        $this->advancedSearchOpen = false;
        $this->searchTeamQuery = '';
    }

    public function setPeriod(int $year, int $quarter): void
    {
        $this->year = $year;
        $this->quarter = $quarter;
    }

    public function selectPeriod(int $year, int $quarter): void
    {
        $this->year = $year;
        $this->quarter = $quarter;
        $this->advancedSearchOpen = false;
    }

    public function render(FamilyHealthService $service, DashboardSnapshotService $snapshots): View
    {
        $overview = $service->getMunicipalOverview($this->year, $this->quarter);
        
        $periods = collect($snapshots->periods())
            ->filter(fn ($p) => $p['year'] <= (int) now()->year)
            ->values()
            ->all();

        if (empty($periods)) {
            $periods = $snapshots->periods();
        }

        $teams = $overview['available_teams'] ?? [];

        if (auth()->user()?->isOperator() && auth()->user()->cnes) {
            $cnesInes = array_flip(\App\Models\CvatTeamEvaluation::where('cnes', auth()->user()->cnes)->pluck('ine')->all());
            $teams = array_values(array_filter($teams, fn ($team) => isset($cnesInes[$team['ine'] ?? ''])));
        }

        if (! empty(trim($this->searchTeamQuery))) {
            $term = mb_strtolower(trim($this->searchTeamQuery));
            $teams = array_values(array_filter($teams, function ($team) use ($term) {
                return str_contains(mb_strtolower($team['name'] ?? ''), $term) || str_contains((string) ($team['ine'] ?? ''), $term);
            }));
        }

        return view('livewire.family-health.overview', [
            'overview' => $overview,
            'periods' => $periods,
            'teams' => $teams,
        ]);
    }
}
