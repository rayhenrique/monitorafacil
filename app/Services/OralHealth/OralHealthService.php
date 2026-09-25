<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use App\Models\OralHealth\OralHealthIndicatorSnapshot;
use App\Models\OralHealth\OralHealthMonthlySnapshot;
use Illuminate\Support\Collection;

class OralHealthService
{
    /**
     * Retorna a lista com os metadados técnicos oficiais de todos os 6 indicadores B1 a B6.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getIndicatorsMetadata(): array
    {
        return [
            'b1' => [
                'code' => 'B1',
                'slug' => 'b1',
                'short_title' => 'Primeira Consulta',
                'panel_title' => 'Primeira Consulta Odontológica Programada',
                'panel_subtitle' => 'Primeira Consulta Odontológica Programática por eSB',
                'full_title' => 'Primeira Consulta Odontológica Programática realizada pela Equipe de Saúde Bucal',
                'category' => 'Acesso Odontológico',
                'target_population' => 'População vinculada à eSF/eAP de referência da eSB',
                'icon' => 'tooth',
                'color' => 'teal',
                'weight' => 2.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica B1 - Primeira consulta programada.pdf',
                'objective' => 'Mensurar o acesso aos serviços de Saúde Bucal através da primeira consulta odontológica programática realizada pela equipe, com respeito ao intervalo anual por cirurgião-dentista.',
                'numerator_desc' => 'Nº total de pessoas com primeira consulta odontológica programática realizadas pela eSB (SIGTAP 03.01.01.015-3 ou Tipo de Consulta = 1 no MIAOI).',
                'denominator_desc' => 'Nº total de pessoas vinculadas à eSF/eAP de referência da eSB (com ajuste /2 para eSB de carga horária diferenciada 20h).',
                'parameters' => [
                    'regular' => ['min' => 0.0, 'max' => 0.25, 'label' => 'Regular (≤ 0,25)', 'points' => '0,50 pt', 'color' => 'red', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                    'sufficient' => ['min' => 0.2501, 'max' => 0.75, 'label' => 'Suficiente (> 0,25 e ≤ 0,75)', 'points' => '1,00 pt', 'color' => 'yellow', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'good' => ['min' => 0.7501, 'max' => 1.25, 'label' => 'Bom (> 0,75 e ≤ 1,25)', 'points' => '1,50 pt', 'color' => 'green', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'optimal' => ['min' => 1.2501, 'max' => 999.0, 'label' => 'Ótimo (> 1,25)', 'points' => '2,00 pts', 'color' => 'blue', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                ],
                'cbos' => [
                    '2232-08 (Cirurgião-Dentista Clínico Geral)',
                    '2232-93 (Cirurgião-Dentista da ESF)',
                    '2232-72 (Cirurgião-Dentista de Saúde Coletiva)',
                ],
                'sigtaps' => ['03.01.01.015-3 (Primeira consulta odontológica programática)'],
            ],

            'b2' => [
                'code' => 'B2',
                'slug' => 'b2',
                'short_title' => 'Tratamento Concluído',
                'panel_title' => 'Tratamento Odontológico Concluído',
                'panel_subtitle' => 'Proporção de Tratamentos Odontológicos Concluídos pela eSB',
                'full_title' => 'Proporção de Pessoas com Tratamento Odontológico Concluído em até 12 Meses',
                'category' => 'Longitudinalidade e Cuidado',
                'target_population' => 'Pessoas com 1ª consulta odontológica programática no período',
                'icon' => 'check-badge',
                'color' => 'emerald',
                'weight' => 2.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica B2 - Tratamento concluído.pdf',
                'objective' => 'Avaliar a resolutividade do cuidado odontológico e o acompanhamento longitudinal, mensurando os usuários que concluíram o plano de tratamento proposto em até 12 meses.',
                'numerator_desc' => 'Nº total de pessoas com tratamento concluído pela eSB (Conduta: "Tratamento concluído" no MIAOI) em até 12 meses após a 1ª consulta.',
                'denominator_desc' => 'Nº total de pessoas com primeira consulta odontológica programática realizadas pela eSB.',
                'parameters' => [
                    'regular' => ['min' => 0.0, 'max' => 25.0, 'label' => 'Regular (≤ 25%)', 'points' => '0,50 pt', 'color' => 'red', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                    'sufficient' => ['min' => 25.01, 'max' => 50.0, 'label' => 'Suficiente (> 25% e ≤ 50%)', 'points' => '1,00 pt', 'color' => 'yellow', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'good' => ['min' => 50.01, 'max' => 75.0, 'label' => 'Bom (> 50% e ≤ 75%)', 'points' => '1,50 pt', 'color' => 'green', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'optimal' => ['min' => 75.01, 'max' => 100.0, 'label' => 'Ótimo (> 75% e ≤ 100%)', 'points' => '2,00 pts', 'color' => 'blue', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                ],
                'cbos' => [
                    '2232-08 (Cirurgião-Dentista Clínico Geral)',
                    '2232-93 (Cirurgião-Dentista da ESF)',
                    '2232-72 (Cirurgião-Dentista de Saúde Coletiva)',
                ],
                'sigtaps' => ['Conduta MIAOI: Tratamento Concluído'],
            ],

            'b3' => [
                'code' => 'B3',
                'slug' => 'b3',
                'short_title' => 'Taxa de Exodontia',
                'panel_title' => 'Taxa de Exodontia de Dentes Permanentes',
                'panel_subtitle' => 'Proporção de Exodontias Permanentes sobre Procedimentos Totais',
                'full_title' => 'Taxa de Exodontia de Dentes Permanentes em Relação aos Procedimentos Totais Realizados',
                'category' => 'Perfil Cirúrgico e Mutilador',
                'target_population' => 'Procedimentos odontológicos individuais preventivos, curativos e exodontias',
                'icon' => 'scissors',
                'color' => 'amber',
                'weight' => 2.0,
                'polarity' => 'Menor é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica B3 - Taxa de exodontia.pdf',
                'objective' => 'Monitorar a prática cirúrgico-mutiladora das equipes, estimulando um modelo promotor da saúde focado na preservação dos elementos dentários permanentes.',
                'numerator_desc' => 'Nº total de exodontias de dentes permanentes realizadas por cirurgião-dentista da eSB (SIGTAP 04.14.02.013-8 e 04.14.02.014-6).',
                'denominator_desc' => 'Nº total de procedimentos individuais preventivos, curativos e exodontias realizados pela eSB (Cirurgião-Dentista + TSB).',
                'parameters' => [
                    'optimal' => ['min' => 3.0, 'max' => 9.99, 'label' => 'Ótimo (≥ 3% e < 10%)', 'points' => '2,00 pts', 'color' => 'blue', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                    'good' => ['min' => 10.0, 'max' => 11.99, 'label' => 'Bom (≥ 10% e < 12%)', 'points' => '1,50 pt', 'color' => 'green', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'sufficient' => ['min' => 12.0, 'max' => 13.99, 'label' => 'Suficiente (≥ 12% e < 14%)', 'points' => '1,00 pt', 'color' => 'yellow', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'regular' => ['min' => 0.0, 'max' => 2.99, 'extra' => '≥ 14%', 'label' => 'Regular (< 3% ou ≥ 14%)', 'points' => '0,50 pt', 'color' => 'red', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                ],
                'cbos' => [
                    '2232-08 (CD Clínico Geral)',
                    '2232-93 (CD ESF)',
                    '2232-72 (CD Saúde Coletiva)',
                    '3224-05 (TSB)',
                    '3224-25 (TSB ESF)',
                ],
                'sigtaps' => [
                    '04.14.02.013-8 (Exodontia de dente permanente)',
                    '04.14.02.014-6 (Exodontia múltipla com alveoloplastia)',
                ],
            ],

            'b4' => [
                'code' => 'B4',
                'slug' => 'b4',
                'short_title' => 'Escovação Supervisionada',
                'panel_title' => 'Ação Coletiva de Escovação Dental Supervisionada',
                'panel_subtitle' => 'Cobertura de Crianças de 6 a 12 Anos em Escovação Coletiva',
                'full_title' => 'Ação Coletiva de Escovação Dental Supervisionada na Faixa Etária de 6 a 12 Anos',
                'category' => 'Ações Coletivas e Cobertura',
                'target_population' => 'Crianças de 6 a 12 anos vinculadas à eSF/eAP de referência',
                'icon' => 'users',
                'color' => 'blue',
                'weight' => 1.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica B4 - Escovação supervisionada.pdf',
                'objective' => 'Avaliar a realização de atividades coletivas educativas e preventivas de higiene bucal, garantindo o alcance em crianças escolares no território adscrito.',
                'numerator_desc' => 'Nº total de crianças de 6 a 12 anos participantes da ação coletiva de escovação supervisionada realizada pela eSB (Ficha Atividade Coletiva, prática 04, SIGTAP 01.01.02.003-1).',
                'denominator_desc' => 'Nº total de crianças de 6 a 12 anos vinculadas à eSF/eAP de referência da eSB.',
                'parameters' => [
                    'regular' => ['min' => 0.0, 'max' => 0.25, 'label' => 'Regular (≤ 0,25)', 'points' => '0,25 pt', 'color' => 'red', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                    'sufficient' => ['min' => 0.2501, 'max' => 0.50, 'label' => 'Suficiente (> 0,25 e ≤ 0,50)', 'points' => '0,50 pt', 'color' => 'yellow', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'good' => ['min' => 0.5001, 'max' => 1.00, 'label' => 'Bom (> 0,50 e ≤ 1,00)', 'points' => '0,75 pt', 'color' => 'green', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'optimal' => ['min' => 1.0001, 'max' => 999.0, 'label' => 'Ótimo (> 1,00)', 'points' => '1,00 pt', 'color' => 'blue', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                ],
                'cbos' => [
                    '2232-08 (CD Clínico Geral)',
                    '2232-93 (CD ESF)',
                    '2232-72 (CD Saúde Coletiva)',
                    '3224-05 (TSB)',
                    '3224-25 (TSB ESF)',
                    '3224-15 (ASB)',
                    '3224-30 (ASB ESF)',
                ],
                'sigtaps' => ['01.01.02.003-1 (Ação coletiva de escovação dental supervisionada)'],
            ],

            'b5' => [
                'code' => 'B5',
                'slug' => 'b5',
                'short_title' => 'Procedimentos Preventivos',
                'panel_title' => 'Procedimentos Odontológicos Preventivos Individuais',
                'panel_subtitle' => 'Proporção de Procedimentos Preventivos sobre Procedimentos Totais',
                'full_title' => 'Procedimentos Odontológicos Individuais Preventivos por Equipe de Saúde Bucal',
                'category' => 'Prevenção e Conservação',
                'target_population' => 'Procedimentos odontológicos individuais realizados pela eSB',
                'icon' => 'shield-check',
                'color' => 'indigo',
                'weight' => 2.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica B5 - Procedimentos odontológicos preventivos.pdf',
                'objective' => 'Mensurar a proporção de procedimentos clínicos preventivos e conservadores (flúor, selante, cariostático, profilaxia) em relação ao total de procedimentos clínicos da equipe.',
                'numerator_desc' => 'Nº total de procedimentos preventivos individuais (flúor, selante, cariostático, evidenciação de placa, profilaxia, orientação de higiene) realizados pela eSB (CD + TSB).',
                'denominator_desc' => 'Nº total de procedimentos odontológicos individuais (preventivos + curativos/cirúrgicos) realizados pela eSB.',
                'parameters' => [
                    'regular' => ['min' => 0.0, 'max' => 39.99, 'extra' => '> 85%', 'label' => 'Regular (< 40% ou > 85%)', 'points' => '0,50 pt', 'color' => 'red', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                    'sufficient' => ['min' => 40.0, 'max' => 54.99, 'label' => 'Suficiente (≥ 40% e < 55%)', 'points' => '1,00 pt', 'color' => 'yellow', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'good' => ['min' => 55.0, 'max' => 64.99, 'label' => 'Bom (≥ 55% e < 65%)', 'points' => '1,50 pt', 'color' => 'green', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'optimal' => ['min' => 65.0, 'max' => 85.0, 'label' => 'Ótimo (≥ 65% e ≤ 85%)', 'points' => '2,00 pts', 'color' => 'blue', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                ],
                'cbos' => [
                    '2232-08 (CD Clínico Geral)',
                    '2232-93 (CD ESF)',
                    '2232-72 (CD Saúde Coletiva)',
                    '3224-05 (TSB)',
                    '3224-25 (TSB ESF)',
                ],
                'sigtaps' => [
                    '01.01.02.005-8 (Cariostático)',
                    '01.01.02.006-6 (Selante)',
                    '01.01.02.007-4 (Flúor tópico)',
                    '01.01.02.008-2 (Evidenciação de placa)',
                    '01.01.02.010-4 (Orientação de higiene bucal)',
                    '01.01.02.012-0 (Higienização de prótese)',
                    '03.07.03.004-0 (Profilaxia/remoção de placa)',
                ],
            ],

            'b6' => [
                'code' => 'B6',
                'slug' => 'b6',
                'short_title' => 'Restauração Atraumática (ART)',
                'panel_title' => 'Tratamento Restaurador Atraumático (ART/TRA)',
                'panel_subtitle' => 'Proporção de Procedimentos ART sobre Procedimentos Restauradores',
                'full_title' => 'Proporção de Tratamento Restaurador Atraumático Realizado pela Equipe de Saúde Bucal',
                'category' => 'Odontologia Minimamente Invasiva',
                'target_population' => 'Procedimentos restauradores individuais realizados pela eSB',
                'icon' => 'sparkles',
                'color' => 'purple',
                'weight' => 1.0,
                'polarity' => 'Maior é melhor',
                'periodicity' => 'Quadrimestral',
                'source_pdf' => 'Nota Metodológica B6 - Tratamento restaurador atraumático.pdf',
                'objective' => 'Avaliar a incorporação de técnicas minimamente invasivas de preservação dentária no manejo da cárie dentária na Atenção Primária à Saúde.',
                'numerator_desc' => 'Nº total de procedimentos "Tratamento Restaurador Atraumático" (TRA/ART - SIGTAP 03.07.01.007-4) realizados pela eSB.',
                'denominator_desc' => 'Nº total de procedimentos restauradores realizados pela eSB (TRA/ART, resina composta e ionômero de vidro).',
                'parameters' => [
                    'regular' => ['min' => 0.0, 'max' => 3.0, 'label' => 'Regular (≤ 3%)', 'points' => '0,25 pt', 'color' => 'red', 'badge' => 'bg-rose-100 text-rose-800 border-rose-300'],
                    'sufficient' => ['min' => 3.01, 'max' => 6.0, 'label' => 'Suficiente (> 3% e ≤ 6%)', 'points' => '0,50 pt', 'color' => 'yellow', 'badge' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'good' => ['min' => 6.01, 'max' => 8.0, 'label' => 'Bom (> 6% e ≤ 8%)', 'points' => '0,75 pt', 'color' => 'green', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'optimal' => ['min' => 8.01, 'max' => 100.0, 'label' => 'Ótimo (> 8%)', 'points' => '1,00 pt', 'color' => 'blue', 'badge' => 'bg-sky-100 text-sky-800 border-sky-300'],
                ],
                'cbos' => [
                    '2232-08 (CD Clínico Geral)',
                    '2232-93 (CD ESF)',
                    '2232-72 (CD Saúde Coletiva)',
                ],
                'sigtaps' => [
                    '03.07.01.007-4 (Tratamento Restaurador Atraumático - TRA/ART)',
                    '03.07.01.003-1 (Restauração permanente anterior resina)',
                    '03.07.01.008-2 (Restauração decíduo posterior resina)',
                    '03.07.01.010-4 (Restauração decíduo posterior ionômero)',
                    '03.07.01.011-2 (Restauração decíduo anterior resina)',
                    '03.07.01.012-0 (Restauração permanente posterior resina)',
                ],
            ],
        ];
    }

    /**
     * Retorna os metadados de um indicador de Saúde Bucal específico.
     *
     * @return array<string, mixed>|null
     */
    public static function getIndicatorMeta(string $slug): ?array
    {
        $all = self::getIndicatorsMetadata();

        return $all[strtolower($slug)] ?? null;
    }

