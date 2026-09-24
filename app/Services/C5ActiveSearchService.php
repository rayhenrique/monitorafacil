<?php

namespace App\Services;

use App\Models\C5NominalHypertensive;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class C5ActiveSearchService
{
    /**
     * Verifica se há registros reais na base local para o quadrimestre do C5.
     */
    public static function isRealDataAvailable(int $year, int $quarter): bool
    {
        if (! Schema::hasTable('c5_nominal_hypertensives')) {
            return false;
        }

        return C5NominalHypertensive::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->exists();
    }

    /**
     * Retorna a quantidade de hipertensos reais gravados.
     */
    public static function getRealHypertensivesCount(int $year, int $quarter, ?string $ine = null): int
    {
        if (! Schema::hasTable('c5_nominal_hypertensives')) {
            return 0;
        }

        $q = C5NominalHypertensive::query()
            ->where('year', $year)
            ->where('quarter', $quarter);

        if ($ine) {
            $q->where('ine', $ine);
        }

        return $q->count();
    }

    /**
     * Colunas disponíveis para a tabela de busca ativa do C5.
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
     * Colunas selecionadas por padrão no C5.
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
     * Lista base de hipertensos da coorte. Consulta exclusivamente c5_nominal_hypertensives.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getBaseCohort(int $year, int $quarter, ?string $selectedIne = null): Collection
    {
        $cacheKey = "{$year}_{$quarter}_" . ($selectedIne ?? 'all');
        if (isset($this->cohortCache[$cacheKey])) {
            return $this->cohortCache[$cacheKey];
        }

        if (! Schema::hasTable('c5_nominal_hypertensives')) {
            return collect();
        }

        $records = DB::table('c5_nominal_hypertensives')
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->when($selectedIne, fn ($q) => $q->where('ine', $selectedIne))
            ->orderByDesc('score_percent')
            ->orderBy('name')
            ->get();

        if ($records->isEmpty()) {
            return collect();
        }

        $mapped = $records->map(fn ($d) => $this->formatHypertensiveRecord($d));
        $this->cohortCache[$cacheKey] = $mapped;

        return $mapped;
    }

    /**
     * Busca um único hipertenso por ID diretamente, sem carregar toda a coorte na memória.
     */
    public function getHypertensiveById(int $id): ?array
    {
        $d = DB::table('c5_nominal_hypertensives')->where('id', $id)->first();
        if (! $d) {
            return null;
        }

        return $this->formatHypertensiveRecord($d);
    }

    /**
     * Formata os campos de um registro de hipertenso em array para a UI.
     *
     * @param  C5NominalHypertensive|\stdClass|array  $d
     * @return array<string, mixed>
     */
    public function formatHypertensiveRecord(mixed $d): array
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
            'cidadao_pec_id' => (int) $d->cidadao_pec_id,
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
            'microarea' => ($d->microarea ?? null) ?: '—',
            'condition_status' => $d->condition_status ?? 'Ativo',
            'ciap_codes' => $d->ciap_codes ?? null,
            'cid_codes' => $d->cid_codes ?? null,
            'first_diagnosis_date' => $formatDate($d->first_diagnosis_date ?? null),
            'last_diagnosis_date' => $formatDate($d->last_diagnosis_date ?? null),
            'month_ref' => $d->month_ref ?? null,
            'mici_updated' => (bool) ($d->mici_updated ?? false),
            'is_accompanied' => (bool) ($d->is_accompanied ?? false),
            // Práticas
            'practice_a' => (int) ($d->practice_a ?? 0),
            'practice_a_met' => (bool) ($d->practice_a_met ?? false),
            'last_consultation_date' => $formatDate($d->last_consultation_date ?? null),
            'practice_b' => (int) ($d->practice_b ?? 0),
            'practice_b_met' => (bool) ($d->practice_b_met ?? false),
            'last_pa_date' => $formatDate($d->last_pa_date ?? null),
            'last_pa_value' => ($d->last_pa_value ?? null) ?: '—',
            'practice_c' => (int) ($d->practice_c ?? 0),
            'practice_c_met' => (bool) ($d->practice_c_met ?? false),
            'last_anthropometry_date' => $formatDate($d->last_anthropometry_date ?? null),
            'last_weight' => ! empty($d->last_weight) ? number_format((float) $d->last_weight, 1, ',', '.') . ' kg' : '—',
            'last_height' => ! empty($d->last_height) ? number_format((float) $d->last_height, 1, ',', '.') . ' cm' : '—',
            'practice_d' => (int) ($d->practice_d ?? 0),
            'practice_d_met' => (bool) ($d->practice_d_met ?? false),
            'last_visit_date' => $formatDate($d->last_visit_date ?? null),
            'score_percent' => (float) ($d->score_percent ?? 0.0),
            'performance_level' => FamilyHealthService::calculatePerformanceLevel('c5', (float) ($d->score_percent ?? 0.0)),
        ];
    }

    /**
     * Aplica filtros de busca sobre a coleção de hipertensos.
     *
     * @param  Collection<int, array<string, mixed>>  $cohort
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function filterCohort(Collection $cohort, array $filters): Collection
    {
        return $cohort->filter(function (array $d) use ($filters) {
            // Busca textual básica
            if (! empty($filters['searchCns']) && ! str_contains($d['cns'], $filters['searchCns'])) {
                return false;
            }
            if (! empty($filters['searchCpf']) && ! str_contains($d['cpf'], $filters['searchCpf'])) {
                return false;
            }
            if (! empty($filters['searchName']) && ! str_contains(mb_strtolower($d['name']), mb_strtolower($filters['searchName']))) {
                return false;
            }
            if (! empty($filters['searchCnes']) && $d['cnes'] !== $filters['searchCnes']) {
                return false;
            }
            if (! empty($filters['searchIne']) && $d['ine'] !== $filters['searchIne']) {
                return false;
            }

            // Filtros Avançados
            if (! empty($filters['advDistrict']) && $d['district'] !== $filters['advDistrict']) {
                return false;
            }
            if (! empty($filters['advFacility']) && $d['cnes'] !== $filters['advFacility']) {
                return false;
            }
            if (! empty($filters['advTeam']) && $d['ine'] !== $filters['advTeam']) {
                return false;
            }
            if (! empty($filters['advMicroarea']) && $d['microarea'] !== $filters['advMicroarea']) {
                return false;
            }
            if (! empty($filters['advCitizenName']) && ! str_contains(mb_strtolower($d['name']), mb_strtolower($filters['advCitizenName']))) {
                return false;
            }
            if (! empty($filters['advCitizenCpf']) && ! str_contains($d['cpf'], $filters['advCitizenCpf'])) {
                return false;
            }
            if (! empty($filters['advCitizenCns']) && ! str_contains($d['cns'], $filters['advCitizenCns'])) {
                return false;
            }
            if (! empty($filters['advRaceColor']) && $d['race_color'] !== $filters['advRaceColor']) {
                return false;
            }

            // Filtros de Boas Práticas (true/false)
            $practiceKeys = [
                'advPracticeA' => 'practice_a_met',
                'advPracticeB' => 'practice_b_met',
                'advPracticeC' => 'practice_c_met',
                'advPracticeD' => 'practice_d_met',
            ];

            foreach ($practiceKeys as $filterKey => $metKey) {
                if (isset($filters[$filterKey]) && $filters[$filterKey] !== null && $filters[$filterKey] !== '') {
                    $val = $filters[$filterKey];
                    $expected = ($val === 'sim' || $val === true || $val === '1' || $val === 1);
                    if ($d[$metKey] !== $expected) {
                        return false;
                    }
                }
            }

            return true;
        })->values();
    }

    /**
     * Retorna os KPIs e métricas consolidadas dos hipertensos filtrados.
     *
     * @param  Collection<int, array<string, mixed>>  $cohort
     * @return array<string, mixed>
     */
    public function getSummaryKpis(Collection $cohort, int $year, int $quarter): array
    {
        $total = $cohort->count();
        if ($total === 0) {
            return [
                'total_hypertensives' => 0,
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

        $avgScore = round($cohort->avg('score_percent'), 2);
        $pctA = round(($cntA / $total) * 100, 1);
        $pctB = round(($cntB / $total) * 100, 1);
        $pctC = round(($cntC / $total) * 100, 1);
        $pctD = round(($cntD / $total) * 100, 1);

        return [
            'total_hypertensives' => $total,
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
        if (! Schema::hasTable('c5_nominal_hypertensives')) {
            return [
                'facilities' => [],
                'teams' => [],
                'microareas' => [],
            ];
        }

        $facilities = C5NominalHypertensive::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNotNull('cnes')
            ->select('cnes', 'facility_name')
            ->distinct()
            ->orderBy('facility_name')
            ->get()
            ->map(fn ($f) => ['cnes' => $f->cnes, 'facility_name' => $f->facility_name])
            ->toArray();

        $teams = C5NominalHypertensive::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNotNull('ine')
            ->select('ine', 'team_name')
            ->distinct()
            ->orderBy('team_name')
            ->get()
            ->map(fn ($t) => ['ine' => $t->ine, 'team_name' => $t->team_name])
            ->toArray();

        $microareas = C5NominalHypertensive::query()
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

        return [
            'facilities' => $facilities,
            'teams' => $teams,
            'microareas' => $microareas,
        ];
    }

    /**
     * Exporta os cidadãos hipertensos em formato CSV.
     *
     * @param  Collection<int, array<string, mixed>>  $hypertensives
     */
    public function exportCsv(Collection $hypertensives): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="c5_busca_ativa_hipertensos_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($hypertensives): void {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'CNS', 'CPF', 'Nome', 'Nascimento', 'Idade', 'Raça/Cor',
                'CNES', 'Unidade', 'INE', 'Equipe', 'Microárea',
                '(A) Consulta Méd/Enf', 'Última Consulta',
                '(B) Aferição PA', 'Última PA', 'Valor PA',
                '(C) Antropometria', 'Última Antropo', 'Peso', 'Altura',
                '(D) Visitas ACS', 'Última Visita',
                'Pontuação (0-100)', 'Classificação',
            ], ';');

            foreach ($hypertensives as $d) {
                fputcsv($handle, [
                    $d['cns'],
                    $d['cpf'],
                    $d['name'],
                    $d['birth_date_formatted'],
                    $d['age_years'],
                    $d['race_color'],
                    $d['cnes'],
                    $d['facility_name'],
                    $d['ine'],
                    $d['team_name'],
                    $d['microarea'],
                    $d['practice_a_met'] ? 'SIM' : 'NÃO',
                    $d['last_consultation_date'],
                    $d['practice_b_met'] ? 'SIM' : 'NÃO',
                    $d['last_pa_date'],
                    $d['last_pa_value'],
                    $d['practice_c_met'] ? 'SIM' : 'NÃO',
                    $d['last_anthropometry_date'],
                    $d['last_weight'],
                    $d['last_height'],
                    $d['practice_d_met'] ? 'SIM' : 'NÃO',
                    $d['last_visit_date'],
                    number_format($d['score_percent'], 2, ',', '.'),
                    ucfirst($d['performance_level']),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
