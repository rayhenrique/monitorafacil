<?php

namespace App\Services;

use App\Models\C6NominalElderly;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class C6ActiveSearchService
{
    /**
     * Verifica se há registros reais na base local para o quadrimestre do C6.
     */
    public static function isRealDataAvailable(int $year, int $quarter): bool
    {
        if (! Schema::hasTable('c6_nominal_elderly')) {
            return false;
        }

        return C6NominalElderly::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->exists();
    }

    /**
     * Retorna a quantidade de pessoas idosas reais gravadas.
     */
    public static function getRealElderlyCount(int $year, int $quarter, ?string $ine = null): int
    {
        return self::getRealEldersCount($year, $quarter, $ine);
    }

    /**
     * Retorna a quantidade de pessoas idosas reais gravadas (alias).
     */
    public static function getRealEldersCount(int $year, int $quarter, ?string $ine = null): int
    {
        if (! Schema::hasTable('c6_nominal_elderly')) {
            return 0;
        }

        $q = C6NominalElderly::query()
            ->where('year', $year)
            ->where('quarter', $quarter);

        if ($ine) {
            $q->where('ine', $ine);
        }

        return $q->count();
    }

    /**
     * Colunas disponíveis para a tabela de busca ativa do C6.
     *
     * @return array<string, string>
     */
    public static function getAvailableColumns(): array
    {
        return [
            'cns' => 'CNS',
            'cpf' => 'CPF',
            'birth_date' => 'Nascimento',
            'name' => 'Nome',
            'age_years' => 'Idade',
            'race_color' => 'Raça/Cor',
            'cnes' => 'Unidade',
            'team' => 'Equipe',
            'microarea' => 'Micro Área',
            'month_ref' => 'Mês',
            'mici' => 'MCI Atualizada',
            'good_practices' => 'Boas Práticas (A-D)',
        ];
    }

    /**
     * Colunas selecionadas por padrão no C6.
     *
     * @return list<string>
     */
    public static function getDefaultVisibleColumns(): array
    {
        return array_keys(self::getAvailableColumns());
    }

    /**
     * Cache em memória no escopo da requisição.
     *
     * @var array<string, Collection>
     */
    protected array $cohortCache = [];

    /**
     * Lista base de pessoas idosas da coorte. Consulta exclusivamente c6_nominal_elderly.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getBaseCohort(int $year, int $quarter, ?string $selectedIne = null): Collection
    {
        $cacheKey = "{$year}_{$quarter}_" . ($selectedIne ?? 'all');
        if (isset($this->cohortCache[$cacheKey])) {
            return $this->cohortCache[$cacheKey];
        }

        if (! Schema::hasTable('c6_nominal_elderly')) {
            return collect();
        }

        $records = DB::table('c6_nominal_elderly')
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->when($selectedIne, fn ($q) => $q->where('ine', $selectedIne))
            ->orderByDesc('score_percent')
            ->orderBy('name')
            ->get();

        if ($records->isEmpty()) {
            return collect();
        }

        $mapped = $records->map(fn ($d) => $this->formatElderlyRecord($d));
        $this->cohortCache[$cacheKey] = $mapped;

        return $mapped;
    }

    /**
     * Busca um único idoso por ID diretamente, sem carregar toda a coorte na memória.
     */
    public function getElderlyById(int $id): ?array
    {
        $d = DB::table('c6_nominal_elderly')->where('id', $id)->first();
        if (! $d) {
            return null;
        }

        return $this->formatElderlyRecord($d);
    }

    /**
     * Formata os campos de um registro de idoso em array para a UI.
     *
     * @param  C6NominalElderly|\stdClass|array  $d
     * @return array<string, mixed>
     */
    public function formatElderlyRecord(mixed $d): array
    {
        if (is_array($d)) {
            $d = (object) $d;
        }

        $formatDate = function ($date) {
            if (empty($date) || $date === '—') {
                return '—';
            }
            if ($date instanceof \Carbon\CarbonInterface) {
                return $date->format('d/m/Y');
            }
            $str = (string) $date;
            $ts = strtotime($str);
            return $ts !== false ? date('d/m/Y', $ts) : '—';
        };

        $birthDateFormatted = $formatDate($d->birth_date ?? null);
        $birthDateString = ! empty($d->birth_date) ? substr((string) $d->birth_date, 0, 10) : null;
        $cns = trim((string) ($d->cns ?? ''));
        $cpf = trim((string) ($d->cpf ?? ''));

        return [
            'id' => (int) $d->id,
            'year' => (int) $d->year,
            'quarter' => (int) $d->quarter,
            'cidadao_pec_id' => (int) ($d->cidadao_pec_id ?? 0),
            'cns' => $cns,
            'cpf' => $cpf,
            'cns_masked' => $cns !== '' ? substr($cns, 0, 3) . ' **** **** ' . substr($cns, -4) : '—',
            'cpf_masked' => $cpf !== '' ? substr($cpf, 0, 3) . '.***.***-' . substr($cpf, -2) : '—',
            'name' => $d->name,
            'social_name' => $d->social_name ?? null,
            'birth_date' => $birthDateString,
            'birth_date_formatted' => $birthDateFormatted,
            'age_years' => (int) ($d->age_years ?? 0),
            'phone' => $d->phone ?? null,
            'race_color' => ($d->race_color ?? null) ?: 'Não informada',
            'cnes' => $d->cnes ?? null,
            'facility_name' => $d->facility_name ?? null,
            'district' => $d->district ?? null,
            'ine' => $d->ine ?? null,
            'team_name' => $d->team_name ?? null,
            'team_type' => $d->team_type ?? '70',
            'microarea' => ($d->microarea ?? null) ?: '—',
            'month_ref' => $d->month_ref ?? null,
            'mici_updated' => (bool) ($d->mici_updated ?? false),
            'is_accompanied' => (bool) ($d->is_accompanied ?? false),
            // Práticas
            'practice_a' => (int) ($d->practice_a ?? 0),
            'practice_a_met' => (bool) ($d->practice_a_met ?? false),
            'last_consultation_date' => $formatDate($d->last_consultation_date ?? null),
            'practice_b' => (int) ($d->practice_b ?? 0),
            'practice_b_met' => (bool) ($d->practice_b_met ?? false),
            'last_anthropometry_date' => $formatDate($d->last_anthropometry_date ?? null),
            'last_weight' => ! empty($d->last_weight) ? number_format((float) $d->last_weight, 1, ',', '.') . ' kg' : '—',
            'last_height' => ! empty($d->last_height) ? number_format((float) $d->last_height, 1, ',', '.') . ' cm' : '—',
            'practice_c' => (int) ($d->practice_c ?? 0),
            'practice_c_met' => (bool) ($d->practice_c_met ?? false),
            'last_visit_date' => $formatDate($d->last_visit_date ?? null),
            'practice_d' => (int) ($d->practice_d ?? 0),
            'practice_d_met' => (bool) ($d->practice_d_met ?? false),
            'last_vaccine_date' => $formatDate($d->last_vaccine_date ?? null),
            'last_vaccine_name' => ($d->last_vaccine_name ?? null) ?: 'Vacina influenza trivalente',
            'score_percent' => (float) ($d->score_percent ?? 0.0),
            'performance_level' => FamilyHealthService::calculatePerformanceLevel('c6', (float) ($d->score_percent ?? 0.0)),
        ];
    }

    /**
     * Aplica filtros de busca sobre a coleção de idosos.
     *
     * @param  Collection<int, array<string, mixed>>  $cohort
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function filterCohort(Collection $cohort, array $filters): Collection
    {
        return $cohort->filter(function (array $d) use ($filters) {
            // Busca textual rápida (Barra superior)
            if (! empty($filters['searchCns']) && ! str_contains((string) $d['cns'], (string) $filters['searchCns'])) {
                return false;
            }
            if (! empty($filters['searchCpf']) && ! str_contains((string) $d['cpf'], (string) $filters['searchCpf'])) {
                return false;
            }
            if (! empty($filters['searchName']) && ! str_contains(mb_strtolower((string) $d['name']), mb_strtolower((string) $filters['searchName']))) {
                return false;
            }
            if (! empty($filters['searchCnes']) && (string) $d['cnes'] !== (string) $filters['searchCnes']) {
                return false;
            }
            if (! empty($filters['searchIne']) && (string) $d['ine'] !== (string) $filters['searchIne']) {
                return false;
            }

            // Fallback genérico para 'search'
            if (! empty($filters['search'])) {
                $term = mb_strtolower(trim((string) $filters['search']));
                $name = mb_strtolower((string) $d['name']);
                $cpf = preg_replace('/\D+/', '', (string) $d['cpf']);
                $cns = preg_replace('/\D+/', '', (string) $d['cns']);
                $termClean = preg_replace('/\D+/', '', $term);

                $matchesName = str_contains($name, $term);
                $matchesCpf = $termClean !== '' && str_contains($cpf, $termClean);
                $matchesCns = $termClean !== '' && str_contains($cns, $termClean);

                if (! $matchesName && ! $matchesCpf && ! $matchesCns) {
                    return false;
                }
            }

            // Filtros Avançados (Modal)
            if (! empty($filters['advDistrict']) && (string) $d['district'] !== (string) $filters['advDistrict']) {
                return false;
            }
            if (! empty($filters['advFacility']) && (string) $d['cnes'] !== (string) $filters['advFacility']) {
                return false;
            }
            if (! empty($filters['advTeam']) && (string) $d['ine'] !== (string) $filters['advTeam']) {
                return false;
            }
            if (! empty($filters['advMicroarea']) && (string) $d['microarea'] !== (string) $filters['advMicroarea']) {
                return false;
            }
            if (! empty($filters['advCitizenName']) && ! str_contains(mb_strtolower((string) $d['name']), mb_strtolower((string) $filters['advCitizenName']))) {
                return false;
            }
            if (! empty($filters['advCitizenCpf']) && ! str_contains((string) $d['cpf'], (string) $filters['advCitizenCpf'])) {
                return false;
            }
            if (! empty($filters['advCitizenCns']) && ! str_contains((string) $d['cns'], (string) $filters['advCitizenCns'])) {
                return false;
            }
            if (! empty($filters['advRaceColor']) && (string) $d['race_color'] !== (string) $filters['advRaceColor']) {
                return false;
            }

            // Filtro por Faixa Etária
            $ageFilter = $filters['advElderlyAgeRange'] ?? $filters['age_range'] ?? null;
            if (! empty($ageFilter)) {
                $age = (int) $d['age_years'];
                $match = match ($ageFilter) {
                    '60-69', '60_69' => $age >= 60 && $age <= 69,
                    '70-79', '70_79' => $age >= 70 && $age <= 79,
                    '80+', '80_plus' => $age >= 80,
                    default => true,
                };
                if (! $match) {
                    return false;
                }
            }

            // Filtro por Nível de Desempenho
            if (! empty($filters['performance_level']) && $d['performance_level'] !== $filters['performance_level']) {
                return false;
            }

            // Filtros de Boas Práticas (A a D)
            $practiceKeys = [
                'advPracticeA' => 'practice_a_met',
                'advPracticeB' => 'practice_b_met',
                'advPracticeC' => 'practice_c_met',
                'advPracticeD' => 'practice_d_met',
                'practice_a' => 'practice_a_met',
                'practice_b' => 'practice_b_met',
                'practice_c' => 'practice_c_met',
                'practice_d' => 'practice_d_met',
            ];

            foreach ($practiceKeys as $filterKey => $metKey) {
                if (isset($filters[$filterKey]) && $filters[$filterKey] !== null && $filters[$filterKey] !== '') {
                    $val = $filters[$filterKey];
                    if ($val === 'cumprida' || $val === 'sim' || $val === true || $val === '1' || $val === 1 || $val === 'met') {
                        if (! $d[$metKey]) {
                            return false;
                        }
                    } elseif ($val === 'pendente' || $val === 'nao' || $val === false || $val === '0' || $val === 0 || $val === 'pending') {
                        if ($d[$metKey]) {
                            return false;
                        }
                    }
                }
            }

            return true;
        })->values();
    }

    /**
     * Retorna os KPIs e métricas consolidadas dos idosos filtrados.
     *
     * @param  Collection<int, array<string, mixed>>  $cohort
     * @return array<string, mixed>
     */
    public function getSummaryKpis(Collection $cohort, int $year, int $quarter): array
    {
        $total = $cohort->count();
        if ($total === 0) {
            return [
                'total_elderly' => 0,
                'denominator' => 0,
                'period_label' => sprintf('%d / Q%d', $year, $quarter),
                'period_sublabel' => 'Avaliação Quadrimestral · Componente III',
                'avg_score' => 0.0,
                'practice_a_pct' => 0.0,
                'practice_b_pct' => 0.0,
                'practice_c_pct' => 0.0,
                'practice_d_pct' => 0.0,
                'practice_a_count' => 0,
                'practice_b_count' => 0,
                'practice_c_count' => 0,
                'practice_d_count' => 0,
                'practice_a' => ['count' => 0, 'percent' => 0.0],
                'practice_b' => ['count' => 0, 'percent' => 0.0],
                'practice_c' => ['count' => 0, 'percent' => 0.0],
                'practice_d' => ['count' => 0, 'percent' => 0.0],
                'regular_count' => 0,
                'sufficient_count' => 0,
                'good_count' => 0,
                'optimal_count' => 0,
            ];
        }

        $cntA = $cohort->where('practice_a_met', true)->count();
        $cntB = $cohort->where('practice_b_met', true)->count();
        $cntC = $cohort->where('practice_c_met', true)->count();
        $cntD = $cohort->where('practice_d_met', true)->count();

        $avgScore = round((float) $cohort->avg('score_percent'), 2);
        $pctA = round(($cntA / $total) * 100, 1);
        $pctB = round(($cntB / $total) * 100, 1);
        $pctC = round(($cntC / $total) * 100, 1);
        $pctD = round(($cntD / $total) * 100, 1);

        return [
            'total_elderly' => $total,
            'denominator' => $total,
            'period_label' => sprintf('%d / Q%d', $year, $quarter),
            'period_sublabel' => 'Avaliação Quadrimestral · Componente III',
            'avg_score' => $avgScore,
            'practice_a_pct' => $pctA,
            'practice_b_pct' => $pctB,
            'practice_c_pct' => $pctC,
            'practice_d_pct' => $pctD,
            'practice_a_count' => $cntA,
            'practice_b_count' => $cntB,
            'practice_c_count' => $cntC,
            'practice_d_count' => $cntD,
            'practice_a' => ['count' => $cntA, 'percent' => $pctA],
            'practice_b' => ['count' => $cntB, 'percent' => $pctB],
            'practice_c' => ['count' => $cntC, 'percent' => $pctC],
            'practice_d' => ['count' => $cntD, 'percent' => $pctD],
            'regular_count' => $cohort->where('performance_level', 'regular')->count(),
            'sufficient_count' => $cohort->where('performance_level', 'suficiente')->count(),
            'good_count' => $cohort->where('performance_level', 'bom')->count(),
            'optimal_count' => $cohort->where('performance_level', 'otimo')->count(),
        ];
    }

    /**
     * Retorna as opções de filtro para dropdowns na tela.
     *
     * @return array<string, mixed>
     */
    public function getFilterOptions(int $year, int $quarter): array
    {
        if (! Schema::hasTable('c6_nominal_elderly')) {
            return [
                'facilities' => [],
                'teams' => [],
                'microareas' => [],
                'races' => [],
            ];
        }

        $facilities = C6NominalElderly::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNotNull('cnes')
            ->select('cnes', 'facility_name')
            ->distinct()
            ->orderBy('facility_name')
            ->get()
            ->map(fn ($f) => [
                'cnes' => $f->cnes,
                'facility_name' => $f->facility_name,
                'name' => $f->facility_name ?: "CNES {$f->cnes}",
            ])
            ->toArray();

        $teams = C6NominalElderly::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNotNull('ine')
            ->select('ine', 'team_name')
            ->distinct()
            ->orderBy('team_name')
            ->get()
            ->map(fn ($t) => [
                'ine' => $t->ine,
                'team_name' => $t->team_name,
                'name' => $t->team_name ?: "INE {$t->ine}",
            ])
            ->toArray();

        $microareas = C6NominalElderly::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNotNull('microarea')
            ->where('microarea', '!=', '')
            ->where('microarea', '!=', '—')
            ->select('microarea')
            ->distinct()
            ->orderBy('microarea')
            ->pluck('microarea')
            ->toArray();

        $races = C6NominalElderly::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNotNull('race_color')
            ->where('race_color', '!=', '')
            ->select('race_color')
            ->distinct()
            ->orderBy('race_color')
            ->pluck('race_color')
            ->toArray();

        return [
            'facilities' => $facilities,
            'teams' => $teams,
            'microareas' => $microareas,
            'races' => $races,
        ];
    }

    /**
     * Exporta a lista nominal em CSV formatado em UTF-8 com BOM para Excel.
     *
     * @param  Collection<int, array<string, mixed>>  $cohort
     */
    public function exportCsv(Collection $cohort): StreamedResponse
    {
        $filename = 'lista_nominal_c6_idosos_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return new StreamedResponse(function () use ($cohort) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para correta interpretação de acentos no Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeçalho CSV
            fputcsv($handle, [
                'ID Cidadão',
                'Nome do Idoso',
                'CPF',
                'CNS',
                'Data de Nascimento',
                'Idade (anos)',
                'Telefone',
                'Raça/Cor',
                'Unidade de Saúde',
                'Equipe',
                'INE',
                'Microárea',
                'Prática A (Consulta Médica/Enf 12m)',
                'Última Consulta',
                'Prática B (Antropometria 12m)',
                'Última Antropometria',
                'Último Peso',
                'Última Altura',
                'Prática C (2 Visitas ACS 12m)',
                'Última Visita',
                'Prática D (Vacina Influenza 12m)',
                'Última Vacina',
                'Pontuação Geral (%)',
                'Nível de Desempenho',
            ], ';');

            foreach ($cohort as $d) {
                fputcsv($handle, [
                    $d['cidadao_pec_id'],
                    $d['name'],
                    $d['cpf'],
                    $d['cns'],
                    $d['birth_date_formatted'],
                    $d['age_years'],
                    $d['phone'] ?? '',
                    $d['race_color'] ?? '',
                    $d['facility_name'] ?? '',
                    $d['team_name'] ?? '',
                    $d['ine'] ?? '',
                    $d['microarea'] ?? '',
                    $d['practice_a_met'] ? 'Sim (Cumprida)' : 'Não (Pendente)',
                    $d['last_consultation_date'] ?? '—',
                    $d['practice_b_met'] ? 'Sim (Cumprida)' : 'Não (Pendente)',
                    $d['last_anthropometry_date'] ?? '—',
                    $d['last_weight'] ?? '—',
                    $d['last_height'] ?? '—',
                    $d['practice_c_met'] ? 'Sim (Cumprida)' : 'Não (Pendente)',
                    $d['last_visit_date'] ?? '—',
                    $d['practice_d_met'] ? 'Sim (Cumprida)' : 'Não (Pendente)',
                    $d['last_vaccine_date'] ?? '—',
                    number_format((float) $d['score_percent'], 1, ',', '.'),
                    ucfirst((string) $d['performance_level']),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