    /**
     * Retorna a visão geral municipal de todos os indicadores B1 a B6 para o quadrimestre.
     *
     * @return array<string, mixed>
     */
    public function getMunicipalOverview(int $year, int $quarter): array
    {
        $indicatorsMeta = self::getIndicatorsMetadata();

        $snapshots = OralHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNull('ine') // Consolidado municipal
            ->get()
            ->keyBy(fn ($item) => strtolower($item->indicator_code));

        $teamSnapshots = OralHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNotNull('ine') // Snapshots individuais por equipe eSB
            ->where(function ($q): void {
                $q->where('team_name', 'like', 'ESB%')
                    ->orWhere('team_name', 'like', '%SAUDE BUCAL%')
                    ->orWhere('team_name', 'like', '%Saúde Bucal%')
                    ->orWhere('team_type', '88');
            })
            ->where('team_name', 'not like', 'ESF%')
            ->where('team_name', 'not like', 'USF%')
            ->get()
            ->groupBy(fn ($item) => strtolower($item->indicator_code));

        $cards = [];
        $totalWeightedScore = 0.0;
        $totalWeight = 0.0;

        $calculator = new OralHealthPracticeCalculator();

        foreach ($indicatorsMeta as $slug => $meta) {
            $snap = $snapshots->get($slug);
            $score = $snap ? (float) $snap->score_percent : null;
            $level = $snap ? $snap->performance_level : null;
            $points = $level ? $calculator->getScorePoints($slug, $level) : 0.0;

            $weight = (float) ($meta['weight'] ?? 1.0);
            $totalWeight += $weight;
            $totalWeightedScore += $points;

            // Agregação real das classificações de equipes
            /** @var Collection<int, OralHealthIndicatorSnapshot> $teamsForIndicator */
            $teamsForIndicator = $teamSnapshots->get($slug, collect());

            $classifications = [
                'otimo' => 0,
                'bom' => 0,
                'suficiente' => 0,
                'regular' => 0,
                'total' => 0,
            ];

            foreach ($teamsForIndicator as $teamSnap) {
                $rawLevel = $teamSnap->performance_level ?: $calculator->getPerformanceLevel($slug, (float) $teamSnap->score_percent);
                $normalized = match (strtolower((string) $rawLevel)) {
                    'otimo', 'optimal' => 'otimo',
                    'bom', 'good' => 'bom',
                    'suficiente', 'sufficient' => 'suficiente',
                    default => 'regular',
                };
                if (isset($classifications[$normalized])) {
                    $classifications[$normalized]++;
                    $classifications['total']++;
                }
            }

            $cards[$slug] = [
                'meta' => $meta,
                'snapshot' => $snap,
                'score' => $score,
                'points' => $points,
                'level' => $level,
                'teams_count' => $classifications['total'],
                'classifications' => $classifications,
                'numerator' => $snap?->numerator ?? 0,
                'denominator' => $snap?->denominator ?? 0,
            ];
        }

        $municipalAverageScore = $totalWeight > 0 ? round(($totalWeightedScore / $totalWeight) * 10, 2) : 0.0;
        $municipalClassification = match (true) {
            $municipalAverageScore >= 7.5 => 'otimo',
            $municipalAverageScore >= 5.0 => 'bom',
            $municipalAverageScore >= 2.5 => 'suficiente',
            default => 'regular',
        };

        return [
            'year' => $year,
            'quarter' => $quarter,
            'cards' => $cards,
            'municipal_score' => $totalWeightedScore, // soma dos pontos (máximo 10)
            'municipal_average' => $municipalAverageScore,
            'municipal_classification' => $municipalClassification,
            'total_weight' => $totalWeight,
            'has_data' => $snapshots->isNotEmpty(),
        ];
    }

