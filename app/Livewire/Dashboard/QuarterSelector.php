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

    public function mount(): void
    {
        $currentYear = (int) now()->year;
        $currentQuarter = min(3, (int) ceil(now()->month / 4));

        if ($this->year < 2020 || $this->year > (int) now()->year) {
            $this->year = $currentYear;
        }

        if ($this->quarter < 1 || $this->quarter > 3) {
            $this->quarter = $currentQuarter;
        }
    }

    public function updatedYear(): void
    {
        if ($this->year < 2020 || $this->year > (int) now()->year) {
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
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return view('livewire.dashboard.quarter-selector', ['years' => $years]);
    }
}
