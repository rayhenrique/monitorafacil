<?php

namespace App\Livewire\TerritorialBonding;

use App\Services\CvatService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Monitoramento de Vínculo e Acompanhamento - Equipes (Mensal)')]
class TerritorialBondingOverview extends Component
{
    #[Url(as: 'aba')]
    public string $activeTab = 'teams';

    #[Url(as: 'ano')]
    public int $selectedYear = 2026;

    #[Url(as: 'q')]
    public int $selectedQuarter = 1;

    #[Url(as: 'busca')]
    public string $search = '';

    #[Url(as: 'cnes')]
    public string $filterCnes = '';

    #[Url(as: 'unidade')]
    public string $filterUnit = '';

    #[Url(as: 'ine')]
    public string $filterIne = '';

    #[Url(as: 'equipe')]
    public string $filterTeam = '';

    #[Url(as: 'classificacao')]
    public string $filterClassification = '';

    #[Url(as: 'por_pagina')]
    public int $perPage = 30;

    public string $sortBy = 'linked_registrations';

    public string $sortDirection = 'desc';

    public bool $showAdvancedModal = false;

    public ?float $advMinScore = null;

    public ?float $advMaxScore = null;

    public string $advClassification = '';

    public string $advTeamType = '';

    public function mount(CvatService $cvatService): void
    {
        if (request()->routeIs('territorial-bonding.overview') && ! request()->has('aba')) {
            $this->redirect(route('territorial-bonding.nominal'), navigate: true);

            return;
        }

        // Redireciona abas removidas para 'teams'
        if (in_array($this->activeTab, ['cadastro', 'acompanhamento', 'overview'], true)) {
            $this->activeTab = 'teams';
        }

        $allowedTabs = ['teams', 'guide'];
        if (! in_array($this->activeTab, $allowedTabs, true)) {
            $this->activeTab = 'teams';
        }

        if (! request()->has('ano') && ! request()->has('q')) {
            $latestPeriod = $cvatService->getLatestNominalPeriod();
            if ($latestPeriod) {
                $this->selectedYear = $latestPeriod['year'];
                $this->selectedQuarter = $latestPeriod['quarter'];
            }
        }
    }

    public function selectPeriod(int $year, int $quarter): void
    {
        $this->selectedYear = $year;
        $this->selectedQuarter = $quarter;
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

    public function openAdvancedModal(): void
    {
        $this->showAdvancedModal = true;
    }

    public function closeAdvancedModal(): void
    {
        $this->showAdvancedModal = false;
    }

    public function applyAdvancedSearch(): void
    {
        if ($this->advClassification) {
            $this->filterClassification = $this->advClassification;
        }
        $this->showAdvancedModal = false;
    }

    public function resetFilters(): void
    {
        $this->filterCnes = '';
        $this->filterUnit = '';
        $this->filterIne = '';
        $this->filterTeam = '';
        $this->filterClassification = '';
        $this->advMinScore = null;
        $this->advMaxScore = null;
        $this->advClassification = '';
        $this->advTeamType = '';
        $this->search = '';
        $this->perPage = 30;
    }

    public function render(CvatService $cvatService): View
    {
        // Busca equipes com os filtros da tela
        $allMatchingTeams = $cvatService->getTeamsList(
            year: $this->selectedYear,
            quarter: $this->selectedQuarter,
            search: $this->search,
            classificationFilter: $this->filterClassification ?: null,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            cnes: $this->filterCnes,
            facilityName: $this->filterUnit,
            ine: $this->filterIne,
            teamName: $this->filterTeam,
            minScore: $this->advMinScore,
            maxScore: $this->advMaxScore
        );

        $totalTeamsFound = $allMatchingTeams->count();
        $teams = $this->perPage > 0 ? $allMatchingTeams->take($this->perPage) : $allMatchingTeams;

        $monthlySummary = $cvatService->getMonthlyTeamSummary($this->selectedYear, $this->selectedQuarter);

        $selectedQuarterLabel = 'Q'.$this->selectedQuarter.'/'.substr((string) $this->selectedYear, -2);

        return view('livewire.territorial-bonding.overview', [
            'activeTab' => $this->activeTab,
            'teams' => $teams,
            'totalTeamsFound' => $totalTeamsFound,
            'monthlySummary' => $monthlySummary,
            'selectedQuarterLabel' => $selectedQuarterLabel,
            'filterCnes' => $this->filterCnes,
            'filterUnit' => $this->filterUnit,
            'filterIne' => $this->filterIne,
            'filterTeam' => $this->filterTeam,
            'filterClassification' => $this->filterClassification,
            'advMinScore' => $this->advMinScore,
            'advMaxScore' => $this->advMaxScore,
            'showAdvancedModal' => $this->showAdvancedModal,
        ]);
    }
}
