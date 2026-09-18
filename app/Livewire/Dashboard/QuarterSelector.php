<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardSnapshotService;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class QuarterSelector extends Component
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

    public function updatedYear(): void
    {
        if ($this->year < 2020 || $this->year > 2100) {
            $this->year = (int) now()->year;
        }
    }

    public function updatedQuarter(): void
    {
        if ($this->quarter < 1 || $this->quarter > 3) {
            $this->quarter = 1;
        }
    }

    public function render(DashboardSnapshotService $snapshots): View
    {
        $years = collect($snapshots->periods())
            ->pluck('year')
            ->push($this->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return view('livewire.dashboard.quarter-selector', ['years' => $years]);
    }
}
