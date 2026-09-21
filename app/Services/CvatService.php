<?php

namespace App\Services;

use App\Models\CvatDimensionDistribution;
use App\Models\CvatNominalCitizen;
use App\Models\CvatTeamEvaluation;
use App\Models\FamilyHealthIndicatorSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CvatService
{
    private const TEAM_POPULATION_PARAMETER = 2500;

    /** @var array<string, array{type: string, name: string, cnes: string}>|null */
    private ?array $eligiblePrimaryCareTeams = null;

    /**
     * Importa os dados oficiais do Siaps a partir dos arquivos CSV.
     *
     * @return array{distributions: int, teams: int}
     */
    public function importFromCsvFiles(): array
    {
        $importedDistributions = $this->importDimensionDistributions();
        $importedTeams = $this->importTeamEvaluations();

        return [
            'distributions' => $importedDistributions,
            'teams' => $importedTeams,
        ];
    }

    /**
     * Importa as distribuições históricas de dimensões (Q2/25, Q3/25, Q1/26).
     */
    public function importDimensionDistributions(?string $customPath = null): int
    {
        $path = $customPath ?: base_path('importacao/siaps/Vínculo_e_Acompanhamento_Territorial.csv');

        if (! File::exists($path)) {
            // Tenta caminho relativo com fallback
            $path = base_path('importacao/siaps/Vinculo_e_Acompanhamento_Territorial.csv');
            if (! File::exists($path)) {
                return 0;
            }
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            return 0;
        }

        $imported = 0;

        foreach ($lines as $line) {
            // Linha com delimitador ponto e vírgula
            $columns = array_map(function ($col) {
                return trim(trim($col), "\" \t\n\r\0\x0B");
            }, explode(';', $line));

            // Procura linha de dados (ex: "2025Q2", "AL", "270915", ...)
            if (count($columns) < 10) {
                continue;
            }

            $rawPeriod = $columns[0]; // "2025Q2" ou "2026Q1"
            if (! preg_match('/^(\d{4})Q([1-3])$/i', $rawPeriod, $matches)) {
                continue;
            }

            $year = (int) $matches[1];
            $quarter = (int) $matches[2];
            $quarterLabel = 'Q'.$quarter.'/'.substr((string) $year, -2);
            $teamType = $columns[4] ?: 'eSF';
            $dimensionRaw = $columns[5];

            $dimensionCode = str_contains(mb_strtolower($dimensionRaw), 'acompanhamento') ? 'acompanhamento' : 'cadastro';
            $dimensionName = $dimensionCode === 'acompanhamento' ? 'CVAT - Dimensão Acompanhamento' : 'CVAT - Dimensão Cadastro';

            $regular = (int) ($columns[6] ?? 0);
            $sufficient = (int) ($columns[7] ?? 0);
            $good = (int) ($columns[8] ?? 0);
            $optimal = (int) ($columns[9] ?? 0);
            $total = $regular + $sufficient + $good + $optimal;

            CvatDimensionDistribution::updateOrCreate(
                [
                    'year' => $year,
                    'quarter' => $quarter,
                    'team_type' => $teamType,
                    'dimension_code' => $dimensionCode,
                ],
                [
                    'quarter_label' => $quarterLabel,
                    'dimension_name' => $dimensionName,
                    'regular_count' => $regular,
                    'sufficient_count' => $sufficient,
                    'good_count' => $good,
                    'optimal_count' => $optimal,
                    'total_teams' => $total > 0 ? $total : 19,
                ]
            );

            $imported++;
        }

        return $imported;
    }

    /**
     * Importa a avaliação nominal das equipes no quadrimestre.
     */
    public function importTeamEvaluations(?string $customPath = null): int
    {
        $path = $customPath ?: base_path('importacao/siaps/Desempenho Quadrimestral - Componente Vínculo e Acompanhamento Territorial.csv');

        if (! File::exists($path)) {
            return 0;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            return 0;
        }

        $imported = 0;

        foreach ($lines as $line) {
            $columns = array_map(function ($col) {
                return trim(trim($col), "\" \t\n\r\0\x0B");
            }, explode(';', $line));

            if (count($columns) < 10) {
                continue;
            }

            $rawQuarter = $columns[0]; // "Q1/26"
            if (! preg_match('/^Q([1-3])\/(\d{2})$/i', $rawQuarter, $matches)) {
                continue;
            }

            $quarter = (int) $matches[1];
            $year = 2000 + (int) $matches[2];
            $cnes = $columns[1];
            $facilityName = $columns[2];
            $ine = str_pad($columns[3], 10, '0', STR_PAD_LEFT);
            $teamType = $columns[4] ?: 'eSF';
            $teamName = $columns[5];
            $registrationScore = (float) str_replace(',', '.', $columns[6]);
            $monitoringScore = (float) str_replace(',', '.', $columns[7]);
            $finalScore = (float) str_replace(',', '.', $columns[8]);
            $finalClassification = mb_strtoupper($columns[9]);

            CvatTeamEvaluation::updateOrCreate(
                [
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => $ine,
                ],
                [
                    'quarter_label' => $rawQuarter,
                    'cnes' => $cnes,
                    'facility_name' => $facilityName,
                    'team_type' => $teamType,
                    'team_name' => $teamName,
                    'parameter' => self::TEAM_POPULATION_PARAMETER,
                    'linked_registrations' => 0,
                    'linked_ratio' => 0,
                    'registration_result' => 0,
                    'registration_score' => $registrationScore,
                    'monitoring_result' => 0,
                    'monitoring_score' => $monitoringScore,
                    'final_score' => $finalScore,
                    'final_classification' => $finalClassification,
                ]
            );

            $imported++;
        }

        return $imported;
    }

    /**
     * Importa um arquivo CSV arbitrário do Siaps enviado pelo usuário no módulo.
     * Identifica automaticamente se é o arquivo de Desempenho de Equipes ou de Distribuição de Dimensões.
     *
     * @return array{type: string, count: int, message: string}
     */
    public function importUploadedCsv(string $realPath, string $originalName): array
    {
        if (! File::exists($realPath)) {
            throw new \InvalidArgumentException('Arquivo não encontrado para importação.');
        }

        $lines = file($realPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            throw new \InvalidArgumentException('O arquivo CSV enviado está vazio.');
        }

        $sample = mb_strtolower(implode("\n", array_slice($lines, 0, 8)));

        if (str_contains($sample, 'desempenho') || str_contains($sample, 'nota final') || str_contains($sample, 'q1/26') || str_contains($sample, 'classificação') || str_contains($sample, 'classificacao') || str_contains($sample, 'ine')) {
            $count = $this->importTeamEvaluations($realPath);
            if ($count > 0) {
                return [
                    'type' => 'teams',
                    'count' => $count,
                    'message' => "Arquivo '{$originalName}' importado com sucesso: {$count} equipes avaliadas e cadastradas no CVAT.",
                ];
            }
        }

        if (str_contains($sample, 'dimensão') || str_contains($sample, 'dimensao') || str_contains($sample, 'cadastro') || str_contains($sample, 'acompanhamento') || str_contains($sample, '2025q2') || str_contains($sample, '2026q1')) {
            $count = $this->importDimensionDistributions($realPath);
            if ($count > 0) {
                return [
                    'type' => 'distributions',
                    'count' => $count,
                    'message' => "Arquivo '{$originalName}' importado com sucesso: {$count} distribuições dimensionais (Cadastro/Acompanhamento) atualizadas.",
                ];
            }
        }

        // Tentativas de fallback
        $teamsCount = $this->importTeamEvaluations($realPath);
        if ($teamsCount > 0) {
            return [
                'type' => 'teams',
                'count' => $teamsCount,
                'message' => "Arquivo '{$originalName}' importado com sucesso: {$teamsCount} avaliações de equipes atualizadas.",
            ];
        }

        $distCount = $this->importDimensionDistributions($realPath);
        if ($distCount > 0) {
            return [
                'type' => 'distributions',
                'count' => $distCount,
                'message' => "Arquivo '{$originalName}' importado com sucesso: {$distCount} distribuições dimensionais atualizadas.",
            ];
        }

        throw new \RuntimeException("O formato do arquivo '{$originalName}' não foi reconhecido como um relatório oficial válido do Siaps para o componente CVAT.");
    }

    /**
     * Retorna a lista de quadrimestres oficiais disponíveis.
     *
     * @return list<array{year: int, quarter: int, label: string, is_latest: bool, has_team_details: bool}>
     */
    public function getAvailableQuarters(): array
    {
        if (! Schema::hasTable('cvat_nominal_citizens')) {
            return [];
        }

        $eligibleTeams = $this->eligiblePrimaryCareTeams();
        if ($eligibleTeams === []) {
            return [];
        }

        return CvatNominalCitizen::query()
            ->where('source', CvatNominalDwService::SOURCE)
            ->where('registration_eligible', true)
            ->whereNotNull('ine')
            ->where('ine', '!=', '')
            ->whereIn('ine', array_keys($eligibleTeams))
            ->select(['year', 'month'])
            ->distinct()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function (CvatNominalCitizen $period): array {
                $quarter = (int) ceil($period->month / 4);
                $shortLabel = 'Q'.$quarter.'/'.substr((string) $period->year, -2);

                return [
                    'year' => $period->year,
                    'quarter' => $quarter,
                    'label' => $shortLabel.' (extração parcial)',
                    'short_label' => $shortLabel,
                    'is_latest' => false,
                    'has_team_details' => false,
                ];
            })
            ->unique(fn (array $period): string => $period['year'].'-'.$period['quarter'])
            ->values()
            ->map(function (array $period, int $index): array {
                $period['is_latest'] = $index === 0;

                return $period;
            })
            ->all();
    }

    /**
     * Retorna a competência nominal mais recente disponível no MySQL local.
     *
     * @return array{year: int, month: int, quarter: int, last_attendance_date: ?string}|null
     */
    public function getLatestNominalPeriod(?int $year = null, ?int $quarter = null): ?array
    {
        if (! Schema::hasTable('cvat_nominal_citizens')) {
            return null;
        }

        $eligibleTeams = $this->eligiblePrimaryCareTeams();
        if ($eligibleTeams === []) {
            return null;
        }

        $query = CvatNominalCitizen::query()
            ->where('source', CvatNominalDwService::SOURCE)
            ->where('registration_eligible', true)
            ->whereNotNull('ine')
            ->where('ine', '!=', '')
            ->whereIn('ine', array_keys($eligibleTeams));

        if ($year !== null) {
            $query->where('year', $year);
        }

        if ($quarter !== null) {
            $query->whereBetween('month', [(($quarter - 1) * 4) + 1, $quarter * 4]);
        }

        $period = $query
            ->select(['year', 'month'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        if (! $period) {
            return null;
        }

        $lastAttendanceDate = CvatNominalCitizen::query()
            ->where('source', CvatNominalDwService::SOURCE)
            ->where('registration_eligible', true)
            ->where('year', $period->year)
            ->where('month', $period->month)
            ->whereNotNull('ine')
            ->where('ine', '!=', '')
            ->whereIn('ine', array_keys($eligibleTeams))
            ->max('last_visit_date');

        return [
            'year' => $period->year,
            'month' => $period->month,
            'quarter' => (int) ceil($period->month / 4),
            'last_attendance_date' => $lastAttendanceDate,
        ];
    }

    /**
     * Retorna os dados para os gráficos de barras empilhadas do Siaps.
     *
     * @return array{
     *     cadastro: list<array{period: string, year: int, quarter: int, regular: int, sufficient: int, good: int, optimal: int, total: int}>,
     *     acompanhamento: list<array{period: string, year: int, quarter: int, regular: int, sufficient: int, good: int, optimal: int, total: int}>
     * }
     */
    public function getDimensionChartsData(): array
    {
        // Se ainda não houver dados populados, importa
        if (CvatDimensionDistribution::count() === 0) {
            $this->importDimensionDistributions();
        }

        $records = CvatDimensionDistribution::orderBy('year', 'desc')
            ->orderBy('quarter', 'desc')
            ->get();

        $cadastro = [];
        $acompanhamento = [];

        // Ordem oficial como na imagem do Siaps: Q1/26 no topo, Q3/25 no meio, Q2/25 embaixo
        $periods = [
            ['year' => 2026, 'quarter' => 1, 'period' => 'Q1/26'],
            ['year' => 2025, 'quarter' => 3, 'period' => 'Q3/25'],
            ['year' => 2025, 'quarter' => 2, 'period' => 'Q2/25'],
        ];

        foreach ($periods as $p) {
            $cadRec = $records->first(fn ($r) => $r->year === $p['year'] && $r->quarter === $p['quarter'] && $r->dimension_code === 'cadastro');
            $acompRec = $records->first(fn ($r) => $r->year === $p['year'] && $r->quarter === $p['quarter'] && $r->dimension_code === 'acompanhamento');

            $cadastro[] = [
                'period' => $p['period'],
                'year' => $p['year'],
                'quarter' => $p['quarter'],
                'regular' => $cadRec ? $cadRec->regular_count : 0,
                'sufficient' => $cadRec ? $cadRec->sufficient_count : 0,
                'good' => $cadRec ? $cadRec->good_count : 0,
                'optimal' => $cadRec ? $cadRec->optimal_count : 0,
                'total' => $cadRec ? $cadRec->total_teams : 19,
            ];

            $acompanhamento[] = [
                'period' => $p['period'],
                'year' => $p['year'],
                'quarter' => $p['quarter'],
                'regular' => $acompRec ? $acompRec->regular_count : 0,
                'sufficient' => $acompRec ? $acompRec->sufficient_count : 0,
                'good' => $acompRec ? $acompRec->good_count : 0,
                'optimal' => $acompRec ? $acompRec->optimal_count : 0,
                'total' => $acompRec ? $acompRec->total_teams : 19,
            ];
        }

        return [
            'cadastro' => $cadastro,
            'acompanhamento' => $acompanhamento,
        ];
    }

    /**
     * Resumo municipal consolidado do quadrimestre.
     *
     * @return array{
     *     has_data: bool,
     *     average_final_score: float,
     *     municipal_classification: string,
     *     average_registration: float,
     *     average_monitoring: float,
     *     total_teams: int,
     *     optimal_count: int,
     *     good_count: int,
     *     sufficient_count: int,
     *     regular_count: int,
     *     financial_incentive: string
     * }
     */
    public function getMunicipalSummary(int $year, int $quarter): array
    {
        return [
            'has_data' => false,
            'average_final_score' => 0.0,
            'municipal_classification' => 'NÃO AFERÍVEL',
            'average_registration' => 0.0,
            'average_monitoring' => 0.0,
            'total_teams' => 0,
            'optimal_count' => 0,
            'good_count' => 0,
            'sufficient_count' => 0,
            'regular_count' => 0,
            'financial_incentive' => 'Pendente',
        ];
    }

    /**
     * Retorna a coleção de equipes avaliadas para o período, priorizando avaliações persistidas.
     *
     * @return Collection<int, CvatTeamEvaluation>
     */
    public function getEvaluations(int $year, int $quarter): Collection
    {
        // Sem BPC/PBF, bônus de satisfação e quatro meses históricos, a NT não permite
        // uma classificação quadrimestral. Os CSVs antigos não têm proveniência verificável.
        return collect();
    }

    /**
     * Lista de equipes com filtros e ordenação.
     *
     * @return Collection<int, CvatTeamEvaluation>
     */
    public function getTeamsList(
        int $year,
        int $quarter,
        ?string $search = null,
        ?string $classificationFilter = null,
        string $sortBy = 'final_score',
        string $sortDirection = 'desc',
        ?string $cnes = null,
        ?string $facilityName = null,
        ?string $ine = null,
        ?string $teamName = null,
        ?float $minScore = null,
        ?float $maxScore = null
    ): Collection {
        $teams = $this->getEvaluations($year, $quarter);

        $matches = static fn (?string $value, ?string $filter): bool => $filter === null
            || trim($filter) === ''
            || str_contains(mb_strtolower((string) $value), mb_strtolower(trim($filter)));

        $teams = $teams->filter(function (CvatTeamEvaluation $team) use (
            $cnes,
            $facilityName,
            $ine,
            $teamName,
            $search,
            $classificationFilter,
            $minScore,
            $maxScore,
            $matches
        ): bool {
            if (! $matches($team->cnes, $cnes)
                || ! $matches($team->facility_name, $facilityName)
                || ! $matches($team->ine, $ine)
                || ! $matches($team->team_name, $teamName)) {
                return false;
            }

            if ($search && ! collect([$team->team_name, $team->facility_name, $team->ine, $team->cnes])
                ->contains(fn (?string $value): bool => $matches($value, $search))) {
                return false;
            }

            if ($classificationFilter && $classificationFilter !== 'ALL'
                && mb_strtoupper($team->final_classification) !== mb_strtoupper($classificationFilter)) {
                return false;
            }

            if ($minScore !== null && $team->final_score < $minScore) {
                return false;
            }

            return $maxScore === null || $team->final_score <= $maxScore;
        });

        $allowedSorts = [
            'team_name',
            'facility_name',
            'ine',
            'cnes',
            'linked_registrations',
            'linked_ratio',
            'registration_result',
            'registration_score',
            'monitoring_result',
            'monitoring_score',
            'final_score',
            'final_classification',
        ];

        if (in_array($sortBy, $allowedSorts, true)) {
            $teams = $teams->sortBy(
                fn (CvatTeamEvaluation $team): mixed => $team->{$sortBy},
                SORT_REGULAR,
                $sortDirection !== 'asc'
            );
        } else {
            $teams = $teams->sortByDesc('final_score');
        }

        return $teams->values();
    }

    /**
     * Resumo mensal calculado exclusivamente a partir da relação nominal armazenada no MySQL.
     *
     * @return array{
     *     has_data: bool,
     *     month_label: ?string,
     *     last_attendance_date: ?string,
     *     total_teams: int,
     *     optimal: int,
     *     optimal_pct: float,
     *     good: int,
     *     good_pct: float,
     *     sufficient: int,
     *     sufficient_pct: float,
     *     regular: int,
     *     regular_pct: float
     * }
     */
    public function getMonthlyTeamSummary(int $year, int $quarter): array
    {
        $teams = $this->getEvaluations($year, $quarter);
        $total = $teams->count();

        if ($total === 0) {
            return [
                'has_data' => false,
                'month_label' => null,
                'last_attendance_date' => null,
                'total_teams' => 0,
                'optimal' => 0,
                'optimal_pct' => 0.0,
                'good' => 0,
                'good_pct' => 0.0,
                'sufficient' => 0,
                'sufficient_pct' => 0.0,
                'regular' => 0,
                'regular_pct' => 0.0,
            ];
        }

        $period = $this->getLatestNominalPeriod($year, $quarter) ?? [
            'year' => $year,
            'month' => min(12, $quarter * 4),
            'quarter' => $quarter,
            'last_attendance_date' => null,
        ];

        $countClassification = static fn (string $classification): int => $teams
            ->filter(fn (CvatTeamEvaluation $team): bool => mb_strtoupper($team->final_classification) === $classification)
            ->count();

        $optimal = $teams->filter(fn (CvatTeamEvaluation $team): bool => in_array(mb_strtoupper($team->final_classification), ['ÓTIMO', 'OTIMO'], true))->count();
        $good = $countClassification('BOM');
        $sufficient = $countClassification('SUFICIENTE');
        $regular = $countClassification('REGULAR');

        return [
            'has_data' => true,
            'month_label' => $period['year'].' / M'.$period['month'],
            'last_attendance_date' => $period['last_attendance_date']
                ? Carbon::parse($period['last_attendance_date'])->format('d/m/Y')
                : null,
            'total_teams' => $total,
            'optimal' => $optimal,
            'optimal_pct' => round(($optimal / $total) * 100, 2),
            'good' => $good,
            'good_pct' => round(($good / $total) * 100, 2),
            'sufficient' => $sufficient,
            'sufficient_pct' => round(($sufficient / $total) * 100, 2),
            'regular' => $regular,
            'regular_pct' => round(($regular / $total) * 100, 2),
        ];
    }

    /**
     * Retorna somente INEs homologados como eSF (70) ou eAP (76).
     * O XML CNES é a fonte principal; snapshots MySQL válidos são usados quando o arquivo não está disponível.
     *
     * @return array<string, array{type: string, name: string, cnes: string}>
     */
    private function eligiblePrimaryCareTeams(): array
    {
        if ($this->eligiblePrimaryCareTeams !== null) {
            return $this->eligiblePrimaryCareTeams;
        }

        $teams = app(CnesXmlParserService::class)->getEligibleC1Teams();

        if ($teams === [] && Schema::hasTable('family_health_indicator_snapshots')) {
            $latestSnapshot = FamilyHealthIndicatorSnapshot::query()
                ->whereIn('team_type', ['70', '76'])
                ->whereNotNull('ine')
                ->where('ine', '!=', '')
                ->orderByDesc('year')
                ->orderByDesc('quarter')
                ->first(['year', 'quarter']);

            if ($latestSnapshot) {
                $teams = FamilyHealthIndicatorSnapshot::query()
                    ->where('year', $latestSnapshot->year)
                    ->where('quarter', $latestSnapshot->quarter)
                    ->whereIn('team_type', ['70', '76'])
                    ->whereNotNull('ine')
                    ->where('ine', '!=', '')
                    ->get(['ine', 'team_name', 'team_type'])
                    ->map(fn (FamilyHealthIndicatorSnapshot $snapshot): array => [
                        'ine' => (string) $snapshot->ine,
                        'name' => (string) $snapshot->team_name,
                        'type' => (string) $snapshot->team_type,
                        'cnes' => '',
                    ])
                    ->all();
            }
        }

        return $this->eligiblePrimaryCareTeams = collect($teams)
            ->filter(fn (array $team): bool => in_array((string) ($team['type'] ?? ''), ['70', '76'], true)
                && filled($team['ine'] ?? null))
            ->unique('ine')
            ->mapWithKeys(fn (array $team): array => [
                (string) $team['ine'] => [
                    'type' => (string) $team['type'],
                    'name' => (string) ($team['name'] ?? ''),
                    'cnes' => (string) ($team['cnes'] ?? ''),
                ],
            ])
            ->all();
    }
}
