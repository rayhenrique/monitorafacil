<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardSnapshotService;
use Illuminate\View\View;
use Livewire\Component;

class TeamsOverview extends Component
{
    public int $year;

    public int $quarter;

    public function render(DashboardSnapshotService $snapshots): View
    {
        $totals = $snapshots->teamTotals($this->year, $this->quarter);

        return view('livewire.dashboard.teams-overview', [
            'totals' => $totals,
            'complete' => ! in_array(null, $totals, true),
        ]);
    }
}
