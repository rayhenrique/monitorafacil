<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardSnapshotService;
use Illuminate\View\View;
use Livewire\Component;

class RegistrationsOverview extends Component
{
    public int $year;

    public int $quarter;

    public function render(DashboardSnapshotService $snapshots): View
    {
        $snapshot = $snapshots->registrations($this->year, $this->quarter);

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
            'groups' => $groups,
            'classifications' => [
                'optimal' => 0,
                'good' => 0,
                'sufficient' => 0,
                'regular' => 0,
            ],
        ]);
    }
}
