<?php

namespace App\Livewire\TerritorialBonding;

use App\Services\CvatService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Vínculo e Acompanhamento Territorial · Componente II')]
class TerritorialBondingOverview extends Component
{
    #[Url(as: 'aba')]
    public string $activeTab = 'overview';

    #[Url(as: 'ano')]
    public int $selectedYear = 2026;

    #[Url(as: 'q')]
    public int $selectedQuarter = 1;

    #[Url(as: 'busca')]
    public string $search = '';

    #[Url(as: 'conceito')]
    public string $classificationFilter = 'ALL';

    public string $sortBy = 'final_score';

    public string $sortDirection = 'desc';

    public function mount(): void
    {
        // Se a aba vier na rota ou URL, valida
        $allowedTabs = ['overview', 'cadastro', 'acompanhamento', 'teams', 'guide'];
        if (! in_array($this->activeTab, $allowedTabs, true)) {
            $this->activeTab = 'overview';
        }
    }

    public function selectPeriod(int $year, int $quarter): void
    {
        $this->selectedYear = $year;
        $this->selectedQuarter = $quarter;
    }

    public function filterByClassification(string $classification): void
    {
        $this->classificationFilter = $classification;
    }

    public function sortByField(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->classificationFilter = 'ALL';
    }

    public function render(CvatService $cvatService): View
    {
        $availableQuarters = $cvatService->getAvailableQuarters();
        $chartsData = $cvatService->getDimensionChartsData();
        $summary = $cvatService->getMunicipalSummary($this->selectedYear, $this->selectedQuarter);
        $teams = $cvatService->getTeamsList(
            $this->selectedYear,
            $this->selectedQuarter,
            $this->search,
            $this->classificationFilter,
            $this->sortBy,
            $this->sortDirection
        );

        $selectedQuarterLabel = 'Q' . $this->selectedQuarter . '/' . substr((string) $this->selectedYear, -2);

        return view('livewire.territorial-bonding.overview', [
            'availableQuarters' => $availableQuarters,
            'chartsData' => $chartsData,
            'summary' => $summary,
            'teams' => $teams,
            'selectedQuarterLabel' => $selectedQuarterLabel,
        ]);
    }
}
