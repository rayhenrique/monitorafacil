<?php

namespace App\Livewire\FamilyHealth;

use App\Services\DashboardSnapshotService;
use App\Services\FamilyHealthService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class IndicatorDetail extends Component
{
    public string $indicator = 'c1';

    #[Url(as: 'ano', history: true)]
    public int $year = 0;

    #[Url(as: 'quadrimestre', history: true)]
    public int $quarter = 0;

    #[Url(as: 'equipe', history: true)]
    public ?string $selectedIne = null;

    #[Url(as: 'mes', history: true)]
    public ?int $selectedMonth = null; // null = todos os 4 meses, ou 1, 2, 3, 4 (mês no quadrimestre)

    #[Url(as: 'classificacao', history: true)]
    public ?string $selectedClassification = null; // null = todas, ou 'regular', 'suficiente', 'bom', 'otimo'

    public string $activeTab = 'dashboard'; // 'dashboard', 'teams', 'active_search', 'rules'

    public function mount(string $indicator, DashboardSnapshotService $snapshots): void
    {
        $this->indicator = strtolower($indicator);

        if (! FamilyHealthService::getIndicatorMeta($this->indicator)) {
            abort(404, 'Indicador de Saúde da Família não encontrado.');
        }

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

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function selectTeam(?string $ine): void
    {
        $this->selectedIne = $ine ?: null;
    }

    public function setPeriod(int $year, int $quarter): void
    {
        $this->year = $year;
        $this->quarter = $quarter;
    }

    public function setPeriodString(string $value): void
    {
        if (str_contains($value, '-')) {
            [$year, $quarter] = explode('-', $value, 2);
            $this->setPeriod((int) $year, (int) $quarter);
        }
    }

    public function setMonth(?int $month): void
    {
        $this->selectedMonth = $month ? (int) $month : null;
    }

    public function setClassification(?string $classification): void
    {
        $this->selectedClassification = $classification ?: null;
    }

    public function resetFilters(): void
    {
        $this->selectedIne = null;
        $this->selectedMonth = null;
        $this->selectedClassification = null;
    }

    public function render(FamilyHealthService $service, DashboardSnapshotService $snapshots): View
    {
        $data = $service->getIndicatorDetail($this->indicator, $this->year, $this->quarter, $this->selectedIne);
        $periods = $snapshots->periods();

        $c1Teams = $data['teams'];

        // Se estiver no C1, aplica filtragem de Equipe e Classificação
        if ($this->indicator === 'c1') {
            if ($this->selectedIne) {
                $c1Teams = $c1Teams->filter(fn ($t) => $t->ine === $this->selectedIne);
            }

            if ($this->selectedClassification) {
                $c1Teams = $c1Teams->filter(function ($t) {
                    if ($this->selectedMonth !== null && isset($t->monthly_details[$this->selectedMonth])) {
                        return $t->monthly_details[$this->selectedMonth]['performance_level'] === $this->selectedClassification;
                    }

                    $level = $t->quarter_level ?? $t->performance_level;
                    return $level === $this->selectedClassification;
                });
            }
        }

        return view('livewire.family-health.indicator-detail', [
            'data' => $data,
            'meta' => $data['meta'],
            'current' => $data['current'],
            'teams' => $data['teams'],
            'c1Teams' => $c1Teams,
            'activeSearchList' => $data['active_search_list'],
            'periods' => $periods,
        ]);
    }
}
