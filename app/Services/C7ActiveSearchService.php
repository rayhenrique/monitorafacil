<?php

namespace App\Services;

use App\Models\C7NominalWoman;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class C7ActiveSearchService
{
    /**
     * Verifica se há registros reais na base local para o quadrimestre do C7.
     */
    public static function isRealDataAvailable(int $year, int $quarter): bool
    {
        if (! Schema::hasTable('c7_nominal_women')) {
            return false;
        }

        return C7NominalWoman::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->exists();
    }

    /**
     * Retorna a quantidade de mulheres da coorte gravadas.
     */
    public static function getRealWomenCount(int $year, int $quarter, ?string $ine = null): int
    {
        if (! Schema::hasTable('c7_nominal_women')) {
            return 0;
        }

        $q = C7NominalWoman::query()
            ->where('year', $year)
            ->where('quarter', $quarter);

        if ($ine) {
            $q->where('ine', $ine);
        }

        return $q->count();
    }

    /**
     * Colunas disponíveis para a tabela de busca ativa do C7.
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
            'gender' => 'Identidade/Gênero',
            'race_color' => 'Raça/Cor',
            'cnes' => 'Unidade',
            'team' => 'Equipe',
            'microarea' => 'Micro Área',
            'practice_a' => 'Colo do Útero (A)',
            'practice_b' => 'Vacina HPV (B)',
            'practice_c' => 'Saúde Sexual (C)',
            'practice_d' => 'Mama (D)',
            'score' => 'Pontuação',
        ];
    }

    /**
     * Colunas selecionadas por padrão no C7.
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
     * Lista base de mulheres da coorte. Consulta exclusivamente c7_nominal_women.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getBaseCohort(int $year, int $quarter, ?string $selectedIne = null): Collection
    {
        $cacheKey = "{$year}_{$quarter}_" . ($selectedIne ?? 'all');
        if (isset($this->cohortCache[$cacheKey])) {
            return $this->cohortCache[$cacheKey];
        }

        if (! Schema::hasTable('c7_nominal_women')) {
            return collect();
        }

        $records = DB::table('c7_nominal_women')
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->when($selectedIne, fn ($q) => $q->where('ine', $selectedIne))
            ->orderByDesc('score_percent')
            ->orderBy('name')
            ->get();

        if ($records->isEmpty()) {
            return collect();
        }

        $mapped = $records->map(fn ($d) => $this->formatWomanRecord($d));
        $this->cohortCache[$cacheKey] = $mapped;

        return $mapped;
    }

    /**
     * Busca uma mulher por ID diretamente para o modal de auditoria clínica.
     */
    public function getWomanById(int $id): ?array
    {
        $d = DB::table('c7_nominal_women')->where('id', $id)->first();
        if (! $d) {
            return null;
        }

        return $this->formatWomanRecord($d);
    }

    /**
     * Formata os campos de um registro em array para a UI.
     *
     * @param  C7NominalWoman|\stdClass|array  $d
     * @return array<string, mixed>
     */
    public function formatWomanRecord(mixed $d): array
    {
        if (is_array($d)) {
            $d = (object) $d;
        }

        $formatDate = function ($date) {
            if (empty($date) || $date === '—') {
                return null;
            }
            if ($date instanceof \Carbon\CarbonInterface) {
                return $date->format('d/m/Y');
            }
            $str = (string) $date;
            $ts = strtotime($str);
            return $ts !== false ? date('d/m/Y', $ts) : null;
        };

        $birthDateFormatted = $formatDate($d->birth_date ?? null) ?: '—';
        $birthDateString = ! empty($d->birth_date) ? substr((string) $d->birth_date, 0, 10) : null;
        $cns = trim((string) ($d->cns ?? ''));
        $cpf = trim((string) ($d->cpf ?? ''));

        $pendingRaw = $d->pending_practices ?? [];
        if (is_string($pendingRaw)) {
            $pending = json_decode($pendingRaw, true) ?: [];
        } else {
            $pending = is_array($pendingRaw) ? $pendingRaw : [];
        }

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
            'sex' => $d->sex ?? 'FEMININO',
            'gender_identity' => $d->gender_identity ?? 'MULHER_CIS',
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

            // Prática A: Colo do Útero (25 a 64 anos - 20 pts)
            'eligible_practice_a' => (bool) ($d->eligible_practice_a ?? false),
            'practice_a_met' => (bool) ($d->practice_a_met ?? false),
            'practice_a_count' => (int) ($d->practice_a_count ?? 0),
            'last_cervical_exam_date' => $formatDate($d->last_cervical_exam_date ?? null),
            'last_cervical_exam_code' => $d->last_cervical_exam_code ?? null,
            'last_cervical_exam_desc' => $d->last_cervical_exam_desc ?? null,

            // Prática B: Vacina HPV (9 a 14 anos - 30 pts)
            'eligible_practice_b' => (bool) ($d->eligible_practice_b ?? false),
            'practice_b_met' => (bool) ($d->practice_b_met ?? false),
            'practice_b_count' => (int) ($d->practice_b_count ?? 0),
            'last_hpv_vaccine_date' => $formatDate($d->last_hpv_vaccine_date ?? null),
            'last_hpv_vaccine_code' => $d->last_hpv_vaccine_code ?? null,
            'last_hpv_vaccine_name' => $d->last_hpv_vaccine_name ?? null,

            // Prática C: Saúde Sexual e Reprodutiva (14 a 69 anos - 30 pts)
            'eligible_practice_c' => (bool) ($d->eligible_practice_c ?? false),
            'practice_c_met' => (bool) ($d->practice_c_met ?? false),
            'practice_c_count' => (int) ($d->practice_c_count ?? 0),
            'last_sexual_health_date' => $formatDate($d->last_sexual_health_date ?? null),
            'last_sexual_health_code' => $d->last_sexual_health_code ?? null,
            'last_sexual_health_detail' => $d->last_sexual_health_detail ?? null,

            // Prática D: Câncer de Mama (50 a 69 anos - 20 pts)
            'eligible_practice_d' => (bool) ($d->eligible_practice_d ?? false),
            'practice_d_met' => (bool) ($d->practice_d_met ?? false),
            'practice_d_count' => (int) ($d->practice_d_count ?? 0),
            'last_mammogram_date' => $formatDate($d->last_mammogram_date ?? null),
            'last_mammogram_code' => $d->last_mammogram_code ?? null,
            'last_mammogram_desc' => $d->last_mammogram_desc ?? null,

            'score_percent' => (float) ($d->score_percent ?? 0.0),
            'pending_practices' => $pending,
            'performance_level' => FamilyHealthService::calculatePerformanceLevel('c7', (float) ($d->score_percent ?? 0.0)),
        ];
    }

    /**
     * Aplica filtros de busca sobre a coorte.
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

            // Filtros Avançados
            if (! empty($filters['advDistrict']) && (string) $d['district'] !== (string) $filters['advDistrict']) {
                return false;
            }
            if (! empty($filters['advFacility'])) {
                $fac = (string) $filters['advFacility'];
                if ((string) $d['cnes'] !== $fac && (string) ($d['facility_name'] ?? '') !== $fac) {
                    return false;
                }
            }
            if (! empty($filters['advTeam'])) {
                $tm = (string) $filters['advTeam'];
                if ((string) $d['ine'] !== $tm && (string) ($d['team_name'] ?? '') !== $tm) {
                    return false;
                }
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
            $ageFilter = $filters['advAgeRange'] ?? $filters['age_range'] ?? null;
            if (! empty($ageFilter)) {
                $age = (int) $d['age_years'];
                $match = match ($ageFilter) {
                    '9-14', '9_14' => $age >= 9 && $age <= 14,
                    '14-24', '14_24' => $age >= 14 && $age <= 24,
                    '25-49', '25_49' => $age >= 25 && $age <= 49,
                    '50-64', '50_64' => $age >= 50 && $age <= 64,
                    '65-69', '65_69' => $age >= 65 && $age <= 69,
                    '25-64', '25_64' => $age >= 25 && $age <= 64,
                    '50-69', '50_69' => $age >= 50 && $age <= 69,
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

            // Filtro por Situação Geral de Pendências
            $statusFilter = $filters['status_filter'] ?? null;
            if (! empty($statusFilter)) {
                $hasPending = count($d['pending_practices']) > 0;
                if ($statusFilter === 'em_dia' && $hasPending) {
                    return false;
                }
                if ($statusFilter === 'pendente' && ! $hasPending) {
                    return false;
                }
            }

            // Filtros de Boas Práticas (A a D)
            $practiceKeys = [
                'advPracticeA' => ['met' => 'practice_a_met', 'eligible' => 'eligible_practice_a'],
                'advPracticeB' => ['met' => 'practice_b_met', 'eligible' => 'eligible_practice_b'],
                'advPracticeC' => ['met' => 'practice_c_met', 'eligible' => 'eligible_practice_c'],
                'advPracticeD' => ['met' => 'practice_d_met', 'eligible' => 'eligible_practice_d'],
                'practice_a' => ['met' => 'practice_a_met', 'eligible' => 'eligible_practice_a'],
                'practice_b' => ['met' => 'practice_b_met', 'eligible' => 'eligible_practice_b'],
                'practice_c' => ['met' => 'practice_c_met', 'eligible' => 'eligible_practice_c'],
                'practice_d' => ['met' => 'practice_d_met', 'eligible' => 'eligible_practice_d'],
            ];

            foreach ($practiceKeys as $filterKey => $cfg) {
                if (isset($filters[$filterKey]) && $filters[$filterKey] !== null && $filters[$filterKey] !== '') {
                    $val = $filters[$filterKey];
                    $metKey = $cfg['met'];
                    $elKey = $cfg['eligible'];

                    if ($val === 'cumprida' || $val === 'sim' || $val === true || $val === '1' || $val === 1 || $val === 'met') {
                        if (! $d[$elKey] || ! $d[$metKey]) {
                            return false;
                        }
                    } elseif ($val === 'pendente' || $val === 'nao' || $val === false || $val === '0' || $val === 0 || $val === 'pending') {
                        // Deve ser elegível e estar pendente
                        if (! $d[$elKey] || $d[$metKey]) {
                            return false;
                        }
                    } elseif ($val === 'nao_elegivel') {
                        if ($d[$elKey]) {
                            return false;
                        }
                    }
                }
            }

            return true;
        })->values();
    }

    /**
     * Retorna os KPIs e métricas consolidadas das mulheres filtradas.
     *
     * @param  Collection<int, array<string, mixed>>  $cohort
     * @return array<string, mixed>
     */
    public function getSummaryKpis(Collection $cohort, int $year, int $quarter): array
    {
        $total = $cohort->count();
        if ($total === 0) {
            return [
                'total_women' => 0,
                'denominator' => 0,
                'period_label' => sprintf('%d / Q%d', $year, $quarter),
                'period_sublabel' => 'Avaliação Quadrimestral · Componente III (Peso 2.0x)',
                'avg_score' => 0.0,
                'practice_a' => ['eligible' => 0, 'compliant' => 0, 'count' => 0, 'percent' => 0.0],
                'practice_b' => ['eligible' => 0, 'compliant' => 0, 'count' => 0, 'percent' => 0.0],
                'practice_c' => ['eligible' => 0, 'compliant' => 0, 'count' => 0, 'percent' => 0.0],
                'practice_d' => ['eligible' => 0, 'compliant' => 0, 'count' => 0, 'percent' => 0.0],
                'regular_count' => 0,
                'sufficient_count' => 0,
                'good_count' => 0,
                'optimal_count' => 0,
            ];
        }

        $elA = $cohort->where('eligible_practice_a', true)->count();
        $cntA = $cohort->where('eligible_practice_a', true)->where('practice_a_met', true)->count();
        $pctA = $elA > 0 ? round(($cntA / $elA) * 100, 1) : 100.0;

        $elB = $cohort->where('eligible_practice_b', true)->count();
        $cntB = $cohort->where('eligible_practice_b', true)->where('practice_b_met', true)->count();
        $pctB = $elB > 0 ? round(($cntB / $elB) * 100, 1) : 100.0;

        $elC = $cohort->where('eligible_practice_c', true)->count();
        $cntC = $cohort->where('eligible_practice_c', true)->where('practice_c_met', true)->count();
        $pctC = $elC > 0 ? round(($cntC / $elC) * 100, 1) : 100.0;

        $elD = $cohort->where('eligible_practice_d', true)->count();
        $cntD = $cohort->where('eligible_practice_d', true)->where('practice_d_met', true)->count();
        $pctD = $elD > 0 ? round(($cntD / $elD) * 100, 1) : 100.0;

        $avgScore = round((float) $cohort->avg('score_percent'), 2);

        return [
            'total_women' => $total,
            'denominator' => $total,
            'period_label' => sprintf('%d / Q%d', $year, $quarter),
            'period_sublabel' => 'Avaliação Quadrimestral · Componente III (Peso 2.0x)',
            'avg_score' => $avgScore,
            'practice_a' => ['eligible' => $elA, 'compliant' => $cntA, 'count' => $cntA, 'percent' => $pctA],
            'practice_b' => ['eligible' => $elB, 'compliant' => $cntB, 'count' => $cntB, 'percent' => $pctB],
            'practice_c' => ['eligible' => $elC, 'compliant' => $cntC, 'count' => $cntC, 'percent' => $pctC],
            'practice_d' => ['eligible' => $elD, 'compliant' => $cntD, 'count' => $cntD, 'percent' => $pctD],
            'regular_count' => $cohort->where('performance_level', 'regular')->count(),
            'sufficient_count' => $cohort->where('performance_level', 'suficiente')->count(),
            'good_count' => $cohort->where('performance_level', 'bom')->count(),
            'optimal_count' => $cohort->where('performance_level', 'otimo')->count(),
        ];
    }

    /**
     * Retorna o resumo dos KPIs diretamente da tabela c7_cohort_snapshots (instantâneo e sem consumo de memória).
     */
    public function getSummaryKpisFromSnapshot(int $year, int $quarter, ?string $selectedIne = null): ?array
    {
        if (! Schema::hasTable('c7_cohort_snapshots')) {
            return null;
        }

        $query = DB::table('c7_cohort_snapshots')
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('calculation_version', C7DwService::VERSION);

        if ($selectedIne) {
            $snap = (clone $query)->where('ine', $selectedIne)->first();
        } else {
            $snap = (clone $query)->whereNull('ine')->first();
        }

        if (! $snap) {
            return null;
        }

        $elA = (int) $snap->practice_a_eligible;
        $cmA = (int) $snap->practice_a_compliant;
        $pctA = $elA > 0 ? round(($cmA / $elA) * 100, 1) : 100.0;

        $elB = (int) $snap->practice_b_eligible;
        $cmB = (int) $snap->practice_b_compliant;
        $pctB = $elB > 0 ? round(($cmB / $elB) * 100, 1) : 100.0;

        $elC = (int) $snap->practice_c_eligible;
        $cmC = (int) $snap->practice_c_compliant;
        $pctC = $elC > 0 ? round(($cmC / $elC) * 100, 1) : 100.0;

        $elD = (int) $snap->practice_d_eligible;
        $cmD = (int) $snap->practice_d_compliant;
        $pctD = $elD > 0 ? round(($cmD / $elD) * 100, 1) : 100.0;

        $tot = (int) $snap->cohort_total;

        return [
            'total_women' => $tot,
            'denominator' => $tot,
            'period_label' => sprintf('%d / Q%d', $year, $quarter),
            'period_sublabel' => 'Avaliação Quadrimestral · Componente III (Peso 2.0x)',
            'avg_score' => (float) $snap->final_score,
            'practice_a' => ['eligible' => $elA, 'compliant' => $cmA, 'count' => $cmA, 'percent' => $pctA],
            'practice_b' => ['eligible' => $elB, 'compliant' => $cmB, 'count' => $cmB, 'percent' => $pctB],
            'practice_c' => ['eligible' => $elC, 'compliant' => $cmC, 'count' => $cmC, 'percent' => $pctC],
            'practice_d' => ['eligible' => $elD, 'compliant' => $cmD, 'count' => $cmD, 'percent' => $pctD],
            'regular_count' => 0,
            'sufficient_count' => 0,
            'good_count' => 0,
            'optimal_count' => 0,
        ];
    }

    /**
     * Retorna as opções de filtro para dropdowns na tela.
     *
     * @return array<string, mixed>
     */
    public function getFilterOptions(int $year, int $quarter): array
    {
        if (! Schema::hasTable('c7_nominal_women')) {
            return [
                'districts' => [],
                'facilities' => [],
                'teams' => [],
                'microareas' => [],
                'races' => [],
            ];
        }

        $base = DB::table('c7_nominal_women')
            ->where('year', $year)
            ->where('quarter', $quarter);

        $districts = (clone $base)
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->distinct()
            ->pluck('district')
            ->sort()
            ->values()
            ->all();

        $facilities = (clone $base)
            ->whereNotNull('cnes')
            ->where('cnes', '!=', '')
            ->select('cnes', 'facility_name')
            ->distinct()
            ->orderBy('facility_name')
            ->get()
            ->map(fn ($f) => [
                'cnes' => (string) $f->cnes,
                'facility_name' => (string) $f->facility_name,
                'name' => (string) ($f->facility_name ?: "CNES {$f->cnes}"),
            ])
            ->values()
            ->all();

        $teams = (clone $base)
            ->whereNotNull('ine')
            ->where('ine', '!=', '')
            ->select('ine', 'team_name')
            ->distinct()
            ->orderBy('team_name')
            ->get()
            ->map(fn ($t) => [
                'ine' => (string) $t->ine,
                'team_name' => (string) $t->team_name,
                'name' => (string) ($t->team_name ?: "INE {$t->ine}"),
            ])
            ->values()
            ->all();

        $microareas = (clone $base)
            ->whereNotNull('microarea')
            ->where('microarea', '!=', '')
            ->where('microarea', '!=', '—')
            ->distinct()
            ->pluck('microarea')
            ->sort()
            ->values()
            ->all();

        $races = (clone $base)
            ->whereNotNull('race_color')
            ->where('race_color', '!=', '')
            ->distinct()
            ->pluck('race_color')
            ->sort()
            ->values()
            ->all();

        return [
            'districts' => $districts,
            'facilities' => $facilities,
            'teams' => $teams,
            'microareas' => $microareas,
            'races' => $races,
        ];
    }

    /**
     * Constrói o histórico clínico e parecer de auditoria normativa para uma mulher.
     *
     * @param  array<string, mixed>  $woman
     * @return array<string, mixed>
     */
    public function buildAuditTimeline(array $woman): array
    {
        $age = $woman['age_years'];

        // Prática A: Colo do Útero (25 a 64 anos)
        $practiceA = [
            'letter' => 'A',
            'title' => 'Rastreamento de Câncer do Colo do Útero',
            'target_range' => '25 a 64 anos',
            'weight' => '20 pontos',
            'eligible' => $woman['eligible_practice_a'],
            'met' => $woman['practice_a_met'],
            'last_date' => $woman['last_cervical_exam_date'],
            'exam_code' => $woman['last_cervical_exam_code'],
            'exam_desc' => $woman['last_cervical_exam_desc'],
            'window' => 'Últimos 36 meses (citopatológico) ou 60 meses (teste molecular DNA-HPV)',
            'recommendation' => $woman['eligible_practice_a']
                ? ($woman['practice_a_met'] ? 'Exame em dia conforme diretrizes do INCA/MS.' : 'Realizar coleta de citopatológico ou teste molecular pelo médico ou enfermeiro.')
                : "Fora da faixa etária prioritária ({$age} anos).",
        ];

        // Prática B: Vacina HPV (9 a 14 anos)
        $practiceB = [
            'letter' => 'B',
            'title' => 'Vacinação contra o Papilomavírus Humano (HPV)',
            'target_range' => '9 a 14 anos',
            'weight' => '30 pontos',
            'eligible' => $woman['eligible_practice_b'],
            'met' => $woman['practice_b_met'],
            'last_date' => $woman['last_hpv_vaccine_date'],
            'vaccine_code' => $woman['last_hpv_vaccine_code'],
            'vaccine_name' => $woman['last_hpv_vaccine_name'],
            'window' => 'Ao menos 1 dose registrada na vida até o final do período',
            'recommendation' => $woman['eligible_practice_b']
                ? ($woman['practice_b_met'] ? 'Vacina administrada e registrada no sistema.' : 'Administrar dose única/esquema de vacina HPV na UBS ou na escola.')
                : "Fora da faixa etária prioritária ({$age} anos).",
        ];

        // Prática C: Saúde Sexual e Reprodutiva (14 a 69 anos)
        $practiceC = [
            'letter' => 'C',
            'title' => 'Acesso e Cuidado em Saúde Sexual e Reprodutiva',
            'target_range' => '14 a 69 anos',
            'weight' => '30 pontos',
            'eligible' => $woman['eligible_practice_c'],
            'met' => $woman['practice_c_met'],
            'last_date' => $woman['last_sexual_health_date'],
            'code' => $woman['last_sexual_health_code'],
            'detail' => $woman['last_sexual_health_detail'],
            'window' => 'Últimos 12 meses (consulta presencial ou remota)',
            'recommendation' => $woman['eligible_practice_c']
                ? ($woman['practice_c_met'] ? 'Consulta realizada com abordagem de saúde reprodutiva/sexual.' : 'Agendar consulta com foco em planejamento reprodutivo ou queixas ginecológicas.')
                : "Fora da faixa etária prioritária ({$age} anos).",
        ];

        // Prática D: Câncer de Mama (50 a 69 anos)
        $practiceD = [
            'letter' => 'D',
            'title' => 'Rastreamento de Câncer de Mama (Mamografia)',
            'target_range' => '50 a 69 anos',
            'weight' => '20 pontos',
            'eligible' => $woman['eligible_practice_d'],
            'met' => $woman['practice_d_met'],
            'last_date' => $woman['last_mammogram_date'],
            'exam_code' => $woman['last_mammogram_code'],
            'exam_desc' => $woman['last_mammogram_desc'],
            'window' => 'Últimos 24 meses (mamografia de rastreamento solicitada ou avaliada)',
            'recommendation' => $woman['eligible_practice_d']
                ? ($woman['practice_d_met'] ? 'Mamografia solicitada ou avaliada nos últimos 24 meses.' : 'Solicitar ou avaliar mamografia bilateral de rastreamento.')
                : "Fora da faixa etária prioritária ({$age} anos).",
        ];

        return [
            'woman' => $woman,
            'practices' => [
                'A' => $practiceA,
                'B' => $practiceB,
                'C' => $practiceC,
                'D' => $practiceD,
            ],
            'pending_list' => $woman['pending_practices'],
            'overall_status' => count($woman['pending_practices']) === 0 ? 'Em dia' : 'Com pendências',
        ];
    }

    /**
     * Exporta a lista de busca ativa em formato CSV.
     *
     * @param  Collection<int, array<string, mixed>>  $cohort
     */
    public function exportCsv(Collection $cohort, int $year = 0, int $quarter = 0, ?string $teamName = null): StreamedResponse
    {
        $year = $year > 0 ? $year : (int) date('Y');
        $quarter = $quarter > 0 ? $quarter : 1;
        $filename = sprintf(
            'busca_ativa_c7_mulheres_%s_%d_Q%d_%s.csv',
            $teamName ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $teamName) : 'municipio',
            $year,
            $quarter,
            now()->format('Ymd_His')
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($cohort) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM para Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID Cidadã PEC',
                'Nome da Cidadã',
                'Nome Social',
                'CPF',
                'CNS',
                'Data Nascimento',
                'Idade',
                'Sexo Biológico',
                'Identidade de Gênero',
                'Telefone',
                'Raça/Cor',
                'CNES',
                'Unidade de Saúde',
                'Bairro',
                'INE',
                'Nome Equipe',
                'Microárea',
                'Elegível Colo Útero (A)',
                'Colo Útero Cumprido',
                'Último Exame Colo',
                'Elegível Vacina HPV (B)',
                'Vacina HPV Cumprida',
                'Última Vacina HPV',
                'Elegível Saúde Sexual (C)',
                'Saúde Sexual Cumprida',
                'Última Consulta Saúde Sexual',
                'Elegível Mama (D)',
                'Mama Cumprida',
                'Última Mamografia',
                'Pontuação Individual (%)',
                'Classificação',
                'Pendências Clínicas',
            ], ';');

            foreach ($cohort as $w) {
                fputcsv($handle, [
                    $w['cidadao_pec_id'],
                    $w['name'],
                    $w['social_name'] ?? '',
                    $w['cpf'],
                    $w['cns'],
                    $w['birth_date_formatted'],
                    $w['age_years'],
                    $w['sex'],
                    $w['gender_identity'],
                    $w['phone'] ?? '',
                    $w['race_color'],
                    $w['cnes'] ?? '',
                    $w['facility_name'] ?? '',
                    $w['district'] ?? '',
                    $w['ine'] ?? '',
                    $w['team_name'] ?? '',
                    $w['microarea'] ?? '',
                    $w['eligible_practice_a'] ? 'Sim' : 'Não',
                    $w['practice_a_met'] ? 'Cumprido' : ($w['eligible_practice_a'] ? 'Pendente' : 'Não se aplica'),
                    $w['last_cervical_exam_date'] ?? '—',
                    $w['eligible_practice_b'] ? 'Sim' : 'Não',
                    $w['practice_b_met'] ? 'Cumprido' : ($w['eligible_practice_b'] ? 'Pendente' : 'Não se aplica'),
                    $w['last_hpv_vaccine_date'] ?? '—',
                    $w['eligible_practice_c'] ? 'Sim' : 'Não',
                    $w['practice_c_met'] ? 'Cumprido' : ($w['eligible_practice_c'] ? 'Pendente' : 'Não se aplica'),
                    $w['last_sexual_health_date'] ?? '—',
                    $w['eligible_practice_d'] ? 'Sim' : 'Não',
                    $w['practice_d_met'] ? 'Cumprido' : ($w['eligible_practice_d'] ? 'Pendente' : 'Não se aplica'),
                    $w['last_mammogram_date'] ?? '—',
                    number_format($w['score_percent'], 1, ',', '.').'%',
                    strtoupper($w['performance_level']),
                    implode(', ', $w['pending_practices']) ?: 'Nenhuma (Em dia)',
                ], ';');
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
