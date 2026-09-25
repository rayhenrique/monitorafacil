<?php

declare(strict_types=1);

namespace App\Livewire\OralHealth;

use App\Models\OralHealth\OralHealthMonthlySnapshot;
use App\Services\OralHealth\OralHealthPracticeCalculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Dashboard Mensal de Equipes · Saúde Bucal')]
class MonthlyDashboard extends Component
{
    use WithPagination;

    #[Url(as: 'ano')]
    public int $selectedYear = 2026;

    #[Url(as: 'mes')]
    public int $selectedMonth = 9;

    #[Url(as: 'equipe')]
    public string $filterTeam = '';

    #[Url(as: 'cnes')]
    public string $filterCnes = '';

    #[Url(as: 'pag')]
    public int $perPage = 10;

    // Modal de Busca Avançada
    public bool $advancedModalOpen = false;

    public string $advCnes = '';

    public string $advIne = '';

    public int $advMonth = 9;

    public const INDICATOR_SHORT_NAMES = [
        'b1' => 'B1 - Programada',
        'b2' => 'B2 - Concluído',
        'b3' => 'B3 - Exodontias',
        'b4' => 'B4 - Supervisionada',
        'b5' => 'B5 - Procedimentos',
        'b6' => 'B6 - Atraumático',
    ];

    public function updatedSelectedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedFilterTeam(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCnes(): void
    {
        $this->resetPage();
    }

    public function setMonth(int $month): void
    {
        $this->selectedMonth = $month;
        $this->resetPage();
    }

    public function openAdvancedModal(): void
    {
        $this->advCnes = $this->filterCnes;
        $this->advIne = $this->filterTeam;
        $this->advMonth = $this->selectedMonth;
        $this->advancedModalOpen = true;
    }

    public function closeAdvancedModal(): void
    {
        $this->advancedModalOpen = false;
    }

    public function applyAdvancedFilters(): void
    {
        $this->filterCnes = $this->advCnes;
        $this->filterTeam = $this->advIne;
        $this->selectedMonth = $this->advMonth;
        $this->advancedModalOpen = false;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['filterTeam', 'filterCnes', 'advCnes', 'advIne']);
        $this->resetPage();
    }

