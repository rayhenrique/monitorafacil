<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardSnapshotService;
use App\Services\FinancialProjectionService;
use Illuminate\View\View;
use Livewire\Component;

class FinancialSimulator extends Component
{
    public int $year;

    public int $quarter;

    public int $optimal = 0;

    public int $good = 0;

    public int $sufficient = 0;

    public int $regular = 0;

    public function useScenario(string $classification, DashboardSnapshotService $snapshots): void
    {
        if (! array_key_exists($classification, FinancialProjectionService::MONTHLY_RATES)) {
            return;
        }

        $total = $snapshots->teamTotals($this->year, $this->quarter)['esf'];

        if ($total === null) {
            return;
        }

        foreach (array_keys(FinancialProjectionService::MONTHLY_RATES) as $key) {
            $this->{$key} = $key === $classification ? $total : 0;
        }
    }

    public function render(DashboardSnapshotService $snapshots, FinancialProjectionService $projection): View
    {
        $teamCount = $snapshots->teamTotals($this->year, $this->quarter)['esf'];
        $distribution = [
            'optimal' => $this->optimal,
            'good' => $this->good,
            'sufficient' => $this->sufficient,
            'regular' => $this->regular,
        ];
        $assigned = array_sum($distribution);
        $monthly = $teamCount === null ? null : $projection->monthlyAmount($distribution, $teamCount);

        return view('livewire.dashboard.financial-simulator', [
            'teamCount' => $teamCount,
            'assigned' => $assigned,
            'monthly' => $monthly,
            'rates' => FinancialProjectionService::MONTHLY_RATES,
        ]);
    }
}
