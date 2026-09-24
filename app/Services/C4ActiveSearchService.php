<?php

namespace App\Services;

use App\Models\C4NominalDiabetic;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class C4ActiveSearchService
{
    /**
     * Verifica se há registros reais na base local para o quadrimestre do C4.
     */
    public static function isRealDataAvailable(int $year, int $quarter): bool
    {
        if (! Schema::hasTable('c4_nominal_diabetics')) {
            return false;
        }

        return C4NominalDiabetic::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->exists();
    }

    /**
     * Retorna a quantidade de diabéticos reais gravados.
     */
    public static function getRealDiabeticsCount(int $year, int $quarter, ?string $ine = null): int
    {
        if (! Schema::hasTable('c4_nominal_diabetics')) {
            return 0;
        }

        $q = C4NominalDiabetic::query()
            ->where('year', $year)
            ->where('quarter', $quarter);

        if ($ine) {
            $q->where('ine', $ine);
        }

        return $q->count();
    }

    /**
     * Colunas disponíveis para a tabela de busca ativa do C4.
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
            'good_practices' => 'Boas Práticas (A-F)',
        ];
    }

    /**
     * Colunas selecionadas por padrão no C4.
     *
     * @return list<string>
     */
    public static function getDefaultVisibleColumns(): array
    {
        return array_keys(self::getAvailableColumns());
    }

    /**
     * Lista base de diabéticos da coorte. Consulta exclusivamente c4_nominal_diabetics.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getBaseCohort(int $year, int $quarter, ?string $selectedIne = null): Collection
    {
        if (! Schema::hasTable('c4_nominal_diabetics')) {
            return collect();
        }

        $dbQuery = C4NominalDiabetic::query()
            ->where('year', $year)
            ->where('quarter', $quarter);

        if ($selectedIne) {
            $dbQuery->where('ine', $selectedIne);
        }

        $records = $dbQuery->orderByDesc('score_percent')->orderBy('name')->get();

        if ($records->isEmpty()) {
            return collect();
        }

        return $records->map(function (C4NominalDiabetic $d) {
            $birthDateFormatted = $d->birth_date ? $d->birth_date->format('d/m/Y') : '—';
            $cns = trim((string) ($d->cns ?? ''));
            $cpf = trim((string) ($d->cpf ?? ''));

            return [
                'id' => $d->id,
                'year' => (int) $d->year,
                'quarter' => (int) $d->quarter,
                'cidadao_pec_id' => $d->cidadao_pec_id,
                'cns' => $cns,
                'cpf' => $cpf,
                'cns_masked' => $cns !== '' ? substr($cns, 0, 3) . ' **** **** ' . substr($cns, -4) : '—',
                'cpf_masked' => $cpf !== '' ? substr($cpf, 0, 3) . '.***.***-' . substr($cpf, -2) : '—',
                'name' => $d->name,
                'social_name' => $d->social_name,
                'birth_date' => $d->birth_date?->toDateString(),
                'birth_date_formatted' => $birthDateFormatted,
                'age_years' => (int) $d->age_years,
                'phone' => $d->phone,
                'race_color' => $d->race_color ?: 'Não informada',
                'cnes' => $d->cnes,
                'facility_name' => $d->facility_name,
                'district' => $d->district,
                'ine' => $d->ine,
                'team_name' => $d->team_name,
                'microarea' => $d->microarea ?: '—',
                'condition_status' => $d->condition_status,
                'ciap_codes' => $d->ciap_codes,
                'cid_codes' => $d->cid_codes,
                'first_diagnosis_date' => $d->first_diagnosis_date?->format('d/m/Y') ?? '—',
                'last_diagnosis_date' => $d->last_diagnosis_date?->format('d/m/Y') ?? '—',
                'month_ref' => $d->month_ref,
                'mici_updated' => (bool) $d->mici_updated,
                'is_accompanied' => (bool) $d->is_accompanied,
                // Práticas
                'practice_a' => (int) $d->practice_a,
                'practice_a_met' => (bool) $d->practice_a_met,
                'last_consultation_date' => $d->last_consultation_date?->format('d/m/Y') ?? '—',
                'practice_b' => (int) $d->practice_b,
                'practice_b_met' => (bool) $d->practice_b_met,
                'last_pa_date' => $d->last_pa_date?->format('d/m/Y') ?? '—',
                'last_pa_value' => $d->last_pa_value ?: '—',
                'practice_c' => (int) $d->practice_c,
                'practice_c_met' => (bool) $d->practice_c_met,
                'last_anthropometry_date' => $d->last_anthropometry_date?->format('d/m/Y') ?? '—',
                'last_weight' => $d->last_weight ? number_format((float) $d->last_weight, 1, ',', '.') . ' kg' : '—',
                'last_height' => $d->last_height ? number_format((float) $d->last_height, 1, ',', '.') . ' cm' : '—',
                'practice_d' => (int) $d->practice_d,
                'practice_d_met' => (bool) $d->practice_d_met,
                'last_visit_date' => $d->last_visit_date?->format('d/m/Y') ?? '—',
                'practice_e' => (int) $d->practice_e,
                'practice_e_met' => (bool) $d->practice_e_met,
                'last_hba1c_date' => $d->last_hba1c_date?->format('d/m/Y') ?? '—',
                'last_hba1c_type' => $d->last_hba1c_type ?: '—',
                'practice_f' => (int) $d->practice_f,
                'practice_f_met' => (bool) $d->practice_f_met,
                'last_foot_exam_date' => $d->last_foot_exam_date?->format('d/m/Y') ?? '—',
                'score_percent' => (float) $d->score_percent,
                'performance_level' => FamilyHealthService::calculatePerformanceLevel('c4', (float) $d->score_percent),
            ];
        });
    }

    /**
     * Aplica filtros de busca sobre a coleção de diabéticos.
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
                'advPracticeE' => 'practice_e_met',
                'advPracticeF' => 'practice_f_met',
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
     * Retorna os KPIs e métricas consolidadas dos diabéticos filtrados.
     *
     * @param  Collection<int, array<string, mixed>>  $cohort
     * @return array<string, mixed>
     */
    public function getSummaryKpis(Collection $cohort, int $year, int $quarter): array
    {
        $total = $cohort->count();
        if ($total === 0) {
            return [
                'total_diabetics' => 0,
                'denominator' => 0,
                'period_label' => sprintf('%d / Q%d', $year, $quarter),
                'period_sublabel' => 'Avaliação Quadrimestral · Componente III',
                'avg_score' => 0.0,
                'practice_a_pct' => 0.0,
                'practice_b_pct' => 0.0,
                'practice_c_pct' => 0.0,
                'practice_d_pct' => 0.0,
                'practice_e_pct' => 0.0,
                'practice_f_pct' => 0.0,
                'practice_a_count' => 0,
                'practice_b_count' => 0,
                'practice_c_count' => 0,
                'practice_d_count' => 0,
                'practice_e_count' => 0,
                'practice_f_count' => 0,
                'practice_a' => ['count' => 0, 'percent' => 0.0],
                'practice_b' => ['count' => 0, 'percent' => 0.0],
                'practice_c' => ['count' => 0, 'percent' => 0.0],
                'practice_d' => ['count' => 0, 'percent' => 0.0],
                'practice_e' => ['count' => 0, 'percent' => 0.0],
                'practice_f' => ['count' => 0, 'percent' => 0.0],
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
        $cntE = $cohort->where('practice_e_met', true)->count();
        $cntF = $cohort->where('practice_f_met', true)->count();

        $avgScore = round($cohort->avg('score_percent'), 2);
        $pctA = round(($cntA / $total) * 100, 1);
        $pctB = round(($cntB / $total) * 100, 1);
        $pctC = round(($cntC / $total) * 100, 1);
        $pctD = round(($cntD / $total) * 100, 1);
        $pctE = round(($cntE / $total) * 100, 1);
        $pctF = round(($cntF / $total) * 100, 1);

        return [
            'total_diabetics' => $total,
            'denominator' => $total,
            'period_label' => sprintf('%d / Q%d', $year, $quarter),
            'period_sublabel' => 'Avaliação Quadrimestral · Componente III',
            'avg_score' => $avgScore,
            'practice_a_pct' => $pctA,
            'practice_b_pct' => $pctB,
            'practice_c_pct' => $pctC,
            'practice_d_pct' => $pctD,
            'practice_e_pct' => $pctE,
            'practice_f_pct' => $pctF,
            'practice_a_count' => $cntA,
            'practice_b_count' => $cntB,
            'practice_c_count' => $cntC,
            'practice_d_count' => $cntD,
            'practice_e_count' => $cntE,
            'practice_f_count' => $cntF,
            'practice_a' => ['count' => $cntA, 'percent' => $pctA],
            'practice_b' => ['count' => $cntB, 'percent' => $pctB],
            'practice_c' => ['count' => $cntC, 'percent' => $pctC],
            'practice_d' => ['count' => $cntD, 'percent' => $pctD],
            'practice_e' => ['count' => $cntE, 'percent' => $pctE],
            'practice_f' => ['count' => $cntF, 'percent' => $pctF],
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
        if (! Schema::hasTable('c4_nominal_diabetics')) {
            return [
                'facilities' => [],
                'teams' => [],
                'microareas' => [],
            ];
        }

        $facilities = C4NominalDiabetic::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNotNull('cnes')
            ->select('cnes', 'facility_name')
            ->distinct()
            ->orderBy('facility_name')
            ->get()
            ->map(fn ($f) => ['cnes' => $f->cnes, 'facility_name' => $f->facility_name])
            ->toArray();

        $teams = C4NominalDiabetic::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNotNull('ine')
            ->select('ine', 'team_name')
            ->distinct()
            ->orderBy('team_name')
            ->get()
            ->map(fn ($t) => ['ine' => $t->ine, 'team_name' => $t->team_name])
            ->toArray();

        $microareas = C4NominalDiabetic::query()
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
     * Exporta os cidadãos diabéticos em formato CSV.
     *
     * @param  Collection<int, array<string, mixed>>  $diabetics
     */
    public function exportCsv(Collection $diabetics): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="c4_busca_ativa_diabeticos_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($diabetics): void {
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
                '(E) Hemoglobina Glicada', 'Última HbA1c', 'Tipo HbA1c',
                '(F) Avaliação dos Pés', 'Última Avaliação Pés',
                'Pontuação (0-100)', 'Classificação',
            ], ';');

            foreach ($diabetics as $d) {
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
                    $d['practice_e_met'] ? 'SIM' : 'NÃO',
                    $d['last_hba1c_date'],
                    $d['last_hba1c_type'],
                    $d['practice_f_met'] ? 'SIM' : 'NÃO',
                    $d['last_foot_exam_date'],
                    number_format($d['score_percent'], 2, ',', '.'),
                    ucfirst($d['performance_level']),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
