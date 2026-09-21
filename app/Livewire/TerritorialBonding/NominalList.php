<?php

namespace App\Livewire\TerritorialBonding;

use App\Models\CvatNominalCitizen;
use App\Services\CvatNominalDwService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Relação Nominal · Vínculo e Acompanhamento Territorial')]
class NominalList extends Component
{
    use WithPagination;

    #[Url(as: 'cns')]
    public string $filterCns = '';

    #[Url(as: 'cpf')]
    public string $filterCpf = '';

    #[Url(as: 'nome')]
    public string $filterName = '';

    #[Url(as: 'cns_prof')]
    public string $filterProfCns = '';

    #[Url(as: 'nome_prof')]
    public string $filterProfName = '';

    #[Url(as: 'cnes')]
    public string $filterCnes = '';

    #[Url(as: 'ine')]
    public string $filterIne = '';

    #[Url(as: 'raca')]
    public string $filterRaceColor = 'ALL';

    #[Url(as: 'pag')]
    public int $perPage = 30;

    // Filtros Avançados
    public bool $advancedModalOpen = false;

    public string $advMicroarea = '';

    public string $advMiciUpdated = '';

    public string $advMicdtUpdated = '';

    public string $advHasMicdt = '';

    public string $advVulnerability = 'ALL';

    public string $advSocialBenefit = 'ALL';

    public string $advAccompanied = '';

    public string $advLinked = '';

    public string $advTeam = '';

    // Modal de Detalhes do Cidadão
    public bool $detailsModalOpen = false;

    public ?CvatNominalCitizen $selectedCitizen = null;

    // Ordenação
    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    // Estado do Acordeão Dimensão Acompanhamento
    public bool $acompanhamentoExpanded = true;

    public function updatedFilterCns(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCpf(): void
    {
        $this->resetPage();
    }

    public function updatedFilterName(): void
    {
        $this->resetPage();
    }

    public function updatedFilterProfCns(): void
    {
        $this->resetPage();
    }

    public function updatedFilterProfName(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCnes(): void
    {
        $this->resetPage();
    }

    public function updatedFilterIne(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRaceColor(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortByField(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function toggleAcompanhamento(): void
    {
        $this->acompanhamentoExpanded = ! $this->acompanhamentoExpanded;
    }

    public function openAdvancedModal(): void
    {
        $this->advancedModalOpen = true;
    }

    public function closeAdvancedModal(): void
    {
        $this->advancedModalOpen = false;
    }

    public function applyAdvancedFilters(): void
    {
        $this->advancedModalOpen = false;
        $this->resetPage();
    }

    public function clearAllFilters(): void
    {
        $this->filterCns = '';
        $this->filterCpf = '';
        $this->filterName = '';
        $this->filterProfCns = '';
        $this->filterProfName = '';
        $this->filterCnes = '';
        $this->filterIne = '';
        $this->filterRaceColor = 'ALL';
        $this->advMicroarea = '';
        $this->advMiciUpdated = '';
        $this->advMicdtUpdated = '';
        $this->advHasMicdt = '';
        $this->advVulnerability = 'ALL';
        $this->advSocialBenefit = 'ALL';
        $this->advAccompanied = '';
        $this->advLinked = '';
        $this->advTeam = '';
        $this->resetPage();
    }

    public function openDetails(int $citizenId): void
    {
        $this->selectedCitizen = CvatNominalCitizen::where('source', CvatNominalDwService::SOURCE)
            ->where('id', $citizenId)
            ->first();

        if ($this->selectedCitizen) {
            $this->detailsModalOpen = true;
        }
    }

    public function closeDetails(): void
    {
        $this->detailsModalOpen = false;
        $this->selectedCitizen = null;
    }

    public function render(CvatNominalDwService $service): View
    {
        $metrics = $service->getMetrics();

        $filters = [
            'cns' => $this->filterCns,
            'cpf' => $this->filterCpf,
            'name' => $this->filterName,
            'professional_cns' => $this->filterProfCns,
            'professional_name' => $this->filterProfName,
            'cnes' => $this->filterCnes,
            'ine' => $this->filterIne,
            'race_color' => $this->filterRaceColor,
            'microarea' => $this->advMicroarea,
            'team' => $this->advTeam,
            'mici_updated' => $this->advMiciUpdated,
            'micdt_updated' => $this->advMicdtUpdated,
            'has_micdt' => $this->advHasMicdt,
            'vulnerability_type' => $this->advVulnerability,
            'social_benefit' => $this->advSocialBenefit,
            'is_accompanied' => $this->advAccompanied,
            'is_linked' => $this->advLinked,
            'sort_by' => $this->sortBy,
            'sort_dir' => $this->sortDirection,
            'per_page' => $this->perPage,
        ];

        $citizens = $service->queryCitizens($filters);

        $teamsList = CvatNominalCitizen::query()
            ->where('source', CvatNominalDwService::SOURCE)
            ->where('registration_eligible', true)
            ->whereNotNull('ine')
            ->where('ine', '!=', '')
            ->select(['ine', 'team_name'])
            ->distinct()
            ->orderBy('team_name')
            ->get();

        return view('livewire.territorial-bonding.nominal-list', array_merge(
            get_object_vars($this),
            [
                'metrics' => $metrics,
                'citizens' => $citizens,
                'teamsList' => $teamsList,
                'races' => ['ALL' => 'Todas as Raças/Cores', 'Parda' => 'Parda', 'Branca' => 'Branca', 'Preta' => 'Preta', 'Amarela' => 'Amarela', 'Indígena' => 'Indígena'],
                'totalRecordsCount' => $citizens->total(),
            ]
        ));
    }
}
