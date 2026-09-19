<?php

namespace App\Services;

use App\Models\CvatDimensionDistribution;
use App\Models\CvatTeamEvaluation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CvatService
{
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
            $quarterLabel = 'Q' . $quarter . '/' . substr((string) $year, -2);
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
                    'registration_score' => $registrationScore,
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
        return [
            [
                'year' => 2026,
                'quarter' => 1,
                'label' => 'Q1/26 (2026)',
                'short_label' => 'Q1/26',
                'is_latest' => true,
                'has_team_details' => true,
            ],
            [
                'year' => 2025,
                'quarter' => 3,
                'label' => 'Q3/25 (2025)',
                'short_label' => 'Q3/25',
                'is_latest' => false,
                'has_team_details' => false,
            ],
            [
                'year' => 2025,
                'quarter' => 2,
                'label' => 'Q2/25 (2025)',
                'short_label' => 'Q2/25',
                'is_latest' => false,
                'has_team_details' => false,
            ],
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
        if (CvatTeamEvaluation::count() === 0) {
            $this->importTeamEvaluations();
        }

        $teams = CvatTeamEvaluation::where('year', $year)
            ->where('quarter', $quarter)
            ->get();

        if ($teams->isEmpty()) {
            // Fallback usando distribuições de dimensões se disponíveis
            $cadDist = CvatDimensionDistribution::where('year', $year)
                ->where('quarter', $quarter)
                ->where('dimension_code', 'cadastro')
                ->first();

            $acompDist = CvatDimensionDistribution::where('year', $year)
                ->where('quarter', $quarter)
                ->where('dimension_code', 'acompanhamento')
                ->first();

            if (! $cadDist && ! $acompDist) {
                return [
                    'has_data' => false,
                    'average_final_score' => 0.0,
                    'municipal_classification' => 'SEM DADOS',
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

            return [
                'has_data' => true,
                'average_final_score' => 0.0,
                'municipal_classification' => 'CONSOLIDADO DIMENSÕES',
                'average_registration' => 0.0,
                'average_monitoring' => 0.0,
                'total_teams' => $cadDist ? $cadDist->total_teams : 19,
                'optimal_count' => $cadDist ? $cadDist->optimal_count : 0,
                'good_count' => $cadDist ? $cadDist->good_count : 0,
                'sufficient_count' => $cadDist ? $cadDist->sufficient_count : 0,
                'regular_count' => $cadDist ? $cadDist->regular_count : 0,
                'financial_incentive' => 'Conforme Quadro 5',
            ];
        }

        $totalTeams = $teams->count();
        $avgFinal = round($teams->avg('final_score') ?? 0.0, 2);
        $avgReg = round($teams->avg('registration_score') ?? 0.0, 2);
        $avgMon = round($teams->avg('monitoring_score') ?? 0.0, 2);

        $optimal = $teams->filter(fn ($t) => mb_strtoupper($t->final_classification) === 'ÓTIMO' || mb_strtoupper($t->final_classification) === 'OTIMO')->count();
        $good = $teams->filter(fn ($t) => mb_strtoupper($t->final_classification) === 'BOM')->count();
        $sufficient = $teams->filter(fn ($t) => mb_strtoupper($t->final_classification) === 'SUFICIENTE')->count();
        $regular = $teams->filter(fn ($t) => mb_strtoupper($t->final_classification) === 'REGULAR')->count();

        // Classificação do Município conforme Nota Técnica nº 08/2026 - Quadro 5
        // > 8,5: Ótimo | >= 7 e <= 8,5: Bom | >= 5 e < 7: Suficiente | < 5: Regular
        $classification = match (true) {
            $avgFinal > 8.5 => 'ÓTIMO',
            $avgFinal >= 7.0 => 'BOM',
            $avgFinal >= 5.0 => 'SUFICIENTE',
            default => 'REGULAR',
        };

        $financialIncentive = match ($classification) {
            'ÓTIMO' => 'Incentivo Máximo (100% repasse)',
            'BOM' => 'Incentivo Bom (75% repasse)',
            'SUFICIENTE' => 'Incentivo Suficiente (50% repasse)',
            default => 'Incentivo Regular (25% repasse)',
        };

        return [
            'has_data' => true,
            'average_final_score' => $avgFinal,
            'municipal_classification' => $classification,
            'average_registration' => $avgReg,
            'average_monitoring' => $avgMon,
            'total_teams' => $totalTeams,
            'optimal_count' => $optimal,
            'good_count' => $good,
            'sufficient_count' => $sufficient,
            'regular_count' => $regular,
            'financial_incentive' => $financialIncentive,
        ];
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
        string $sortDirection = 'desc'
    ): Collection {
        if (CvatTeamEvaluation::count() === 0) {
            $this->importTeamEvaluations();
        }

        $query = CvatTeamEvaluation::where('year', $year)
            ->where('quarter', $quarter);

        if ($search) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('team_name', 'like', "%{$search}%")
                    ->orWhere('facility_name', 'like', "%{$search}%")
                    ->orWhere('ine', 'like', "%{$search}%")
                    ->orWhere('cnes', 'like', "%{$search}%");
            });
        }

        if ($classificationFilter && $classificationFilter !== 'ALL') {
            $query->where('final_classification', $classificationFilter);
        }

        $allowedSorts = ['team_name', 'facility_name', 'ine', 'registration_score', 'monitoring_score', 'final_score', 'final_classification'];
        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('final_score', 'desc');
        }

        return $query->get();
    }
}
