<?php

namespace App\Livewire\TerritorialBonding;

use App\Services\CvatService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Vínculo e Acompanhamento Territorial · Componente II')]
class TerritorialBondingOverview extends Component
{
    use WithFileUploads;

    #[Url(as: 'aba')]
    public string $activeTab = 'cadastro';

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

    public bool $showImportModal = false;

    public $csvFile = null;

    public ?string $importMessage = null;

    public ?string $importStatus = null; // 'success' | 'error'

    public bool $isImporting = false;

    public function mount(): void
    {
        // Se a aba vier na rota ou URL, valida. Se for overview ou inválida, redireciona para a Relação Nominal
        $allowedTabs = ['cadastro', 'acompanhamento', 'teams', 'guide'];
        if (! in_array($this->activeTab, $allowedTabs, true)) {
            $this->redirect(route('territorial-bonding.nominal'), navigate: true);
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

    public function openImportModal(): void
    {
        $this->showImportModal = true;
        $this->importMessage = null;
        $this->importStatus = null;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->csvFile = null;
        $this->importMessage = null;
        $this->importStatus = null;
    }

    public function uploadCsv(CvatService $cvatService): void
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csvFile.required' => 'Por favor, selecione um arquivo CSV do Siaps para importar.',
            'csvFile.mimes' => 'O arquivo precisa ser no formato CSV ou texto delimitado.',
            'csvFile.max' => 'O arquivo não pode exceder 10MB.',
        ]);

        $this->isImporting = true;
        try {
            $originalName = $this->csvFile->getClientOriginalName();
            $result = $cvatService->importUploadedCsv($this->csvFile->getRealPath(), $originalName);

            $this->importStatus = 'success';
            $this->importMessage = $result['message'];
            $this->csvFile = null;
        } catch (\Throwable $e) {
            $this->importStatus = 'error';
            $this->importMessage = 'Erro na importação do CSV: ' . $e->getMessage();
        } finally {
            $this->isImporting = false;
        }
    }

    public function importServerDefaults(CvatService $cvatService): void
    {
        $this->isImporting = true;
        try {
            $result = $cvatService->importFromCsvFiles();
            $this->importStatus = 'success';
            $this->importMessage = "Importação dos arquivos padrão concluída: {$result['teams']} equipes e {$result['distributions']} distribuições dimensionais carregadas.";
        } catch (\Throwable $e) {
            $this->importStatus = 'error';
            $this->importMessage = 'Falha ao importar arquivos padrão: ' . $e->getMessage();
        } finally {
            $this->isImporting = false;
        }
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
