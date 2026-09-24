<?php

namespace App\Livewire\Dashboard;

use App\Services\CvatNominalDwService;
use App\Services\CvatService;
use App\Services\DashboardSnapshotService;
use Illuminate\View\View;
use Livewire\Component;

class RegistrationsOverview extends Component
{
    public int $year;

    public int $quarter;

    public function render(
        DashboardSnapshotService $snapshots,
        CvatService $cvatService,
        CvatNominalDwService $cvatNominalDwService
    ): View {
        $snapshot = $snapshots->registrations($this->year, $this->quarter);
        $cvatSummary = $cvatService->getMunicipalSummary($this->year, $this->quarter);
        $cvatMetrics = $cvatNominalDwService->getMetrics(year: $this->year, quarter: $this->quarter);

        $groups = $snapshot === null ? [] : [
            [
                'title' => 'Cadastro individual',
                'code' => 'MICI',
                'unit' => 'cadastros individuais',
                'updated' => $snapshot->mici_updated_count,
                'outdated' => $snapshot->mici_outdated_count,
            ],
            [
                'title' => 'Cadastro domiciliar e territorial',
                'code' => 'MICDT',
                'unit' => 'fichas/domicílios',
                'updated' => $snapshot->micdt_updated_count,
                'outdated' => $snapshot->micdt_outdated_count,
            ],
        ];

        return view('livewire.dashboard.registrations-overview', [
            'snapshot' => $snapshot,
            'cvatMetrics' => $cvatMetrics,
            'groups' => $groups,
            'classifications' => [
                'optimal' => $cvatSummary['optimal_count'],
                'good' => $cvatSummary['good_count'],
                'sufficient' => $cvatSummary['sufficient_count'],
                'regular' => $cvatSummary['regular_count'],
            ],
            'cvatSummary' => $cvatSummary,
        ]);
    }
}
