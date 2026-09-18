<?php

namespace App\Livewire\FamilyHealth;

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
        $latest = $snapshots->periods()[0] ?? [
            'year' => (int) now()->year,
            'quarter' => min(3, (int) ceil(now()->month / 4)),
        ];

        if ($this->year < 2020 || $this->year > 2100) {
            $this->year = $latest['year'];
        }

        if ($this->quarter < 1 || $this->quarter > 3) {
            $this->quarter = $latest['quarter'];
        }
    }

    public function setPeriod(int $year, int $quarter): void
    {
        $this->year = $year;
        $this->quarter = $quarter;
    }

    public function render(FamilyHealthService $service, DashboardSnapshotService $snapshots): View
    {
        $overview = $service->getMunicipalOverview($this->year, $this->quarter);
        $periods = $snapshots->periods();

        return view('livewire.family-health.overview', [
            'overview' => $overview,
            'periods' => $periods,
        ]);
    }
}
