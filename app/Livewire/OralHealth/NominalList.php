<?php

declare(strict_types=1);

namespace App\Livewire\OralHealth;

use App\Models\OralHealth\OralHealthIndicatorSnapshot;
use App\Models\OralHealth\OralHealthNominalCitizen;
use App\Services\OralHealth\OralHealthService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Busca Geral Nominal · Saúde Bucal')]
class NominalList extends Component
{
    use WithPagination;

    #[Url(as: 'cns')]
    public string $filterCns = '';

    #[Url(as: 'cpf')]
    public string $filterCpf = '';

    #[Url(as: 'nome')]
    public string $filterName = '';

    #[Url(as: 'cnes')]
    public string $filterCnes = '';

    #[Url(as: 'ine')]
    public string $filterIne = '';

    #[Url(as: 'pag')]
    public int $perPage = 30;

    #[Url(as: 'ano')]
    public int $selectedYear = 2026;

    #[Url(as: 'quad')]
    public int $selectedQuarter = 3;

    // Colunas visíveis
    public array $visibleColumns = [
        'cns' => true,
        'cpf' => true,
        'birth_date' => true,
        'name' => true,
        'age' => true,
        'cnes' => true,
        'ine' => true,
        'professional' => true,
        'microarea' => true,
        'mici' => true,
        'b1' => true,
        'b2' => true,
        'b3' => true,
        'b4' => true,
        'b5' => true,
        'b6' => true,
        'actions' => true,
    ];

    public bool $columnsDropdownOpen = false;

    // Modal de Busca Avançada
    public bool $advancedModalOpen = false;

    public string $advDistrict = '';

    public string $advCnes = '';

    public string $advIne = '';

    public string $advMicroarea = '';

    public string $advCitizenName = '';

    public string $advMotherName = '';

    public string $advCpf = '';

    public string $advCns = '';

    public string $advMonth = '09 / 2026';

    public string $advProfName = '';

    public string $advProfCns = '';

    public ?string $advMiciUpdated = null; // 'SIM', 'NAO', null

    public ?string $advMicdtUpdated = null; // 'SIM', 'NAO', null

    public ?string $advB1 = null; // 'SIM', 'NAO', null

    public ?string $advB2 = null; // 'SIM', 'NAO', null

    public ?string $advB3 = null; // 'SIM', 'NAO', null

    public ?string $advB4 = null; // 'SIM', 'NAO', null

    public ?string $advB5 = null; // 'SIM', 'NAO', null

    public ?string $advB6 = null; // 'SIM', 'NAO', null

    public bool $advOnlyWithoutBond = false;

    // Modal de Detalhes do Cidadão
    public bool $detailsModalOpen = false;

    public ?OralHealthNominalCitizen $selectedCitizen = null;

    // Estado da legenda sanfonada (aberta por padrão como na imagem de referência)
    public bool $legendOpen = true;

    // Ordenação
    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

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

    public function updatedFilterCnes(): void
    {
        $this->resetPage();
    }