    public function exportCsv(): StreamedResponse
    {
        $year = $this->selectedYear;
        $month = $this->selectedMonth;
        $fileName = sprintf('dashboard_mensal_saude_bucal_%s_M%02d_%s.csv', $year, $month, now()->format('Ymd_His'));

        $snapshots = OralHealthMonthlySnapshot::where('year', $year)
            ->where('month', $month)
            ->when(filled($this->filterTeam), fn ($q) => $q->where('ine', $this->filterTeam)->orWhere('team_name', 'like', "%{$this->filterTeam}%"))
            ->when(filled($this->filterCnes), fn ($q) => $q->where('cnes', $this->filterCnes))
            ->orderBy('team_name')
            ->orderBy('indicator_code')
            ->get();

        return response()->streamDownload(function () use ($snapshots, $year, $month): void {
            $handle = fopen('php://output', 'w');
            if (! $handle) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'Ano',
                'Mês',
                'INE Equipe',
                'Nome da Equipe',
                'CNES',
                'Estabelecimento',
                'Indicador',
                'Nome Indicador',
                'Numerador',
                'Denominador',
                'Pontuação (%)',
                'Classificação',
            ], ';');

            foreach ($snapshots as $s) {
                $code = strtolower((string) $s->indicator_code);
                fputcsv($handle, [
                    $year,
                    $month,
                    $s->ine,
                    $s->team_name,
                    $s->cnes,
                    $s->facility_name,
                    strtoupper($code),
                    self::INDICATOR_SHORT_NAMES[$code] ?? $code,
                    $s->numerator,
                    $s->denominator,
                    number_format((float) $s->score_percent, 2, ',', '.'),
                    strtoupper((string) $s->performance_level),
                ], ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render(): View
    {
        $availableMonths = OralHealthMonthlySnapshot::select('month')
            ->where('year', $this->selectedYear)
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->toArray();

        if (empty($availableMonths)) {
            $availableMonths = [5, 6, 7, 8, 9, 10, 11, 12];
        }

        // Busca equipes odontológicas ativas com snapshots nesta competência
        $allSnapshots = OralHealthMonthlySnapshot::where('year', $this->selectedYear)
            ->where('month', $this->selectedMonth)
            ->when(filled($this->filterTeam), function ($q): void {
                $term = trim($this->filterTeam);
                $q->where(function ($sub) use ($term): void {
                    $sub->where('ine', $term)->orWhere('team_name', 'like', "%{$term}%");
                });
            })
            ->when(filled($this->filterCnes), fn ($q) => $q->where('cnes', trim($this->filterCnes)))
            ->orderBy('team_name')
            ->get();

        // Agrupa snapshots por equipe (INE)
        $teamsGrouped = [];
        foreach ($allSnapshots as $snap) {
            $ine = (string) $snap->ine;
            if (! isset($teamsGrouped[$ine])) {
                $teamsGrouped[$ine] = [
                    'ine' => $ine,
                    'team_name' => $snap->team_name,
                    'cnes' => $snap->cnes,
                    'facility_name' => $snap->facility_name,
                    'indicators' => [],
                ];
            }

            $code = strtolower((string) $snap->indicator_code);
            $level = strtolower((string) $snap->performance_level);
            $score = (float) $snap->score_percent;

            // Formatação de badges e barras conforme Design System
            $badgeLabel = match ($level) {
                'otimo' => 'Ótimo',
                'bom' => 'Bom',
                'suficiente' => 'Suficiente',
                default => 'Regular',
            };

            $badgeClass = match ($level) {
                'otimo' => 'bg-sky-100 text-sky-800 border-sky-200',
                'bom' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'suficiente' => 'bg-amber-100 text-amber-800 border-amber-200',
                default => 'bg-rose-100 text-rose-800 border-rose-200',
            };

            $barColor = match ($level) {
                'otimo' => 'bg-sky-500',
                'bom' => 'bg-emerald-500',
                'suficiente' => 'bg-amber-500',
                default => 'bg-rose-500',
            };

            // Cálculo da largura visual da barra de progresso (0% a 100%)
            $barWidth = match ($code) {
                'b1' => min(100.0, max(4.0, ($score / 2.0) * 100.0)),
                'b3' => min(100.0, max(4.0, (10.0 - $score) * 10.0)),
                'b4' => min(100.0, max(4.0, ($score / 25.0) * 100.0)),
                default => min(100.0, max(4.0, $score)),
            };

            $teamsGrouped[$ine]['indicators'][$code] = [
                'code' => $code,
                'name' => self::INDICATOR_SHORT_NAMES[$code] ?? strtoupper($code),
                'score' => $score,
                'denominator' => (int) $snap->denominator,
                'numerator' => (int) $snap->numerator,
                'level' => $level,
                'badge_label' => $badgeLabel,
                'badge_class' => $badgeClass,
                'bar_color' => $barColor,
                'bar_width' => $barWidth,
            ];
        }

        // Garante a ordem dos 6 indicadores B1 a B6 em cada equipe
        $orderedCodes = ['b1', 'b2', 'b3', 'b4', 'b5', 'b6'];
        foreach ($teamsGrouped as &$tData) {
            $sortedInds = [];
            foreach ($orderedCodes as $c) {
                if (isset($tData['indicators'][$c])) {
                    $sortedInds[$c] = $tData['indicators'][$c];
                } else {
                    $sortedInds[$c] = [
                        'code' => $c,
                        'name' => self::INDICATOR_SHORT_NAMES[$c] ?? strtoupper($c),
                        'score' => 0.0,
                        'denominator' => 0,
                        'numerator' => 0,
                        'level' => 'regular',
                        'badge_label' => 'Regular',
                        'badge_class' => 'bg-rose-100 text-rose-800 border-rose-200',
                        'bar_color' => 'bg-rose-500',
                        'bar_width' => 4.0,
                    ];
                }
            }
            $tData['indicators'] = $sortedInds;
        }
        unset($tData);

        // Paginação manual das equipes
        $totalTeams = count($teamsGrouped);
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->perPage;
        $itemsForCurrentPage = array_slice(array_values($teamsGrouped), $offset, $this->perPage);

        $paginatedTeams = new ConcretePaginator(
            $itemsForCurrentPage,
            $totalTeams,
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Listas para os filtros
        $teamOptions = OralHealthMonthlySnapshot::select('ine', 'team_name')
            ->where('year', $this->selectedYear)
            ->distinct()
            ->orderBy('team_name')
            ->get();

        $unitOptions = OralHealthMonthlySnapshot::select('cnes', 'facility_name')
            ->where('year', $this->selectedYear)
            ->distinct()
            ->orderBy('facility_name')
            ->get();

        return view('livewire.oral-health.monthly-dashboard', [
            'teams' => $paginatedTeams,
            'totalTeams' => $totalTeams,
            'availableMonths' => $availableMonths,
            'teamOptions' => $teamOptions,
            'unitOptions' => $unitOptions,
        ]);
    }
}
