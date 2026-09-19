<?php

namespace App\Livewire\FamilyHealth;

use App\Services\C2ActiveSearchService;
use App\Services\C3ActiveSearchService;
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

    // --- PROPRIEDADES DA ABA BUSCA ATIVA C2 / C3 ---
    public string $searchCns = '';

    public string $searchCpf = '';

    public string $searchName = '';

    public string $searchCnes = '';

    public string $searchIne = '';

    public int $perPage = 30;

    public int $c2Page = 1;

    public int $c3Page = 1;

    public array $visibleColumns = [];

    public bool $showColumnsDropdown = false;

    // Modal Busca Avançada
    public bool $showAdvancedModal = false;

    public string $advTeam = '';

    public string $advMicroarea = '';

    public string $advCitizenName = '';

    public string $advCitizenCpf = '';

    public string $advCitizenCns = '';

    public string $advMotherName = '';

    public string $advPhone = '';

    public string $advStatus = ''; // C3: 'gestante', 'puerpera', 'encerrada'

    public string $advTrimester = ''; // C3: '1', '2', '3'

    public string $advMonth = '';

    public string $advMonthOption = '';

    public string $advQuarter = '';

    public string $advProfessionalCns = '';

    public string $advProfessionalName = '';

    public string $advRaceColor = '';

    public string $advAgeGroup = '';

    /** @var list<int> */
    public array $advAgeMonths = [];

    public ?string $advMici = null;

    public ?string $advMicdt = null;

    public ?string $advAccompanied = null;

    public ?string $advPracticeA = null;

    public ?string $advPracticeB = null;

    public ?string $advPracticeC = null;

    public ?string $advPracticeD = null;

    public ?string $advPracticeE = null;

    // Práticas adicionais para o Indicador C3 (F a K)
    public ?string $advPracticeF = null;

    public ?string $advPracticeG = null;

    public ?string $advPracticeH = null;

    public ?string $advPracticeI = null;

    public ?string $advPracticeJ = null;

    public ?string $advPracticeK = null;

    // Modal Detalhes do Cuidado Infantil (C2)
    public bool $showDetailModal = false;

    public ?array $selectedChild = null;

    // Modal Auditoria Clínica Gestante / Puérpera (C3)
    public bool $showPregnancyDetailModal = false;

    public ?array $selectedPregnancy = null;

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

        if ($this->indicator === 'c3') {
            $this->visibleColumns = C3ActiveSearchService::getDefaultVisibleColumns();
        } else {
            $this->visibleColumns = C2ActiveSearchService::getDefaultVisibleColumns();
        }
    }

    public function updatedSearchCns(): void
    {
        $this->c2Page = 1;
        $this->c3Page = 1;
    }

    public function updatedSearchCpf(): void
    {
        $this->c2Page = 1;
        $this->c3Page = 1;
    }

    public function updatedSearchName(): void
    {
        $this->c2Page = 1;
        $this->c3Page = 1;
    }

    public function updatedSearchCnes(): void
    {
        $this->c2Page = 1;
        $this->c3Page = 1;
    }

    public function updatedSearchIne(): void
    {
        $this->c2Page = 1;
        $this->c3Page = 1;
    }

    public function updatedPerPage(): void
    {
        $this->c2Page = 1;
        $this->c3Page = 1;
    }

    public function toggleColumn(string $column): void
    {
        if (in_array($column, $this->visibleColumns, true)) {
            $this->visibleColumns = array_values(array_diff($this->visibleColumns, [$column]));
        } else {
            $this->visibleColumns[] = $column;
        }
    }

    public function selectAllColumns(): void
    {
        if ($this->indicator === 'c3') {
            $this->visibleColumns = array_keys(C3ActiveSearchService::getAvailableColumns());
        } else {
            $this->visibleColumns = array_keys(C2ActiveSearchService::getAvailableColumns());
        }
    }

    public function resetDefaultColumns(): void
    {
        if ($this->indicator === 'c3') {
            $this->visibleColumns = C3ActiveSearchService::getDefaultVisibleColumns();
        } else {
            $this->visibleColumns = C2ActiveSearchService::getDefaultVisibleColumns();
        }
    }

    public function toggleColumnsDropdown(): void
    {
        $this->showColumnsDropdown = ! $this->showColumnsDropdown;
    }

    public function closeColumnsDropdown(): void
    {
        $this->showColumnsDropdown = false;
    }

    public function openAdvancedSearch(): void
    {
        $this->showAdvancedModal = true;
    }

    public function closeAdvancedSearch(): void
    {
        $this->showAdvancedModal = false;
    }

    public function applyAdvancedSearch(): void
    {
        $this->c2Page = 1;
        $this->c3Page = 1;
        $this->showAdvancedModal = false;
    }

    public function clearAdvancedFilters(): void
    {
        $this->advTeam = '';
        $this->advMicroarea = '';
        $this->advCitizenName = '';
        $this->advCitizenCpf = '';
        $this->advCitizenCns = '';
        $this->advMotherName = '';
        $this->advPhone = '';
        $this->advStatus = '';
        $this->advTrimester = '';
        $this->advMonth = '';
        $this->advMonthOption = '';
        $this->advQuarter = '';
        $this->advProfessionalCns = '';
        $this->advProfessionalName = '';
        $this->advRaceColor = '';
        $this->advAgeGroup = '';
        $this->advAgeMonths = [];
        $this->advMici = null;
        $this->advMicdt = null;
        $this->advAccompanied = null;
        $this->advPracticeA = null;
        $this->advPracticeB = null;
        $this->advPracticeC = null;
        $this->advPracticeD = null;
        $this->advPracticeE = null;
        $this->advPracticeF = null;
        $this->advPracticeG = null;
        $this->advPracticeH = null;
        $this->advPracticeI = null;
        $this->advPracticeJ = null;
        $this->advPracticeK = null;
        $this->c2Page = 1;
        $this->c3Page = 1;
    }

    public function setAgeGroup(string $group): void
    {
        if ($this->advAgeGroup === $group) {
            $this->advAgeGroup = '';
            $this->advAgeMonths = [];
        } else {
            $this->advAgeGroup = $group;
            $this->advAgeMonths = match ($group) {
                '0-6' => range(0, 6),
                '7-12' => range(7, 12),
                '13-24' => range(13, 24),
                default => [],
            };
        }
        $this->c2Page = 1;
    }

    public function toggleAgeMonth(int $month): void
    {
        if (in_array($month, $this->advAgeMonths, true)) {
            $this->advAgeMonths = array_values(array_filter($this->advAgeMonths, fn ($m) => $m !== $month));
        } else {
            $this->advAgeMonths[] = $month;
            sort($this->advAgeMonths);
        }

        $this->syncAgeGroupFromMonths();
        $this->c2Page = 1;
    }

    public function toggleAllAgeMonths(): void
    {
        if (count($this->advAgeMonths) >= 25) {
            $this->advAgeMonths = [];
            $this->advAgeGroup = '';
        } else {
            $this->advAgeMonths = range(0, 24);
            $this->advAgeGroup = '';
        }
        $this->c2Page = 1;
    }

    public function setMonthFilter(string $month): void
    {
        $this->advMonth = $month;
        $this->c2Page = 1;
    }

    public function clearMonthFilter(): void
    {
        $this->advMonth = '';
        $this->c2Page = 1;
    }

    public function setMonthOption(string $option): void
    {
        $this->advMonthOption = $option;
        $this->c2Page = 1;
    }

    private function syncAgeGroupFromMonths(): void
    {
        $m = $this->advAgeMonths;
        sort($m);
        if ($m === range(0, 6)) {
            $this->advAgeGroup = '0-6';
        } elseif ($m === range(7, 12)) {
            $this->advAgeGroup = '7-12';
        } elseif ($m === range(13, 24)) {
            $this->advAgeGroup = '13-24';
        } else {
            $this->advAgeGroup = '';
        }
    }

    public function toggleBooleanFilter(string $key, string $value): void
    {
        $current = $this->{$key};
        $this->{$key} = ($current === $value) ? null : $value;
        $this->c2Page = 1;
    }

    public function gotoC2Page(int $page): void
    {
        $this->c2Page = max(1, $page);
    }

    public function openChildDetail(int $childId, C2ActiveSearchService $c2Service): void
    {
        $cohort = $c2Service->getBaseCohort($this->year, $this->quarter, $this->selectedIne);
        $found = $cohort->firstWhere('id', $childId);

        if ($found) {
            $this->selectedChild = $found;
            $this->showDetailModal = true;
        }
    }

    public function closeChildDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedChild = null;
    }

    public function gotoC3Page(int $page): void
    {
        $this->c3Page = max(1, $page);
    }

    public function openPregnancyDetail(int $pregnancyId, C3ActiveSearchService $c3Service): void
    {
        $cohort = $c3Service->getBaseCohort($this->year, $this->quarter, $this->selectedIne);
        $found = $cohort->firstWhere('id', $pregnancyId);

        if ($found) {
            $this->selectedPregnancy = $found;
            $this->showPregnancyDetailModal = true;
        }
    }

    public function closePregnancyDetail(): void
    {
        $this->showPregnancyDetailModal = false;
        $this->selectedPregnancy = null;
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

    public function render(
        FamilyHealthService $service,
        DashboardSnapshotService $snapshots,
        C2ActiveSearchService $c2Service,
        C3ActiveSearchService $c3Service
    ): View {
        $data = $service->getIndicatorDetail($this->indicator, $this->year, $this->quarter, $this->selectedIne);
        $periods = $snapshots->periods();

        $filteredTeams = $data['teams'];

        // Se estiver no C1, C2 ou C3, aplica filtragem reativa de Equipe e Classificação
        if (in_array($this->indicator, ['c1', 'c2', 'c3'], true)) {
            if ($this->selectedIne) {
                $filteredTeams = $filteredTeams->filter(fn ($t) => $t->ine === $this->selectedIne);
            }

            if ($this->selectedClassification) {
                $filteredTeams = $filteredTeams->filter(function ($t) {
                    if ($this->selectedMonth !== null && isset($t->monthly_details[$this->selectedMonth])) {
                        return $t->monthly_details[$this->selectedMonth]['performance_level'] === $this->selectedClassification;
                    }

                    $level = $t->quarter_level ?? $t->performance_level;

                    return $level === $this->selectedClassification;
                });
            }
        }

        // Processamento da coorte nominal C2
        $c2NominalList = collect();
        $c2SummaryKpis = null;
        $c2FilterOptions = [];
        $c2AvailableColumns = C2ActiveSearchService::getAvailableColumns();
        $c2TotalItems = 0;
        $c2TotalPages = 1;

        // Processamento da coorte nominal C3
        $c3NominalList = collect();
        $c3SummaryKpis = null;
        $c3FilterOptions = [];
        $c3AvailableColumns = C3ActiveSearchService::getAvailableColumns();
        $c3TotalItems = 0;
        $c3TotalPages = 1;

        $activeFiltersCount = 0;

        if ($this->indicator === 'c2') {
            $c2BaseCohort = $c2Service->getBaseCohort($this->year, $this->quarter, $this->selectedIne);

            $filters = [
                'searchCns' => $this->searchCns,
                'searchCpf' => $this->searchCpf,
                'searchName' => $this->searchName,
                'searchCnes' => $this->searchCnes,
                'searchIne' => $this->searchIne,
                'advTeam' => $this->advTeam,
                'advMicroarea' => $this->advMicroarea,
                'advCitizenName' => $this->advCitizenName,
                'advCitizenCpf' => $this->advCitizenCpf,
                'advCitizenCns' => $this->advCitizenCns,
                'advMotherName' => $this->advMotherName,
                'advMonth' => $this->advMonth,
                'advMonthOption' => $this->advMonthOption,
                'advQuarter' => $this->advQuarter,
                'advProfessionalCns' => $this->advProfessionalCns,
                'advProfessionalName' => $this->advProfessionalName,
                'advRaceColor' => $this->advRaceColor,
                'advAgeGroup' => $this->advAgeGroup,
                'advAgeMonths' => $this->advAgeMonths,
                'advMici' => $this->advMici,
                'advMicdt' => $this->advMicdt,
                'advAccompanied' => $this->advAccompanied,
                'advPracticeA' => $this->advPracticeA,
                'advPracticeB' => $this->advPracticeB,
                'advPracticeC' => $this->advPracticeC,
                'advPracticeD' => $this->advPracticeD,
                'advPracticeE' => $this->advPracticeE,
                'baseYear' => $this->year,
                'baseQuarter' => $this->quarter,
            ];

            foreach ($filters as $k => $val) {
                if ($k === 'baseYear' || $k === 'baseQuarter' || $k === 'advMonthOption') {
                    continue;
                }
                if ($k === 'advAgeMonths') {
                    if (! empty($val) && empty($filters['advAgeGroup'])) {
                        $activeFiltersCount++;
                    }

                    continue;
                }
                if ($val !== '' && $val !== null && $val !== []) {
                    $activeFiltersCount++;
                }
            }

            $c2FilteredCohort = $c2Service->filterCohort($c2BaseCohort, $filters);
            $c2SummaryKpis = $c2Service->getSummaryKpis($c2FilteredCohort, $this->year, $this->quarter, $this->selectedMonth);
            $c2FilterOptions = $c2Service->getFilterOptions($this->year, $this->quarter);

            $c2TotalItems = $c2FilteredCohort->count();
            $c2TotalPages = max(1, (int) ceil($c2TotalItems / max(1, $this->perPage)));
            $this->c2Page = min(max(1, $this->c2Page), $c2TotalPages);

            $c2NominalList = $c2FilteredCohort->forPage($this->c2Page, $this->perPage);
        } elseif ($this->indicator === 'c3') {
            $c3BaseCohort = $c3Service->getBaseCohort($this->year, $this->quarter, $this->selectedIne);

            $filters = [
                'searchCns' => $this->searchCns,
                'searchCpf' => $this->searchCpf,
                'searchName' => $this->searchName,
                'searchCnes' => $this->searchCnes,
                'searchIne' => $this->searchIne,
                'advTeam' => $this->advTeam,
                'advMicroarea' => $this->advMicroarea,
                'advCitizenName' => $this->advCitizenName,
                'advCitizenCpf' => $this->advCitizenCpf,
                'advCitizenCns' => $this->advCitizenCns,
                'advPhone' => $this->advPhone,
                'advStatus' => $this->advStatus,
                'advTrimester' => $this->advTrimester,
                'advMonth' => $this->advMonth,
                'advMonthOption' => $this->advMonthOption,
                'advQuarter' => $this->advQuarter,
                'advProfessionalCns' => $this->advProfessionalCns,
                'advProfessionalName' => $this->advProfessionalName,
                'advRaceColor' => $this->advRaceColor,
                'advMici' => $this->advMici,
                'advAccompanied' => $this->advAccompanied,
                'advPracticeA' => $this->advPracticeA,
                'advPracticeB' => $this->advPracticeB,
                'advPracticeC' => $this->advPracticeC,
                'advPracticeD' => $this->advPracticeD,
                'advPracticeE' => $this->advPracticeE,
                'advPracticeF' => $this->advPracticeF,
                'advPracticeG' => $this->advPracticeG,
                'advPracticeH' => $this->advPracticeH,
                'advPracticeI' => $this->advPracticeI,
                'advPracticeJ' => $this->advPracticeJ,
                'advPracticeK' => $this->advPracticeK,
                'baseYear' => $this->year,
                'baseQuarter' => $this->quarter,
            ];

            foreach ($filters as $k => $val) {
                if ($k === 'baseYear' || $k === 'baseQuarter' || $k === 'advMonthOption') {
                    continue;
                }
                if ($val !== '' && $val !== null) {
                    $activeFiltersCount++;
                }
            }

            $c3FilteredCohort = $c3Service->filterCohort($c3BaseCohort, $filters);
            $c3SummaryKpis = $c3Service->getSummaryKpis($c3FilteredCohort, $this->year, $this->quarter, $this->selectedIne);
            $c3FilterOptions = $c3Service->getFilterOptions($this->year, $this->quarter);

            $c3TotalItems = $c3FilteredCohort->count();
            $c3TotalPages = max(1, (int) ceil($c3TotalItems / max(1, $this->perPage)));
            $this->c3Page = min(max(1, $this->c3Page), $c3TotalPages);

            $c3NominalList = $c3FilteredCohort->forPage($this->c3Page, $this->perPage);
        }

        return view('livewire.family-health.indicator-detail', [
            'data' => $data,
            'meta' => $data['meta'],
            'current' => $data['current'],
            'teams' => $data['teams'],
            'cohortTeams' => $data['cohort_teams'],
            'c1Teams' => $filteredTeams,
            'c2Teams' => $filteredTeams,
            'c3Teams' => $filteredTeams,
            'filteredTeams' => $filteredTeams,
            'activeSearchList' => $data['active_search_list'],
            'periods' => $periods,
            'c2NominalList' => $c2NominalList,
            'c2SummaryKpis' => $c2SummaryKpis,
            'c2FilterOptions' => $c2FilterOptions,
            'c2AvailableColumns' => $c2AvailableColumns,
            'c2TotalItems' => $c2TotalItems,
            'c2TotalPages' => $c2TotalPages,
            'c3NominalList' => $c3NominalList,
            'c3SummaryKpis' => $c3SummaryKpis,
            'c3FilterOptions' => $c3FilterOptions,
            'c3AvailableColumns' => $c3AvailableColumns,
            'c3TotalItems' => $c3TotalItems,
            'c3TotalPages' => $c3TotalPages,
            'activeFiltersCount' => $activeFiltersCount,
            'isRealDataAvailable' => $this->indicator === 'c2' ? C2ActiveSearchService::isRealDataAvailable($this->year, $this->quarter) : false,
            'realChildrenCount' => $this->indicator === 'c2' ? C2ActiveSearchService::getRealChildrenCount($this->year, $this->quarter, $this->selectedIne) : 0,
            'isRealC3DataAvailable' => $this->indicator === 'c3' ? C3ActiveSearchService::isRealDataAvailable($this->year, $this->quarter) : false,
            'realPregnanciesCount' => $this->indicator === 'c3' ? C3ActiveSearchService::getRealPregnanciesCount($this->year, $this->quarter, $this->selectedIne) : 0,
        ]);
    }
}
