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
            'id' => '# (ID)',
            'cns' => 'Cartão SUS (CNS)',
            'cpf' => 'CPF',
            'name' => 'Nome da Gestante / Puérpera',
            'birth_date' => 'Data de Nascimento',
            'age_years' => 'Idade (Anos)',
            'phone' => 'Telefone / Celular',
            'race_color' => 'Raça / Cor',
            'facility' => 'Unidade Básica (CNES)',
            'team' => 'Equipe de Saúde (INE)',
            'professional' => 'Profissional / ACS',
            'microarea' => 'Microárea',
            'current_status' => 'Status Clínico',
            'gestational_age_weeks' => 'Idade Gestacional (Sem)',
            'dum' => 'DUM',
            'dpp' => 'DPP Provável',
            'pregnancy_end_date' => 'Desfecho estimado (DUM + 294d)',
            'puerperium_end_date' => 'Fim estimado do Puerpério (42d)',
            'month_ref' => 'Mês de Referência',
            'mici' => 'Cadastro Atualizado?',
            'practice_a_met' => 'Captação Precoce (A)',
            'practice_b_met' => '7+ Consultas (B)',
            'practice_c_met' => '7+ PA Aferida (C)',
            'practice_d_met' => '7+ Peso e Altura (D)',
            'practice_e_met' => '3+ Visitas ACS (E)',
            'practice_f_met' => 'Vacina dTpa (F)',
            'practice_g_met' => 'Exames 1º Trim (G)',
            'practice_h_met' => 'Exames 3º Trim (H)',
            'practice_i_met' => 'Consulta Puerpério (I)',
            'practice_j_met' => 'Visita Puerpério (J)',
            'practice_k_met' => 'Saúde Bucal (K)',
            'total_points' => 'Pontuação Individual',
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
        return [
            'cns',
            'cpf',
            'name',
            'current_status',
            'gestational_age_weeks',
            'dpp',
            'facility',
            'team',
            'practice_a_met',
            'practice_b_met',
            'practice_c_met',
            'practice_d_met',
            'practice_e_met',
            'practice_f_met',
            'practice_g_met',
            'practice_h_met',
            'practice_i_met',
            'practice_j_met',
            'practice_k_met',
            'total_points',
            'actions',
        ];
    }

    /**
     * Lista base de gestantes e puérperas. Consulta prioritariamente c3_nominal_pregnancies.
     * Se vazio, gera dados semente consistentes para desenvolvimento e homologação.
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

        $records = $dbQuery->get();

        if ($records->isNotEmpty()) {
            return $records->map(function (C3NominalPregnancy $p) {
                return [
                    'id' => $p->id,
                    'year' => (int) $p->year,
                    'quarter' => (int) $p->quarter,
                    'cidadao_pec_id' => $p->cidadao_pec_id,
                    'cns' => $p->cns ?? '',
                    'cpf' => $p->cpf ?? '',
                    'name' => $p->name,
                    'birth_date' => $p->birth_date?->format('Y-m-d') ?? '',
                    'age_years' => $p->age_years,
                    'phone' => $p->phone ?? '',
                    'race_color' => $p->race_color ?? 'Não informada',
                    'cnes' => $p->cnes ?? '',
                    'facility_name' => $p->facility_name ?? '',
                    'district' => $p->district ?? '',
                    'ine' => $p->ine ?? '',
                    'team_name' => $p->team_name ?? '',
                    'professional_cns' => $p->professional_cns ?? '',
                    'professional_name' => $p->professional_name ?? '',
                    'microarea' => $p->microarea ?? '',
                    'dum' => $p->dum?->format('Y-m-d') ?? '',
                    'dpp' => $p->dpp?->format('Y-m-d') ?? '',
                    'outcome_date' => $p->outcome_date?->format('Y-m-d') ?? '',
                    'pregnancy_end_date' => $p->outcome_date?->format('Y-m-d') ?? '',
                    'puerperium_end_date' => $puerperiumEnd = $p->puerperium_end_date?->format('Y-m-d') ?? '',
                    'gestational_age_weeks' => $p->gestational_age_weeks,
                    'current_status' => $p->current_status,
                    'month_ref' => $p->month_ref ?? '',
                    'mici_updated' => (bool) $p->mici_updated,
                    'micdt_updated' => (bool) $p->micdt_updated,
                    'is_accompanied' => (bool) $p->is_accompanied,
                    'practice_a' => $p->practice_a,
                    'practice_b' => $p->practice_b,
                    'practice_b_count' => $p->practice_b,
                    'practice_c' => $p->practice_c,
                    'practice_c_count' => $p->practice_c,
                    'practice_d' => $p->practice_d,
                    'practice_d_count' => $p->practice_d,
                    'practice_e' => $p->practice_e,
                    'practice_e_count' => $p->practice_e,
                    'practice_f' => $p->practice_f,
                    'practice_g' => $p->practice_g,
                    'practice_h' => $p->practice_h,
                    'practice_i' => $p->practice_i,
                    'practice_j' => $p->practice_j,
                    'practice_k' => $p->practice_k,
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
                        ? (int) $p->outcome_date->diffInDays(today())
                        : null,
                    'is_real_data' => true,
                ];
            });
        }

        // Dados simulados são permitidos somente em desenvolvimento/testes.
        return app()->environment('local', 'testing')
            ? $this->generateRealisticFallbackCohort($year, $quarter, $selectedIne)
            : collect();
    }

    /**
     * Gera coorte simulada realista de gestantes e puérperas.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function generateRealisticFallbackCohort(int $year, int $quarter, ?string $selectedIne = null): Collection
    {
        $facilities = [
            ['cnes' => '0111791', 'name' => 'USF CENTRO DE SAUDE CENTRAL', 'district' => 'Centro'],
            ['cnes' => '2719886', 'name' => 'USF SAO JORGE', 'district' => 'São Jorge'],
            ['cnes' => '2722682', 'name' => 'USF GULANDIM', 'district' => 'Gulandim'],
            ['cnes' => '2008556', 'name' => 'USF VILA NOVA', 'district' => 'Vila Nova'],
            ['cnes' => '2722593', 'name' => 'USF BAIRRO NOVO', 'district' => 'Bairro Novo'],
            ['cnes' => '2722623', 'name' => 'USF JARDIM DAS PALMEIRAS', 'district' => 'Palmeiras'],
            ['cnes' => '4020596', 'name' => 'USF MUTIRAO', 'district' => 'Mutirão'],
            ['cnes' => '2719738', 'name' => 'USF PARQUE DO FUTURO', 'district' => 'Parque do Futuro'],
            ['cnes' => '2719746', 'name' => 'USF RETIRO', 'district' => 'Zona Rural'],
            ['cnes' => '2719754', 'name' => 'USF POCO DA PEDRA', 'district' => 'Zona Rural'],
        ];

        $teams = [
            ['ine' => '0001715364', 'name' => 'eSF 01 · Centro', 'cnes' => '0111791'],
            ['ine' => '000171123', 'name' => 'eSF 02 · São Jorge', 'cnes' => '2719886'],
            ['ine' => '000171220', 'name' => 'eSF 03 · Gulandim', 'cnes' => '2722682'],
            ['ine' => '000171085', 'name' => 'eSF 04 · Vila Nova', 'cnes' => '2008556'],
            ['ine' => '000171174', 'name' => 'eSF 05 · Bairro Novo', 'cnes' => '2722593'],
            ['ine' => '000171704', 'name' => 'eSF 06 · Palmeiras', 'cnes' => '2722623'],
            ['ine' => '000171239', 'name' => 'eSF 07 · Mutirão', 'cnes' => '4020596'],
            ['ine' => '000171107', 'name' => 'eSF 08 · Parque do Futuro', 'cnes' => '2719738'],
            ['ine' => '000171311', 'name' => 'eSF 09 · Retiro', 'cnes' => '2719746'],
            ['ine' => '000171425', 'name' => 'eSF 10 · Poço da Pedra', 'cnes' => '2719754'],
        ];

        if ($selectedIne) {
            $teams = array_values(array_filter($teams, fn ($t) => $t['ine'] === $selectedIne));
            if (empty($teams)) {
                $teams = [['ine' => $selectedIne, 'name' => 'eSF '.$selectedIne, 'cnes' => '0111791']];
            }
        }

        $names = [
            'ANA CLARA PEREIRA SILVA', 'BEATRIZ ALMEIDA SANTOS', 'CAMILA COSTA RODRIGUES',
            'DANIELA GOMES BARBOSA', 'EDUARDA LIMA CARVALHO', 'FERNANDA RIBEIRO SOUZA',
            'GABRIELA MARTINS CASTRO', 'HELENA MORAIS DIAS', 'ISABELA NASCIMENTO PINTO',
            'JULIANA TEIXEIRA ALVES', 'LARISSA MOREIRA NUNES', 'MARIANA CARDOSO FREITAS',
            'NATALIA ARAUJO RAMOS', 'PATRICIA FARIAS VIEIRA', 'RAQUEL CORDEIRO LOPES',
            'SABRINA MEDEIROS MOURA', 'TATIANE NOGUEIRA DUARTE', 'VALERIA CAVALCANTI REIS',
            'YASMIN TAVARES GUIMARAES', 'AMANDA ROCHA BARROS', 'BRUNA PEIXOTO FAGUNDES',
            'CAROLINA MENDES BASTOS', 'DEBORA MACHADO FERREIRA', 'EMANUELLE DANTAS SALES',
        ];

        $races = ['Parda', 'Parda', 'Branca', 'Branca', 'Preta', 'Indígena'];
        $baseDate = Carbon::create($year, 9, 15);
        $items = [];
        $seq = 450101;

        // Gera 120 gestantes/puérperas distribuídas entre as equipes
        for ($i = 0; $i < 120; $i++) {
            $team = $teams[$i % count($teams)];
            $facility = collect($facilities)->firstWhere('cnes', $team['cnes']) ?? $facilities[0];
            $ageYears = 18 + ($i % 24); // 18 a 41 anos
            $birthDate = (clone $baseDate)->subYears($ageYears)->subDays(($i * 13) % 360);

            // Distribuição clínica: 65% gestantes (semanas 4 a 38), 20% puérperas (até 42 dias), 15% coorte encerrada
            $statusCategory = ($i % 10 < 6) ? 'gestante' : (($i % 10 < 8) ? 'puerpera' : 'encerrada');

            if ($statusCategory === 'gestante') {
                $gestationalWeeks = 6 + (($i * 3) % 34); // 6 a 39 semanas
                $dum = (clone $baseDate)->subWeeks($gestationalWeeks);
                $dpp = (clone $dum)->addDays(280);
                $puerperiumEnd = (clone $dpp)->addDays(42);
                $outcomeDate = null;
            } elseif ($statusCategory === 'puerpera') {
                $daysPostPartum = 5 + (($i * 4) % 35); // 5 a 39 dias pós-parto
                $outcomeDate = (clone $baseDate)->subDays($daysPostPartum);
                $puerperiumEnd = (clone $outcomeDate)->addDays(42);
                $dum = (clone $outcomeDate)->subDays(280);
                $dpp = (clone $dum)->addDays(280);
                $gestationalWeeks = 40;
            } else {
                // Coorte avaliada no quadrimestre
                $puerperiumEnd = (clone $baseDate)->subDays(10 + ($i % 60));
                $outcomeDate = (clone $puerperiumEnd)->subDays(42);
                $dpp = (clone $outcomeDate);
                $dum = (clone $dpp)->subDays(280);
                $gestationalWeeks = 40;
            }

            // Práticas Clínicas (conforme semanas de gestação e situação)
            $earlyConsult = ($i % 5 !== 0); // 80% captação precoce (A)
            $pBCount = min(12, max(1, (int) round(($gestationalWeeks / 40) * (7 + ($i % 3)))));
            $pCCount = min(12, max(1, (int) round(($gestationalWeeks / 40) * (7 + ($i % 2)))));
            $pDCount = min(12, max(1, (int) round(($gestationalWeeks / 40) * (7 + ($i % 2)))));
            $pECount = min(8, max(1, (int) round(($gestationalWeeks / 40) * (3 + ($i % 3)))));
            $pFCount = ($gestationalWeeks >= 20 && ($i % 4 !== 0)) ? 1 : 0;
            $metG = ($gestationalWeeks >= 12 && ($i % 5 !== 0));
            $metH = ($gestationalWeeks >= 28 && ($i % 4 !== 0));
            $pICount = ($statusCategory !== 'gestante' && ($i % 3 !== 0)) ? 1 : 0;
            $pJCount = ($statusCategory !== 'gestante' && ($i % 4 !== 0)) ? 1 : 0;
            $pKCount = ($i % 3 !== 0) ? 1 : 0;

            // Metas
            $metA = $earlyConsult;
            $metB = $pBCount >= 7;
            $metC = $pCCount >= 7;
            $metD = $pDCount >= 7;
            $metE = $pECount >= 3;
            $metF = $pFCount >= 1;
            $metI = $pICount >= 1;
            $metJ = $pJCount >= 1;
            $metK = $pKCount >= 1;

            $score = 0;
            if ($metA) {
                $score += 10;
            }
            if ($metB) {
                $score += 9;
            }
            if ($metC) {
                $score += 9;
            }
            if ($metD) {
                $score += 9;
            }
            if ($metE) {
                $score += 9;
            }
            if ($metF) {
                $score += 9;
            }
            if ($metG) {
                $score += 9;
            }
            if ($metH) {
                $score += 9;
            }
            if ($metI) {
                $score += 9;
            }
            if ($metJ) {
                $score += 9;
            }
            if ($metK) {
                $score += 9;
            }

            $cnsRaw = sprintf('70%02d%011d', ($i % 80) + 10, 20000000000 + ($i * 94371));
            $cpfRaw = sprintf('%03d.%03d.%03d-%02d', 120 + ($i % 800), 300 + (($i * 3) % 600), 400 + (($i * 7) % 500), 10 + ($i % 89));

            $items[] = [
                'id' => $seq++,
                'year' => $year,
                'quarter' => $quarter,
                'cidadao_pec_id' => 900000 + $i,
                'cns' => $cnsRaw,
                'cpf' => $cpfRaw,
                'name' => $names[$i % count($names)].' '.chr(65 + ($i % 26)),
                'birth_date' => $birthDate->format('Y-m-d'),
                'age_years' => $ageYears,
                'phone' => sprintf('(82) 9%04d-%04d', 8000 + ($i * 7), 1000 + ($i * 13)),
                'race_color' => $races[$i % count($races)],
                'cnes' => $facility['cnes'],
                'facility_name' => $facility['name'],
                'district' => $facility['district'],
                'ine' => $team['ine'],
                'team_name' => $team['name'],
                'professional_cns' => sprintf('70%02d%011d', 30 + ($i % 60), 30000000000 + ($i * 53412)),
                'professional_name' => sprintf('Enf. Responsável · %s', $team['name']),
                'microarea' => sprintf('%02d', ($i % 12) + 1),
                'dum' => $dum->format('Y-m-d'),
                'dpp' => $dpp->format('Y-m-d'),
                'outcome_date' => $outcomeDate?->format('Y-m-d'),
                'pregnancy_end_date' => $outcomeDate?->format('Y-m-d'),
                'puerperium_end_date' => $puerperiumEnd->format('Y-m-d'),
                'gestational_age_weeks' => $gestationalWeeks,
                'current_status' => $statusCategory,
                'month_ref' => $puerperiumEnd->format('m/Y'),
                'mici_updated' => ($i % 12 !== 0),
                'micdt_updated' => ($i % 10 !== 0),
                'is_accompanied' => true,
                'practice_a' => $metA ? 1 : 0,
                'practice_b' => $pBCount,
                'practice_b_count' => $pBCount,
                'practice_c' => $pCCount,
                'practice_c_count' => $pCCount,
                'practice_d' => $pDCount,
                'practice_d_count' => $pDCount,
                'practice_e' => $pECount,
                'practice_e_count' => $pECount,
                'practice_f' => $pFCount,
                'practice_g' => $metG ? 1 : 0,
                'practice_h' => $metH ? 1 : 0,
                'practice_i' => $pICount,
                'practice_j' => $pJCount,
                'practice_k' => $pKCount,
                'practice_a_met' => $metA,
                'practice_b_met' => $metB,
                'practice_c_met' => $metC,
                'practice_d_met' => $metD,
                'practice_e_met' => $metE,
                'practice_f_met' => $metF,
                'practice_g_met' => $metG,
                'practice_h_met' => $metH,
                'practice_i_met' => $metI,
                'practice_j_met' => $metJ,
                'practice_k_met' => $metK,
                'score_percent' => (float) $score,
                'total_points' => (float) $score,
                'days_postpartum' => $statusCategory === 'puerpera' && $outcomeDate
                    ? $outcomeDate->diffInDays($baseDate)
                    : null,
                'is_real_data' => false,
            ];
        }

        return collect($items);
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

                    $monthOption = $filters['advMonthOption'] ?? '';
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

        $quarterNames = [1 => 'Q1 (Jan - Abr)', 2 => 'Q2 (Mai - Ago)', 3 => 'Q3 (Set - Dez)'];

        return [
            'period_label' => "Ano {$year} · {$quarterNames[$quarter]}",
            'period_sublabel' => $selectedIne ? "Equipe INE {$selectedIne} · Coorte & Busca Ativa C3" : 'Consolidado Municipal · Coorte & Busca Ativa C3',
            'denominator' => $den,
            'gestantes_count' => $gestantes,
            'puerperas_count' => $puerperas,
            'encerradas_count' => $encerradas,
            'total_gestantes' => $gestantes,
            'total_puerperas' => $puerperas,
            'total_evaluated_cohort' => $encerradas,
            'practice_kpi' => [
                'A' => ['count' => $pA, 'percent' => $calcPercent($pA)],
                'B' => ['count' => $pB, 'percent' => $calcPercent($pB)],
                'C' => ['count' => $pC, 'percent' => $calcPercent($pC)],
                'D' => ['count' => $pD, 'percent' => $calcPercent($pD)],
                'E' => ['count' => $pE, 'percent' => $calcPercent($pE)],
                'F' => ['count' => $pF, 'percent' => $calcPercent($pF)],
                'G' => ['count' => $pG, 'percent' => $calcPercent($pG)],
                'H' => ['count' => $pH, 'percent' => $calcPercent($pH)],
                'I' => ['count' => $pI, 'percent' => $calcPercent($pI)],
                'J' => ['count' => $pJ, 'percent' => $calcPercent($pJ)],
                'K' => ['count' => $pK, 'percent' => $calcPercent($pK)],
            ],
            'practice_a' => [
                'count' => $pA,
                'percent' => $calcPercent($pA),
                'label' => 'Captação Precoce (até 12ª sem) · 10 pts',
            ],
            'practice_b' => [
                'count' => $pB,
                'percent' => $calcPercent($pB),
                'label' => '≥ 7 Consultas de Pré-Natal · 9 pts',
            ],
            'practice_c' => [
                'count' => $pC,
                'percent' => $calcPercent($pC),
                'label' => '≥ 7 Aferições de Pressão Arterial · 9 pts',
            ],
            'practice_d' => [
                'count' => $pD,
                'percent' => $calcPercent($pD),
                'label' => '≥ 7 Registros de Peso e Altura · 9 pts',
            ],
            'practice_e' => [
                'count' => $pE,
                'percent' => $calcPercent($pE),
                'label' => '≥ 3 Visitas Domiciliares ACS · 9 pts',
            ],
            'practice_f' => [
                'count' => $pF,
                'percent' => $calcPercent($pF),
                'label' => 'Vacina dTpa (a partir da 20ª sem) · 9 pts',
            ],
            'practice_g' => [
                'count' => $pG,
                'percent' => $calcPercent($pG),
                'label' => 'Exames 1º Trim (Sífilis, HIV, Hep B/C) · 9 pts',
            ],
            'practice_h' => [
                'count' => $pH,
                'percent' => $calcPercent($pH),
                'label' => 'Exames 3º Trim (Sífilis e HIV) · 9 pts',
            ],
            'practice_i' => [
                'count' => $pI,
                'percent' => $calcPercent($pI),
                'label' => 'Consulta no Puerpério (até 42d) · 9 pts',
            ],
            'practice_j' => [
                'count' => $pJ,
                'percent' => $calcPercent($pJ),
                'label' => 'Visita ACS no Puerpério (até 42d) · 9 pts',
            ],
            'practice_k' => [
                'count' => $pK,
                'percent' => $calcPercent($pK),
                'label' => 'Saúde Bucal / Odonto na Gestação · 9 pts',
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

        // 1. Equipes
        $teams = [];
        try {
            $eligible = app(FamilyHealthService::class)->getEligibleTeams($baseYear, $baseQuarter);
            foreach ($eligible as $et) {
                $teams[$et['ine']] = [
                    'ine' => $et['ine'],
                    'name' => $et['ine'].' · '.$et['name'],
                ];
            }
        } catch (\Throwable) {
        }

        try {
            $nominalTeams = C3NominalPregnancy::query()
                ->select('ine', 'team_name')
                ->whereNotNull('ine')
                ->distinct()
                ->get();
            foreach ($nominalTeams as $nt) {
                if (! isset($teams[$nt->ine])) {
                    $teams[$nt->ine] = [
                        'ine' => $nt->ine,
                        'name' => $nt->ine.' · '.($nt->team_name ?: 'Equipe eSF'),
                    ];
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
                'value' => $m->format('Y-m'),
                'label' => $m->translatedFormat('F/Y')." ({$m->year}/Q{$qNum})",
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

        return [
            'teams' => array_values($teams),
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