    public function updatedFilterIne(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function openAdvancedModal(): void
    {
        // Pré-carrega valores rápidos no modal avançado
        $this->advCns = $this->filterCns;
        $this->advCpf = $this->filterCpf;
        $this->advCitizenName = $this->filterName;
        $this->advCnes = $this->filterCnes;
        $this->advIne = $this->filterIne;
        $this->advancedModalOpen = true;
    }

    public function closeAdvancedModal(): void
    {
        $this->advancedModalOpen = false;
    }

    public function applyAdvancedFilters(): void
    {
        $this->filterCns = $this->advCns;
        $this->filterCpf = $this->advCpf;
        $this->filterName = $this->advCitizenName;
        $this->filterCnes = $this->advCnes;
        $this->filterIne = $this->advIne;

        $this->advancedModalOpen = false;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'filterCns',
            'filterCpf',
            'filterName',
            'filterCnes',
            'filterIne',
            'advDistrict',
            'advCnes',
            'advIne',
            'advMicroarea',
            'advCitizenName',
            'advMotherName',
            'advCpf',
            'advCns',
            'advProfName',
            'advProfCns',
            'advMiciUpdated',
            'advMicdtUpdated',
            'advB1',
            'advB2',
            'advB3',
            'advB4',
            'advB5',
            'advB6',
            'advOnlyWithoutBond',
        ]);
        $this->resetPage();
    }

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = ! $this->visibleColumns[$column];
        }
    }

    public function toggleLegend(): void
    {
        $this->legendOpen = ! $this->legendOpen;
    }

    public function openDetails(int $id): void
    {
        $this->selectedCitizen = OralHealthNominalCitizen::find($id);
        if ($this->selectedCitizen) {
            $this->detailsModalOpen = true;
        }
    }

    public function closeDetails(): void
    {
        $this->detailsModalOpen = false;
        $this->selectedCitizen = null;
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function exportCsv(): StreamedResponse
    {
        $query = $this->buildQuery(false);
        $fileName = sprintf('busca_geral_saude_bucal_%s_%s.csv', $this->selectedYear, now()->format('Ymd_His'));

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            if (! $handle) {
                return;
            }

            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID PEC',
                'CNS Cidadão',
                'CPF Cidadão',
                'Nome do Cidadão',
                'Data de Nascimento',
                'Idade',
                'CNES',
                'Unidade',
                'INE',
                'Equipe',
                'Microárea',
                'MICI Atualizada',
                'B1 (1ª Consulta)',
                'B2 (Trat. Concluído)',
                'B3 (Exodontia)',
                'B4 (Escovação)',
                'B5 (Preventivos)',
                'B6 (ART)',
                'Situação Tratamento',
                'Data 1ª Consulta',
                'Data Trat. Concluído',
                'Data Última Escovação',
                'Último Atendimento Odonto',
                'Cirurgião-Dentista',
            ], ';');

            foreach ($query->cursor() as $c) {
                fputcsv($handle, [
                    $c->cidadao_pec_id,
                    $c->cns ?: '',
                    $c->cpf ?: '',
                    $c->name,
                    $c->birth_date?->format('d/m/Y') ?: '',
                    $c->age_years,
                    $c->cnes ?: '',
                    $c->facility_name ?: '',
                    $c->ine ?: '',
                    $c->team_name ?: '',
                    $c->microarea ?: '',
                    $c->mici_updated ? 'Sim' : 'Não',
                    $c->b1_count,
                    $c->b2_count,
                    $c->b3_count,
                    $c->b4_eligible ? $c->b4_count : 'NA',
                    $c->b5_count,
                    $c->b6_count,
                    $c->treatment_status,
                    $c->first_consultation_date?->format('d/m/Y') ?: '',
                    $c->treatment_completed_date?->format('d/m/Y') ?: '',
                    $c->last_brushing_date?->format('d/m/Y') ?: '',
                    $c->last_attendance_date?->format('d/m/Y') ?: '',
                    $c->last_professional_name ?: '',
                ], ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return Builder<OralHealthNominalCitizen>
     */
    private function buildQuery(bool $withOrdering = true): Builder
    {
        $query = OralHealthNominalCitizen::query()
            ->where('year', $this->selectedYear)
            ->where('quarter', $this->selectedQuarter);

        // Filtros Rápidos
        if (filled($this->filterCns)) {
            $digits = preg_replace('/\D/', '', $this->filterCns);
            $query->where('cns', 'like', "%{$digits}%");
        }

        if (filled($this->filterCpf)) {
            $digits = preg_replace('/\D/', '', $this->filterCpf);
            $query->where('cpf', 'like', "%{$digits}%");
        }

        if (filled($this->filterName)) {
            $term = trim($this->filterName);
            $query->where('name', 'like', "%{$term}%");
        }

        if (filled($this->filterCnes)) {
            $query->where('cnes', trim($this->filterCnes));
        }

        if (filled($this->filterIne)) {
            $query->where('ine', trim($this->filterIne));
        }

        // Filtros Avançados
        if (filled($this->advMicroarea)) {
            $query->where('microarea', trim($this->advMicroarea));
        }

        if (filled($this->advDistrict)) {
            $query->where('district', trim($this->advDistrict));
        }

        if (filled($this->advProfName)) {
            $query->where('professional_name', 'like', '%' . trim($this->advProfName) . '%');
        }

        if (filled($this->advProfCns)) {
            $digits = preg_replace('/\D/', '', $this->advProfCns);
            $query->where('professional_cns', 'like', "%{$digits}%");
        }

        if ($this->advMiciUpdated === 'SIM') {
            $query->where('mici_updated', true);
        } elseif ($this->advMiciUpdated === 'NAO') {
            $query->where('mici_updated', false);
        }

        if ($this->advMicdtUpdated === 'SIM') {
            $query->where('micdt_updated', true);
        } elseif ($this->advMicdtUpdated === 'NAO') {
            $query->where('micdt_updated', false);
        }

        if ($this->advB1 === 'SIM') {
            $query->where('b1_count', '>', 0);
        } elseif ($this->advB1 === 'NAO') {
            $query->where('b1_count', 0);
        }

        if ($this->advB2 === 'SIM') {
            $query->where('b2_count', '>', 0);
        } elseif ($this->advB2 === 'NAO') {
            $query->where('b2_count', 0);
        }

        if ($this->advB3 === 'SIM') {
            $query->where('b3_count', '>', 0);
        } elseif ($this->advB3 === 'NAO') {
            $query->where('b3_count', 0);
        }

        if ($this->advB4 === 'SIM') {
            $query->where('b4_count', '>', 0);
        } elseif ($this->advB4 === 'NAO') {
            $query->where('b4_count', 0);
        }

        if ($this->advB5 === 'SIM') {
            $query->where('b5_count', '>', 0);
        } elseif ($this->advB5 === 'NAO') {
            $query->where('b5_count', 0);
        }

        if ($this->advB6 === 'SIM') {
            $query->where('b6_count', '>', 0);
        } elseif ($this->advB6 === 'NAO') {
            $query->where('b6_count', 0);
        }

        if ($this->advOnlyWithoutBond) {
            $query->where('is_linked', false);
        }

        if ($withOrdering) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query;
    }

    /**
     * Calcula as métricas consolidadas dos 6 cards no topo da página.
     *
     * @return array<string, mixed>
     */
    private function getKpiSummary(): array
    {
        // Se houver filtro de equipe, busca o snapshot da equipe; caso contrário, busca o municipal
        $activeIne = filled($this->filterIne) ? trim($this->filterIne) : null;

        $snapshots = OralHealthIndicatorSnapshot::where('year', $this->selectedYear)
            ->where('quarter', $this->selectedQuarter)
            ->when($activeIne, fn ($q) => $q->where('ine', $activeIne), fn ($q) => $q->whereNull('ine'))
            ->get()
            ->keyBy('indicator_code');

        $kpis = [];
        $codes = ['b1', 'b2', 'b3', 'b4', 'b5', 'b6'];
        foreach ($codes as $code) {
            $s = $snapshots->get($code);
            $num = $s ? (int) $s->numerator : 0;
            $den = $s ? (int) $s->denominator : 0;
            $rate = $s ? (float) $s->score_percent : 0.0;
            $kpis[$code] = [
                'num' => $num,
                'den' => $den,
                'rate' => $rate,
            ];
        }

        $generalDenominator = (int) ($kpis['b1']['den'] ?? 0);
        if ($generalDenominator === 0) {
            $generalDenominator = OralHealthNominalCitizen::where('year', $this->selectedYear)
                ->where('quarter', $this->selectedQuarter)
                ->count();
        }

        $kpis['general_denominator'] = $generalDenominator;

        return $kpis;
    }

    public function render(OralHealthService $service): View
    {
        $records = $this->buildQuery()->paginate($this->perPage);
        $kpis = $this->getKpiSummary();

        // Carrega lista de equipes odontológicas e estabelecimentos para os seletores
        $teams = OralHealthNominalCitizen::select('ine', 'team_name')
            ->where('year', $this->selectedYear)
            ->where('quarter', $this->selectedQuarter)
            ->whereNotNull('ine')
            ->distinct()
            ->orderBy('team_name')
            ->get();

        $units = OralHealthNominalCitizen::select('cnes', 'facility_name')
            ->where('year', $this->selectedYear)
            ->where('quarter', $this->selectedQuarter)
            ->whereNotNull('cnes')
            ->distinct()
            ->orderBy('facility_name')
            ->get();

        $districts = OralHealthNominalCitizen::select('district')
            ->where('year', $this->selectedYear)
            ->where('quarter', $this->selectedQuarter)
            ->whereNotNull('district')
            ->distinct()
            ->pluck('district');

        return view('livewire.oral-health.nominal-list', [
            'records' => $records,
            'kpis' => $kpis,
            'teams' => $teams,
            'units' => $units,
            'districts' => $districts,
            'totalRecordsCount' => $records->total(),
            'activeColumnsCount' => count(array_filter($this->visibleColumns)),
        ]);
    }
}