    /**
     * Retorna o resumo quadrimestral e evolução mensal para uma equipe ou município.
     *
     * @return array<string, mixed>
     */
    public function getIndicatorData(string $indicator, int $year, int $quarter, ?string $ine = null): array
    {
        $indicator = strtolower($indicator);
        $calculator = new OralHealthPracticeCalculator();

        // 1. Snapshot da equipe ou município
        $snapQuery = OralHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('indicator_code', $indicator);

        if ($ine !== null && $ine !== '') {
            $snapQuery->where('ine', $ine);
        } else {
            $snapQuery->whereNull('ine');
        }

        $snapshot = $snapQuery->first();

        // 2. Snapshots de todas as equipes eSB para distribuição de faixas
        $allTeamSnapshots = OralHealthIndicatorSnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('indicator_code', $indicator)
            ->whereNotNull('ine')
            ->where(function ($q): void {
                $q->where('team_name', 'like', 'ESB%')
                    ->orWhere('team_name', 'like', '%SAUDE BUCAL%')
                    ->orWhere('team_name', 'like', '%Saúde Bucal%')
                    ->orWhere('team_type', '88');
            })
            ->where('team_name', 'not like', 'ESF%')
            ->where('team_name', 'not like', 'USF%')
            ->orderBy('team_name')
            ->get();

        $teamClassifications = [
            'otimo' => 0,
            'bom' => 0,
            'suficiente' => 0,
            'regular' => 0,
            'total' => 0,
        ];

        foreach ($allTeamSnapshots as $t) {
            $lvl = $t->performance_level ?: $calculator->getPerformanceLevel($indicator, (float) $t->score_percent);
            $normalized = match (strtolower((string) $lvl)) {
                'otimo', 'optimal' => 'otimo',
                'bom', 'good' => 'bom',
                'suficiente', 'sufficient' => 'suficiente',
                default => 'regular',
            };
            $teamClassifications[$normalized]++;
            $teamClassifications['total']++;
        }

        // 3. Evolução mensal
        $monthlyQuery = OralHealthMonthlySnapshot::query()
            ->where('year', $year)
            ->where('quarter', $quarter)
            ->where('indicator_code', $indicator);

        if ($ine !== null && $ine !== '') {
            $monthlyQuery->where('ine', $ine);
        } else {
            $monthlyQuery->whereNull('ine');
        }

        $monthlyEvolution = $monthlyQuery->orderBy('month')->get();

        return [
            'snapshot' => $snapshot,
            'all_teams' => $allTeamSnapshots,
            'team_classifications' => $teamClassifications,
            'monthly_evolution' => $monthlyEvolution,
        ];
    }
}
