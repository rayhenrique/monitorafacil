<?php

namespace App\Livewire\TerritorialBonding;

use App\Models\CvatNominalCitizen;
use App\Services\CvatNominalDwService;
use App\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    #[Url(as: 'equipe')]
    public string $selectedTeam = '';

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

    public function updatedSelectedTeam(): void
    {
        $this->advTeam = $this->selectedTeam;
        $this->resetPage();
    }

    public function updatedAdvTeam(): void
    {
        $this->selectedTeam = $this->advTeam;
        $this->resetPage();
    }

    public function clearTeamFilter(): void
    {
        $this->selectedTeam = '';
        $this->advTeam = '';
        $this->resetPage();
    }

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
        $this->selectedTeam = $this->advTeam;
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
        $this->selectedTeam = '';
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

    /**
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        $activeTeam = $this->selectedTeam ?: $this->advTeam;

        return [
            'cns' => $this->filterCns,
            'cpf' => $this->filterCpf,
            'name' => $this->filterName,
            'professional_cns' => $this->filterProfCns,
            'professional_name' => $this->filterProfName,
            'cnes' => $this->filterCnes,
            'ine' => $this->filterIne,
            'race_color' => $this->filterRaceColor,
            'microarea' => $this->advMicroarea,
            'team' => $activeTeam,
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
    }

    public function exportCsv(CvatNominalDwService $service): StreamedResponse
    {
        $filters = $this->getFilters();
        $activeTeam = $filters['team'] ?? '';
        $query = $service->buildCitizensQuery($filters);

        $teamSlug = $activeTeam ? 'equipe_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $activeTeam) . '_' : 'consolidado_';
        $fileName = 'relacao_nominal_' . $teamSlug . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            if (! $handle) {
                return;
            }

            // UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header columns
            fputcsv($handle, [
                'ID PEC',
                'CNS Cidadão',
                'CPF Cidadão',
                'Nome do Cidadão',
                'Data de Nascimento',
                'Idade',
                'Raça/Cor',
                'Sexo',
                'CNES',
                'Estabelecimento',
                'INE Equipe',
                'Nome da Equipe',
                'CNS Profissional',
                'Nome do Profissional',
                'Microárea',
                'MICI Atualizado',
                'Data Último MICI',
                'Possui MICDT',
                'MICDT Atualizado',
                'Data Último MICDT',
                'Vínculo Válido',
                'Tipo Vulnerabilidade',
                'Benefício Social',
                'Acompanhado',
                'Total Contatos',
                'Contatos Atenção',
                'Data Último Atendimento',
                'Endereço',
            ], ';');

            foreach ($query->cursor() as $citizen) {
                fputcsv($handle, [
                    $citizen->cidadao_pec_id,
                    $citizen->cns ?: '',
                    $citizen->cpf ?: '',
                    $citizen->name ?: '',
                    $citizen->birth_date ? Carbon::parse($citizen->birth_date)->format('d/m/Y') : '',
                    $citizen->age ?? '',
                    $citizen->race_color ?: '',
                    $citizen->gender ?: '',
                    $citizen->cnes ?: '',
                    $citizen->facility_name ?: '',
                    $citizen->ine ?: '',
                    $citizen->team_name ?: '',
                    $citizen->professional_cns ?: '',
                    $citizen->professional_name ?: '',
                    $citizen->microarea ?: '',
                    $citizen->mici_updated ? 'SIM' : 'NÃO',
                    $citizen->mici_date ? Carbon::parse($citizen->mici_date)->format('d/m/Y') : '',
                    $citizen->has_micdt ? 'SIM' : 'NÃO',
                    $citizen->micdt_updated ? 'SIM' : 'NÃO',
                    $citizen->micdt_date ? Carbon::parse($citizen->micdt_date)->format('d/m/Y') : '',
                    $citizen->is_linked ? 'SIM' : 'NÃO',
                    $citizen->vulnerability_type ?: '',
                    $citizen->social_benefit ?: '',
                    $citizen->is_accompanied ? 'SIM' : 'NÃO',
                    $citizen->total_contacts ?? 0,
                    $citizen->care_contacts ?? 0,
                    $citizen->last_visit_date ? Carbon::parse($citizen->last_visit_date)->format('d/m/Y') : '',
                    $citizen->address ?: '',
                ], ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportPdf(CvatNominalDwService $service, SettingsService $settingsService): StreamedResponse
    {
        $filters = $this->getFilters();
        $activeTeam = $filters['team'] ?? '';
        $metrics = $service->getMetrics(team: $activeTeam ?: null);

        $query = $service->buildCitizensQuery($filters);
        $totalCount = (clone $query)->count();

        // Limit to 1000 records for PDF to avoid browser/memory timeouts
        $citizens = $query->limit(1000)->get();

        $teamName = '';
        if ($activeTeam) {
            $teamModel = CvatNominalCitizen::where('source', CvatNominalDwService::SOURCE)
                ->where(function ($q) use ($activeTeam) {
                    $q->where('ine', $activeTeam)
                      ->orWhere('team_name', 'like', '%'.$activeTeam.'%');
                })
                ->select(['team_name', 'ine'])
                ->first();
            $teamName = $teamModel ? "{$teamModel->team_name} (INE: {$teamModel->ine})" : $activeTeam;
        }

        $settings = $settingsService->all();

        $pdf = Pdf::loadView('reports.nominal-pdf', [
            'metrics' => $metrics,
            'citizens' => $citizens,
            'totalCount' => $totalCount,
            'teamName' => $teamName,
            'settings' => $settings,
        ])->setPaper('a4', 'landscape');

        $teamSlug = $activeTeam ? 'equipe_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $activeTeam) . '_' : 'consolidado_';
        $fileName = 'relacao_nominal_' . $teamSlug . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render(CvatNominalDwService $service): View
    {
        $activeTeam = $this->selectedTeam ?: $this->advTeam;

        $metrics = $service->getMetrics(team: $activeTeam ?: null);

        $filters = $this->getFilters();

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

        $activeTeamModel = null;
        if ($activeTeam) {
            $activeTeamModel = $teamsList->firstWhere('ine', $activeTeam);
        }

        return view('livewire.territorial-bonding.nominal-list', array_merge(
            get_object_vars($this),
            [
                'metrics' => $metrics,
                'citizens' => $citizens,
                'teamsList' => $teamsList,
                'activeTeam' => $activeTeam,
                'activeTeamModel' => $activeTeamModel,
                'races' => ['ALL' => 'Todas as Raças/Cores', 'Parda' => 'Parda', 'Branca' => 'Branca', 'Preta' => 'Preta', 'Amarela' => 'Amarela', 'Indígena' => 'Indígena'],
                'totalRecordsCount' => $citizens->total(),
            ]
        ));
    }
}
