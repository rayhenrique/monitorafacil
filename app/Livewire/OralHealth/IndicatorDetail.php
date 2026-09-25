<?php

declare(strict_types=1);

namespace App\Livewire\OralHealth;

use App\Models\OralHealth\OralHealthIndicatorSnapshot;
use App\Models\OralHealth\OralHealthNominalPatient;
use App\Services\OralHealth\OralHealthActiveSearchService;
use App\Services\OralHealth\OralHealthPracticeCalculator;
use App\Services\OralHealth\OralHealthService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class IndicatorDetail extends Component
{
    use WithPagination;

    public string $indicator = 'b1';

    #[Url(as: 'ano', history: true)]
    public int $year = 0;

    #[Url(as: 'quadrimestre', history: true)]
    public int $quarter = 0;

    #[Url(as: 'aba', history: true)]
    public string $activeTab = 'monthly_summary'; // 'monthly_summary', 'nominal'

    #[Url(as: 'equipe', history: true)]
    public ?string $selectedIne = null;

    #[Url(as: 'classificacao', history: true)]
    public ?string $selectedClassification = null;

    // Busca e filtros na Aba Nominal
    #[Url(as: 'busca', history: true)]
    public string $search = '';

    #[Url(as: 'situacao', history: true)]
    public string $statusFilter = 'all';

    public int $perPage = 30;

    public function mount(string $indicator = 'b1'): void
    {
        $this->indicator = strtolower($indicator);

        if (! in_array($this->indicator, ['b1', 'b2', 'b3', 'b4', 'b5', 'b6'], true)) {
            abort(404, 'Indicador de Saúde Bucal inválido.');
        }

        if ($this->year < 2020 || $this->year > 2100) {
            $this->year = (int) now()->year;
        }

        if ($this->quarter < 1 || $this->quarter > 3) {
            $this->quarter = min(3, max(1, (int) ceil(now()->month / 4)));
        }

        // Verifica se há dados na competência; se não houver, busca o quadrimestre mais recente
        $hasData = OralHealthIndicatorSnapshot::query()
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->where('indicator_code', $this->indicator)
            ->exists();

        if (! $hasData) {
            $latest = OralHealthIndicatorSnapshot::query()
                ->where('indicator_code', $this->indicator)
                ->orderByDesc('year')
                ->orderByDesc('quarter')
                ->first();

            if ($latest) {
                $this->year = (int) $latest->year;
                $this->quarter = (int) $latest->quarter;
            }
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedIne(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedClassification(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'selectedIne', 'selectedClassification']);
        $this->resetPage();
    }

    public function render(OralHealthService $service, OralHealthActiveSearchService $searchService): View
    {
        $metadata = OralHealthService::getIndicatorsMetadata();
        $meta = $metadata[$this->indicator] ?? $metadata['b1'];

        // Resumo de dados da equipe / município
        $data = $service->getIndicatorData($this->indicator, $this->year, $this->quarter, $this->selectedIne);

        // Filtra equipes para a tabela da Aba 1 (estritamente Equipes de Saúde Bucal - eSB)
        $teamsQuery = OralHealthIndicatorSnapshot::query()
            ->where('year', $this->year)
            ->where('quarter', $this->quarter)
            ->where('indicator_code', $this->indicator)
            ->whereNotNull('ine')
            ->where(function ($q): void {
                $q->where('team_name', 'like', 'ESB%')
                    ->orWhere('team_name', 'like', '%SAUDE BUCAL%')
                    ->orWhere('team_name', 'like', '%Saúde Bucal%')
                    ->orWhere('team_type', '88');
            })
            ->where('team_name', 'not like', 'ESF%')
            ->where('team_name', 'not like', 'USF%');

        if ($this->selectedIne) {
            $teamsQuery->where('ine', $this->selectedIne);
        }

        if ($this->selectedClassification && $this->selectedClassification !== 'all') {
            $teamsQuery->where('performance_level', $this->selectedClassification);
        }

        $teamsList = $teamsQuery->orderBy('team_name')->get();

        // Dados da Aba 2: Lista Nominal
        $nominalRecords = $searchService->paginate([
            'indicator_code' => $this->indicator,
            'year' => $this->year,
            'quarter' => $this->quarter,
            'search' => $this->search,
            'ine' => $this->selectedIne,
            'status' => $this->statusFilter,
            'per_page' => $this->perPage,
        ]);

        $nominalMetrics = $searchService->getMetrics([
            'indicator_code' => $this->indicator,
            'year' => $this->year,
            'quarter' => $this->quarter,
            'ine' => $this->selectedIne,
        ]);

        return view('livewire.oral-health.indicator-detail', [
            'meta' => $meta,
            'snapshot' => $data['snapshot'],
            'teamClassifications' => $data['team_classifications'],
            'teamsList' => $teamsList,
            'monthlyEvolution' => $data['monthly_evolution'],
            'nominalRecords' => $nominalRecords,
            'nominalMetrics' => $nominalMetrics,
            'allTeams' => $data['all_teams'],
        ]);
    }
}
