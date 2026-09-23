<?php

namespace App\Services;

use App\Models\C3NominalPregnancy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class C3ActiveSearchService
{
    /**
     * Retorna os quadrimestres da janela de acompanhamento da busca ativa de gestantes e puérperas.
     * Cobre o quadrimestre atual e os 3 subsequentes (ciclo completo de gestação + puerpério).
     *
     * @return list<array{year: int, quarter: int}>
     */
    public static function getActiveSearchQuarterPairs(int $year, int $quarter): array
    {
        $pairs = [];
        $baseIndex = ($year * 3) + ($quarter - 1);
        for ($i = 0; $i <= 3; $i++) {
            $idx = $baseIndex + $i;
            $pairs[] = [
                'year' => intdiv($idx, 3),
                'quarter' => ($idx % 3) + 1,
            ];
        }

        return $pairs;
    }

    /**
     * Verifica se há registros reais na base local para a janela de acompanhamento do C3.
     */
    public static function isRealDataAvailable(int $year, int $quarter): bool
    {
        $periods = self::getActiveSearchQuarterPairs($year, $quarter);

        return C3NominalPregnancy::query()->where(function ($query) use ($periods) {
            foreach ($periods as $p) {
                $query->orWhere(function ($sub) use ($p) {
                    $sub->where('year', $p['year'])->where('quarter', $p['quarter']);
                });
            }
        })->exists();
    }

    /**
     * Retorna a quantidade de gestantes/puérperas reais gravadas.
     */
    public static function getRealPregnanciesCount(int $year, int $quarter, ?string $ine = null): int
    {
        $periods = self::getActiveSearchQuarterPairs($year, $quarter);
        $q = C3NominalPregnancy::query()->where(function ($query) use ($periods) {
            foreach ($periods as $p) {
                $query->orWhere(function ($sub) use ($p) {
                    $sub->where('year', $p['year'])->where('quarter', $p['quarter']);
                });
            }
        });

        if ($ine) {
            $q->where('ine', $ine);
        }

        return $q->count();
    }

    /**
     * Executa a extração em tempo real da base e-SUS PEC para a base local MySQL cobrindo a janela.
     *
     * @return array{pregnancies:int,cohort_pregnancies:int,completed_pregnancies:int,teams:int,months:int}
     */
    public function syncFromPec(int $year, int $quarter): array
    {
        $pec = DB::connection('pgsql_esus');
        $eligibleTeams = app(FamilyHealthService::class)->getEligibleTeams($year, $quarter);
        $periods = self::getActiveSearchQuarterPairs($year, $quarter);

        $totalStats = ['pregnancies' => 0, 'cohort_pregnancies' => 0, 'completed_pregnancies' => 0, 'teams' => 0, 'months' => 0];
        foreach ($periods as $p) {
            $stats = app(C3SnapshotService::class)->process($pec, $p['year'], $p['quarter'], $eligibleTeams);
            $totalStats['pregnancies'] += $stats['pregnancies'];
            $totalStats['cohort_pregnancies'] += $stats['cohort_pregnancies'];
            $totalStats['completed_pregnancies'] += $stats['completed_pregnancies'];
            $totalStats['teams'] = max($totalStats['teams'], $stats['teams']);
            $totalStats['months'] += $stats['months'];
        }

        return $totalStats;
    }

    /**
     * Colunas disponíveis para a tabela de busca ativa do C3.
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
            'professional' => 'Profissional',
            'gestational_age' => 'Idade Gestacional',
            'dpp' => 'DPP (40 Semanas)',
            'puerperium_end_date' => 'Data Final Puerpério (SIAP)',
            'microarea' => 'Micro Área',
            'month_ref' => 'Mês',
            'mici' => 'MCI Atualizada?',
            'good_practices' => 'Boas Práticas',
            'actions' => 'Ações',
        ];
    }

    /**
     * Colunas selecionadas por padrão no C3.
     *
     * @return list<string>
     */
    public static function getDefaultVisibleColumns(): array
    {
        return array_keys(self::getAvailableColumns());
    }

    /**
     * Lista base de gestantes e puérperas. Consulta exclusivamente c3_nominal_pregnancies (dados 100% reais do DW/PEC).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getBaseCohort(int $year, int $quarter, ?string $selectedIne = null): Collection
    {
        $periods = self::getActiveSearchQuarterPairs($year, $quarter);
        $dbQuery = C3NominalPregnancy::query()->where(function ($query) use ($periods) {
            foreach ($periods as $p) {
                $query->orWhere(function ($sub) use ($p) {
                    $sub->where('year', $p['year'])->where('quarter', $p['quarter']);
                });
            }
        });

        if ($selectedIne) {
            $dbQuery->where('ine', $selectedIne);
        }

        $records = $dbQuery->orderByDesc('year')->orderByDesc('quarter')->orderByDesc('id')->get();

        if ($records->isEmpty()) {
            return collect();
        }

        // Desduplicação por cidadão PEC garantindo a ocorrência mais recente da gestação/puerpério
        $uniqueRecords = $records->unique(function (C3NominalPregnancy $p) {
            return $p->cidadao_pec_id ?: ($p->cpf ?: ($p->cns ?: mb_strtolower($p->name).'_'.($p->birth_date?->format('Ymd') ?? '')));
        });

        $today = Carbon::today();

        return $uniqueRecords->map(function (C3NominalPregnancy $p) use ($today) {
            // Formatação da Idade Gestacional em semanas e dias (ex: 19s,5d)
            $gestAgeStr = '—';
            if ($p->dum) {
                $diffDays = (int) $p->dum->diffInDays($today, false);
                if ($diffDays >= 0 && $p->current_status === 'gestante') {
                    $weeks = intdiv($diffDays, 7);
                    $days = $diffDays % 7;
                    $gestAgeStr = "{$weeks}s,{$days}d";
                } elseif ($p->current_status === 'puerpera') {
                    $daysPuerp = $p->outcome_date ? (int) $p->outcome_date->diffInDays($today, false) : 0;
                    $gestAgeStr = $daysPuerp > 0 ? "Puerpério ({$daysPuerp}d)" : 'Puerpério';
                } else {
                    $weeks = (int) $p->gestational_age_weeks;
                    $gestAgeStr = "{$weeks}s,0d";
                }
            } elseif ($p->gestational_age_weeks > 0) {
                $gestAgeStr = "{$p->gestational_age_weeks}s,0d";
            }

            $dumFormatted = $p->dum ? $p->dum->format('d/m/Y') : '—';
            $dppFormatted = $p->dpp ? $p->dpp->format('d/m/Y') : '—';
            $outcomeFormatted = $p->outcome_date ? $p->outcome_date->format('d/m/Y') : '—';
            $puerperiumEndFormatted = $p->puerperium_end_date ? $p->puerperium_end_date->format('d/m/Y') : '—';
            $birthDateFormatted = $p->birth_date ? $p->birth_date->format('d/m/Y') : '—';

            $cns = trim((string) ($p->cns ?? ''));
            $cpf = trim((string) ($p->cpf ?? ''));

            return [
                'id' => $p->id,
                'year' => (int) $p->year,
                'quarter' => (int) $p->quarter,
                'cidadao_pec_id' => $p->cidadao_pec_id,
                'cns' => $cns,
                'cpf' => $cpf,
                'name' => $p->name,
                'birth_date' => $p->birth_date?->format('Y-m-d') ?? '',
                'birth_date_formatted' => $birthDateFormatted,
                'age_years' => (int) $p->age_years,
                'phone' => $p->phone ?? '',
                'race_color' => $p->race_color ?: 'Não informada',
                'cnes' => $p->cnes ?? '',
                'facility_name' => $p->facility_name ?? '',
                'district' => $p->district ?? '',
                'ine' => $p->ine ?? '',
                'team_name' => $p->team_name ?? '',
                'professional_cns' => $p->professional_cns ?? '',
                'professional_name' => $p->professional_name ?? '',
                'microarea' => $p->microarea ? str_pad($p->microarea, 2, '0', STR_PAD_LEFT) : '—',
                'dum' => $p->dum?->format('Y-m-d') ?? '',
                'dum_formatted' => $dumFormatted,
                'dpp' => $p->dpp?->format('Y-m-d') ?? '',
                'dpp_formatted' => $dppFormatted,
                'outcome_date' => $p->outcome_date?->format('Y-m-d') ?? '',
                'outcome_date_formatted' => $outcomeFormatted,
                'pregnancy_end_date' => $p->outcome_date?->format('Y-m-d') ?? '',
                'puerperium_end_date' => $p->puerperium_end_date?->format('Y-m-d') ?? '',
                'puerperium_end_date_formatted' => $puerperiumEndFormatted,
                'gestational_age_weeks' => (int) $p->gestational_age_weeks,
                'gestational_age_formatted' => $gestAgeStr,
                'current_status' => $p->current_status,
                'month_ref' => $p->month_ref ?: ($p->puerperium_end_date ? $p->puerperium_end_date->format('m/Y') : '—'),
                'mici_updated' => (bool) $p->mici_updated,
                'micdt_updated' => (bool) $p->micdt_updated,
                'is_accompanied' => (bool) $p->is_accompanied,
                'practice_a' => (int) $p->practice_a,
                'practice_b' => (int) $p->practice_b,
                'practice_b_count' => (int) $p->practice_b,
                'practice_c' => (int) $p->practice_c,
                'practice_c_count' => (int) $p->practice_c,
                'practice_d' => (int) $p->practice_d,
                'practice_d_count' => (int) $p->practice_d,
                'practice_e' => (int) $p->practice_e,
                'practice_e_count' => (int) $p->practice_e,
                'practice_f' => (int) $p->practice_f,
                'practice_g' => (int) $p->practice_g,
                'practice_h' => (int) $p->practice_h,
                'practice_i' => (int) $p->practice_i,
                'practice_j' => (int) $p->practice_j,
                'practice_k' => (int) $p->practice_k,
                'practice_a_met' => (bool) $p->practice_a_met,
                'practice_b_met' => (bool) $p->practice_b_met,
                'practice_c_met' => (bool) $p->practice_c_met,
                'practice_d_met' => (bool) $p->practice_d_met,
                'practice_e_met' => (bool) $p->practice_e_met,
                'practice_f_met' => (bool) $p->practice_f_met,
                'practice_g_met' => (bool) $p->practice_g_met,
                'practice_h_met' => (bool) $p->practice_h_met,
                'practice_i_met' => (bool) $p->practice_i_met,
                'practice_j_met' => (bool) $p->practice_j_met,
                'practice_k_met' => (bool) $p->practice_k_met,
                'score_percent' => (float) $p->score_percent,
                'total_points' => (float) $p->score_percent,
                'days_postpartum' => $p->outcome_date && $p->current_status === 'puerpera'
                    ? (int) $p->outcome_date->diffInDays($today)
                    : null,
                'is_real_data' => true,
            ];
        })->values();
    }

    /**
     * Aplica filtros rápidos e avançados na coorte de gestantes/puérperas.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function filterCohort(Collection $cohort, array $filters): Collection
    {
        return $cohort->filter(function ($item) use ($filters) {
            // Filtros Rápidos
            if (! empty($filters['searchCns'])) {
                $rawCns = preg_replace('/\D/', '', (string) $filters['searchCns']);
                $itemCns = preg_replace('/\D/', '', (string) $item['cns']);
                if (! str_contains($itemCns, $rawCns)) {
                    return false;
                }
            }

            if (! empty($filters['searchCpf'])) {
                $rawCpf = preg_replace('/\D/', '', (string) $filters['searchCpf']);
                $itemCpf = preg_replace('/\D/', '', (string) $item['cpf']);
                if (! str_contains($itemCpf, $rawCpf)) {
                    return false;
                }
            }

            if (! empty($filters['searchName'])) {
                $needle = mb_strtolower(trim((string) $filters['searchName']));
                if (! str_contains(mb_strtolower($item['name']), $needle)) {
                    return false;
                }
            }

            if (! empty($filters['searchCnes'])) {
                $cnes = trim((string) $filters['searchCnes']);
                if (! str_contains($item['cnes'], $cnes) && ! str_contains(mb_strtolower($item['facility_name']), mb_strtolower($cnes))) {
                    return false;
                }
            }

            if (! empty($filters['searchIne'])) {
                $ine = trim((string) $filters['searchIne']);
                if (! str_contains($item['ine'], $ine) && ! str_contains(mb_strtolower($item['team_name']), mb_strtolower($ine))) {
                    return false;
                }
            }

            // Filtros Avançados
            if (! empty($filters['advDistrict'])) {
                if (mb_strtolower($item['district']) !== mb_strtolower($filters['advDistrict'])) {
                    return false;
                }
            }

            if (! empty($filters['advFacility'])) {
                if ($item['cnes'] !== $filters['advFacility']) {
                    return false;
                }
            }

            if (! empty($filters['advTeam'])) {
                if ($item['ine'] !== $filters['advTeam']) {
                    return false;
                }
            }

            if (! empty($filters['advMicroarea'])) {
                $mFilter = str_pad(trim((string) $filters['advMicroarea']), 2, '0', STR_PAD_LEFT);
                if ($item['microarea'] !== $mFilter) {
                    return false;
                }
            }

            if (! empty($filters['advCitizenName'])) {
                $needle = mb_strtolower(trim((string) $filters['advCitizenName']));
                if (! str_contains(mb_strtolower($item['name']), $needle)) {
                    return false;
                }
            }

            if (! empty($filters['advCitizenCpf'])) {
                $rawCpf = preg_replace('/\D/', '', (string) $filters['advCitizenCpf']);
                $itemCpf = preg_replace('/\D/', '', (string) $item['cpf']);
                if (! str_contains($itemCpf, $rawCpf)) {
                    return false;
                }
            }

            if (! empty($filters['advCitizenCns'])) {
                $rawCns = preg_replace('/\D/', '', (string) $filters['advCitizenCns']);
                $itemCns = preg_replace('/\D/', '', (string) $item['cns']);
                if (! str_contains($itemCns, $rawCns)) {
                    return false;
                }
            }

            if (! empty($filters['advPhone'])) {
                $rawPhone = preg_replace('/\D/', '', (string) $filters['advPhone']);
                $itemPhone = preg_replace('/\D/', '', (string) ($item['phone'] ?? ''));
                if (! str_contains($itemPhone, $rawPhone)) {
                    return false;
                }
            }

            if (! empty($filters['advStatus'])) {
                if ($item['current_status'] !== $filters['advStatus']) {
                    return false;
                }
            }

            if (! empty($filters['advTrimester'])) {
                $gw = (int) ($item['gestational_age_weeks'] ?? 0);
                $matches = match ((string) $filters['advTrimester']) {
                    '1' => $gw >= 1 && $gw <= 13,
                    '2' => $gw >= 14 && $gw <= 27,
                    '3' => $gw >= 28,
                    'puerperio' => $item['current_status'] === 'puerpera',
                    default => true,
                };
                if (! $matches) {
                    return false;
                }
            }

            if (! empty($filters['advRaceColor'])) {
                if (mb_strtolower($item['race_color']) !== mb_strtolower($filters['advRaceColor'])) {
                    return false;
                }
            }

            // Filtro por Mês de Referência (MM/YYYY)
            if (! empty($filters['advMonth'])) {
                $rawFilter = preg_replace('/\s+/', '', (string) $filters['advMonth']);
                $rawItem = preg_replace('/\s+/', '', (string) ($item['month_ref'] ?? ''));

                if (str_contains($rawFilter, '/')) {
                    [$fMonth, $fYear] = explode('/', $rawFilter, 2);
                    $filterIndex = ((int) $fYear * 12) + (int) $fMonth;

                    $itemIndex = 0;
                    if (str_contains($rawItem, '/')) {
                        [$iMonth, $iYear] = explode('/', $rawItem, 2);
                        $itemIndex = ((int) $iYear * 12) + (int) $iMonth;
                    }

                    $monthOption = $filters['advMonthOption'] ?? 'selected_and_next';
                    if ($monthOption === 'selected_and_next') {
                        if ($itemIndex < $filterIndex) {
                            return false;
                        }
                    } else {
                        if ($rawItem !== $rawFilter) {
                            return false;
                        }
                    }
                }
            }

            // Filtro por Quadrimestre
            if (! empty($filters['advQuarter'])) {
                $qVal = (string) $filters['advQuarter'];
                $itemYear = (int) ($item['year'] ?? 0);
                $itemQuarter = (int) ($item['quarter'] ?? 0);

                if ($itemQuarter === 0) {
                    $rawItem = preg_replace('/\s+/', '', (string) ($item['month_ref'] ?? ''));
                    if (str_contains($rawItem, '/')) {
                        [$mStr, $yStr] = explode('/', $rawItem, 2);
                        $itemQuarter = (int) ceil((int) $mStr / 4);
                        $itemYear = (int) $yStr;
                    }
                }

                $baseY = (int) ($filters['baseYear'] ?? 2026);
                $baseQ = (int) ($filters['baseQuarter'] ?? 3);

                if ($qVal === 'current') {
                    if ($itemYear !== $baseY || $itemQuarter !== $baseQ) {
                        return false;
                    }
                } elseif (str_contains($qVal, '-')) {
                    [$targetYear, $targetQ] = explode('-', $qVal, 2);
                    if ($itemYear !== (int) $targetYear || $itemQuarter !== (int) $targetQ) {
                        return false;
                    }
                } elseif (is_numeric($qVal)) {
                    $targetQ = (int) $qVal;
                    if ($itemQuarter !== $targetQ) {
                        return false;
                    }
                }
            }

            // Filtros de Boas Práticas (sim / nao)
            $practiceKeys = [
                'advPracticeA' => 'practice_a_met',
                'advPracticeB' => 'practice_b_met',
                'advPracticeC' => 'practice_c_met',
                'advPracticeD' => 'practice_d_met',
                'advPracticeE' => 'practice_e_met',
                'advPracticeF' => 'practice_f_met',
                'advPracticeG' => 'practice_g_met',
                'advPracticeH' => 'practice_h_met',
                'advPracticeI' => 'practice_i_met',
                'advPracticeJ' => 'practice_j_met',
                'advPracticeK' => 'practice_k_met',
            ];

            foreach ($practiceKeys as $filterKey => $propKey) {
                if (isset($filters[$filterKey]) && $filters[$filterKey] !== null && $filters[$filterKey] !== '') {
                    $expected = $filters[$filterKey] === 'sim';
                    if ((bool) ($item[$propKey] ?? false) !== $expected) {
                        return false;
                    }
                }
            }

            // Filtros de Vínculo e Território
            if (isset($filters['advMici']) && $filters['advMici'] !== null && $filters['advMici'] !== '') {
                $expected = $filters['advMici'] === 'sim';
                if ((bool) ($item['mici_updated'] ?? false) !== $expected) {
                    return false;
                }
            }

            if (isset($filters['advMicdt']) && $filters['advMicdt'] !== null && $filters['advMicdt'] !== '') {
                $expected = $filters['advMicdt'] === 'sim';
                if ((bool) ($item['micdt_updated'] ?? false) !== $expected) {
                    return false;
                }
            }

            if (isset($filters['advAccompanied']) && $filters['advAccompanied'] !== null && $filters['advAccompanied'] !== '') {
                $expected = $filters['advAccompanied'] === 'sim';
                if ((bool) ($item['is_accompanied'] ?? false) !== $expected) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Calcula os KPIs de síntese dos dados gerais do banner superior do C3.
     *
     * @return array{
     *     period_label: string,
     *     period_sublabel: string,
     *     denominator: int,
     *     gestantes_count: int,
     *     puerperas_count: int,
     *     encerradas_count: int,
     *     practice_a: array{count: int, percent: float, label: string},
     *     practice_b: array{count: int, percent: float, label: string},
     *     practice_c: array{count: int, percent: float, label: string},
     *     practice_d: array{count: int, percent: float, label: string},
     *     practice_e: array{count: int, percent: float, label: string},
     *     practice_f: array{count: int, percent: float, label: string},
     *     practice_g: array{count: int, percent: float, label: string},
     *     practice_h: array{count: int, percent: float, label: string},
     *     practice_i: array{count: int, percent: float, label: string},
     *     practice_j: array{count: int, percent: float, label: string},
     *     practice_k: array{count: int, percent: float, label: string}
     * }
     */
    public function getSummaryKpis(Collection $cohort, int $year, int $quarter, ?string $selectedIne = null): array
    {
        $den = $cohort->count();
        $calcPercent = fn (int $cnt) => $den > 0 ? round(($cnt / $den) * 100, 2) : 0.0;

        $pA = $cohort->filter(fn ($it) => ! empty($it['practice_a_met']))->count();
        $pB = $cohort->filter(fn ($it) => ! empty($it['practice_b_met']))->count();
        $pC = $cohort->filter(fn ($it) => ! empty($it['practice_c_met']))->count();
        $pD = $cohort->filter(fn ($it) => ! empty($it['practice_d_met']))->count();
        $pE = $cohort->filter(fn ($it) => ! empty($it['practice_e_met']))->count();
        $pF = $cohort->filter(fn ($it) => ! empty($it['practice_f_met']))->count();
        $pG = $cohort->filter(fn ($it) => ! empty($it['practice_g_met']))->count();
        $pH = $cohort->filter(fn ($it) => ! empty($it['practice_h_met']))->count();
        $pI = $cohort->filter(fn ($it) => ! empty($it['practice_i_met']))->count();
        $pJ = $cohort->filter(fn ($it) => ! empty($it['practice_j_met']))->count();
        $pK = $cohort->filter(fn ($it) => ! empty($it['practice_k_met']))->count();

        $gestantes = $cohort->filter(fn ($it) => ($it['current_status'] ?? '') === 'gestante')->count();
        $puerperas = $cohort->filter(fn ($it) => ($it['current_status'] ?? '') === 'puerpera')->count();
        $encerradas = $cohort->filter(fn ($it) => ($it['current_status'] ?? '') === 'encerrada')->count();

        return [
            'period_label' => "{$year} / M9",
            'period_sublabel' => 'Mês selecionado e próximos',
            'denominator' => $den,
            'gestantes_count' => $gestantes,
            'puerperas_count' => $puerperas,
            'encerradas_count' => $encerradas,
            'total_gestantes' => $gestantes,
            'total_puerperas' => $puerperas,
            'total_evaluated_cohort' => $encerradas,
            'practice_a' => [
                'count' => $pA,
                'percent' => $calcPercent($pA),
                'label' => '1ª Consulta pré-natal até 12 semanas (A)',
                'points' => 10,
                'desc' => '1ª Consulta médica ou de enfermagem realizada até a 12ª semana de gestação',
            ],
            'practice_b' => [
                'count' => $pB,
                'percent' => $calcPercent($pB),
                'label' => 'Consultas (B)',
                'points' => 9,
                'desc' => 'Mínimo de 7 consultas presenciais ou remotas médicas ou de enfermagem durante a gestação',
            ],
            'practice_c' => [
                'count' => $pC,
                'percent' => $calcPercent($pC),
                'label' => 'Aferição de Pressão (C)',
                'points' => 9,
                'desc' => 'Mínimo de 7 registros de aferição de pressão arterial durante a gestação',
            ],
            'practice_d' => [
                'count' => $pD,
                'percent' => $calcPercent($pD),
                'label' => 'Peso e Altura (D)',
                'points' => 9,
                'desc' => 'Mínimo de 7 registros simultâneos de peso e altura realizados no mesmo dia durante a gestação',
            ],
            'practice_e' => [
                'count' => $pE,
                'percent' => $calcPercent($pE),
                'label' => 'Visitas Domiciliares (E)',
                'points' => 9,
                'desc' => 'Mínimo de 3 visitas domiciliares por ACS/TACS após a 1ª consulta do pré-natal (integral para eAP)',
            ],
            'practice_f' => [
                'count' => $pF,
                'percent' => $calcPercent($pF),
                'label' => 'dTpa (F)',
                'points' => 9,
                'desc' => 'Vacina acelular dTpa adulto registrada a partir da 20ª semana de gestação',
            ],
            'practice_g' => [
                'count' => $pG,
                'percent' => $calcPercent($pG),
                'label' => 'Testes 1º trimestre (G)',
                'points' => 9,
                'desc' => 'Testes rápidos ou exames avaliados para Sífilis, HIV, Hepatite B e Hepatite C no 1º trimestre',
            ],
            'practice_h' => [
                'count' => $pH,
                'percent' => $calcPercent($pH),
                'label' => 'Testes 3º trimestre (H)',
                'points' => 9,
                'desc' => 'Testes rápidos ou exames avaliados para Sífilis e HIV no 3º trimestre',
            ],
            'practice_i' => [
                'count' => $pI,
                'percent' => $calcPercent($pI),
                'label' => 'Consulta puerpério (I)',
                'points' => 9,
                'desc' => 'Mínimo de 1 consulta médica ou de enfermagem no puerpério (até 42 dias)',
            ],
            'practice_j' => [
                'count' => $pJ,
                'percent' => $calcPercent($pJ),
                'label' => 'Visita Domiciliar puerpério (J)',
                'points' => 9,
                'desc' => 'Mínimo de 1 visita domiciliar por ACS/TACS no puerpério (integral para eAP)',
            ],
            'practice_k' => [
                'count' => $pK,
                'percent' => $calcPercent($pK),
                'label' => 'Avaliação Odontológica (K)',
                'points' => 9,
                'desc' => 'Mínimo de 1 atividade em saúde bucal por Cirurgião-Dentista ou TSB durante a gestação',
            ],
        ];
    }

    /**
     * Retorna as opções para os seletores da Busca Avançada do C3.
     *
     * @return array<string, mixed>
     */
    public function getFilterOptions(?int $year = null, ?int $quarter = null): array
    {
        $baseYear = $year ?? (int) now()->year;
        $baseQuarter = $quarter ?? min(3, (int) ceil(now()->month / 4));

        // 1. Equipes e Unidades
        $teams = [];
        $facilities = [];
        $districts = [];

        try {
            $eligible = app(FamilyHealthService::class)->getEligibleTeams($baseYear, $baseQuarter);
            foreach ($eligible as $et) {
                $teams[$et['ine']] = [
                    'ine' => $et['ine'],
                    'name' => $et['ine'].' · '.$et['name'],
                ];
                if (! empty($et['cnes'])) {
                    $facilities[$et['cnes']] = [
                        'cnes' => $et['cnes'],
                        'name' => $et['cnes'].' · '.($et['facility_name'] ?? 'UBS'),
                    ];
                }
            }
        } catch (\Throwable) {
        }

        try {
            $nominalData = C3NominalPregnancy::query()
                ->select('ine', 'team_name', 'cnes', 'facility_name', 'district')
                ->distinct()
                ->get();
            foreach ($nominalData as $nt) {
                if ($nt->ine && ! isset($teams[$nt->ine])) {
                    $teams[$nt->ine] = [
                        'ine' => $nt->ine,
                        'name' => $nt->ine.' · '.($nt->team_name ?: 'Equipe eSF'),
                    ];
                }
                if ($nt->cnes && ! isset($facilities[$nt->cnes])) {
                    $facilities[$nt->cnes] = [
                        'cnes' => $nt->cnes,
                        'name' => $nt->cnes.' · '.($nt->facility_name ?: 'UBS'),
                    ];
                }
                if ($nt->district && ! in_array($nt->district, $districts, true)) {
                    $districts[] = $nt->district;
                }
            }
        } catch (\Throwable) {
        }

        // 2. Meses para janela de 4 quadrimestres
        $months = [];
        $startMonthNum = (($baseQuarter - 1) * 4) + 1;
        $currentStart = Carbon::create($baseYear, $startMonthNum, 1);

        for ($i = 0; $i < 16; $i++) {
            $m = (clone $currentStart)->addMonths($i);
            $qNum = min(3, (int) ceil($m->month / 4));
            $months[] = [
                'value' => $m->format('m/Y'),
                'label' => sprintf('%02d / %d', $m->month, $m->year),
                'year' => $m->year,
                'month' => $m->month,
                'quarter' => $qNum,
            ];
        }

        // 3. Quadrimestres
        $quarters = [];
        $baseIdx = ($baseYear * 3) + ($baseQuarter - 1);
        for ($i = 0; $i <= 3; $i++) {
            $idx = $baseIdx + $i;
            $y = intdiv($idx, 3);
            $q = ($idx % 3) + 1;
            $quarters[] = [
                'value' => "{$y}-Q{$q}",
                'label' => "{$y}/Q{$q}".($i === 0 ? ' (Quadrimestre Atual)' : ''),
                'year' => $y,
                'quarter' => $q,
            ];
        }

        // 4. Raças/Cores
        $races = ['Branca', 'Preta', 'Parda', 'Amarela', 'Indígena', 'Não informada'];

        // 5. Microáreas
        $microareas = [];
        for ($m = 1; $m <= 20; $m++) {
            $microareas[] = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
        }

        return [
            'teams' => array_values($teams),
            'facilities' => array_values($facilities),
            'districts' => $districts,
            'microareas' => $microareas,
            'months' => $months,
            'quarters' => $quarters,
            'races' => $races,
            'status_options' => [
                ['value' => 'gestante', 'label' => 'Gestante Ativa'],
                ['value' => 'puerpera', 'label' => 'Puérpera (até 42 dias pós-parto)'],
                ['value' => 'encerrada', 'label' => 'Puerpério Encerrado no Quadrimestre'],
            ],
            'trimester_options' => [
                ['value' => '1', 'label' => '1º Trimestre (até 13 semanas)'],
                ['value' => '2', 'label' => '2º Trimestre (14 a 27 semanas)'],
                ['value' => '3', 'label' => '3º Trimestre (28 semanas ou mais)'],
                ['value' => 'puerperio', 'label' => 'Puerpério'],
            ],
        ];
    }
}
