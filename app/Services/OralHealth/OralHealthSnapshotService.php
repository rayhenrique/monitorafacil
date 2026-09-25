<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use App\Models\OralHealth\OralHealthIndicatorSnapshot;
use App\Models\OralHealth\OralHealthMonthlySnapshot;
use App\Models\OralHealth\OralHealthNominalPatient;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class OralHealthSnapshotService
{
    public function __construct(
        private readonly B1DwService $b1Dw,
        private readonly B2DwService $b2Dw,
        private readonly B3DwService $b3Dw,
        private readonly B4DwService $b4Dw,
        private readonly B5DwService $b5Dw,
        private readonly B6DwService $b6Dw,
        private readonly OralHealthPracticeCalculator $calculator
    ) {}

    /**
     * Processa e persiste os snapshots de Saúde Bucal para o quadrimestre.
     *
     * @return array{
     *     year: int,
     *     quarter: int,
     *     teams_count: int,
     *     indicators_processed: int,
     *     nominals_count: int
     * }
     */
    public function process(ConnectionInterface $pec, int $year, int $quarter): array
    {
        // 1. Carrega todas as equipes de Saúde Bucal ativas no PEC
        $teams = $this->loadOralHealthTeams($pec);
        $linkedPopulations = $this->loadLinkedPopulations($pec, $teams);
        $linkedChildrenPopulations = $this->loadLinkedChildrenPopulations($pec, $teams);

        // 2. Extrai cada um dos 6 indicadores
        $b1Data = $this->b1Dw->extract($pec, $year, $quarter, $teams, $linkedPopulations);
        $b2Data = $this->b2Dw->extract($pec, $year, $quarter, $teams);
        $b3Data = $this->b3Dw->extract($pec, $year, $quarter, $teams);
        $b4Data = $this->b4Dw->extract($pec, $year, $quarter, $teams, $linkedChildrenPopulations);
        $b5Data = $this->b5Dw->extract($pec, $year, $quarter, $teams);
        $b6Data = $this->b6Dw->extract($pec, $year, $quarter, $teams);

        $indicatorsResults = [
            'b1' => $b1Data,
            'b2' => $b2Data,
            'b3' => $b3Data,
            'b4' => $b4Data,
            'b5' => $b5Data,
            'b6' => $b6Data,
        ];

        $totalNominals = 0;

        // 3. Persistência transacional segura no MySQL local
        DB::transaction(function () use ($year, $quarter, $teams, $indicatorsResults, &$totalNominals): void {
            // Limpa dados prévios da mesma competência
            OralHealthIndicatorSnapshot::query()
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->delete();

            OralHealthMonthlySnapshot::query()
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->delete();

            OralHealthNominalPatient::query()
                ->where('year', $year)
                ->where('quarter', $quarter)
                ->delete();

            // Grava snapshots de cada indicador
            foreach ($indicatorsResults as $code => $indData) {
                // Snapshot municipal (ine = null)
                $m = $indData['municipal'];
                OralHealthIndicatorSnapshot::create([
                    'year' => $year,
                    'quarter' => $quarter,
                    'ine' => null,
                    'team_name' => 'Consolidado Municipal',
                    'cnes' => null,
                    'facility_name' => 'Secretaria Municipal de Saúde',
                    'team_type' => '88',
                    'indicator_code' => $code,
                    'numerator' => (int) $m['numerator'],
                    'denominator' => (int) $m['denominator'],
                    'score_percent' => (float) $m['score_percent'],
                    'performance_level' => (string) $m['performance_level'],
                    'good_practices_breakdown' => [
                        'points' => (float) $m['points'],
                        'weight' => (float) (OralHealthPracticeCalculator::WEIGHTS[$code] ?? 1.0),
                    ],
                ]);

                // Snapshots individuais por equipe
                foreach ($indData['teams_data'] as $ine => $t) {
                    OralHealthIndicatorSnapshot::create([
                        'year' => $year,
                        'quarter' => $quarter,
                        'ine' => $ine,
                        'team_name' => $t['team_name'],
                        'cnes' => $t['cnes'],
                        'facility_name' => $t['facility_name'],
                        'team_type' => $t['team_type'],
                        'indicator_code' => $code,
                        'numerator' => (int) $t['numerator'],
                        'denominator' => (int) $t['denominator'],
                        'score_percent' => (float) $t['score_percent'],
                        'performance_level' => (string) $t['performance_level'],
                        'good_practices_breakdown' => [
                            'points' => (float) $t['points'],
                            'weight' => (float) (OralHealthPracticeCalculator::WEIGHTS[$code] ?? 1.0),
                        ],
                    ]);

                    // Evolução mensal da equipe
                    $firstMonth = (($quarter - 1) * 4) + 1;
                    for ($mIndex = 1; $mIndex <= 4; $mIndex++) {
                        $actualMonth = $firstMonth + $mIndex - 1;
                        $mNum = (int) ($t['monthly'][$actualMonth] ?? $t['monthly_num'][$actualMonth] ?? 0);
                        $mDen = (int) ($t['monthly_den'][$actualMonth] ?? $t['denominator']);
                        $mRate = $mDen > 0 ? round(($mNum / $mDen) * 100, 2) : 0.0;
                        $mLevel = $this->calculator->getPerformanceLevel($code, $mRate);

                        OralHealthMonthlySnapshot::create([
                            'year' => $year,
                            'month' => $actualMonth,
                            'quarter' => $quarter,
                            'month_in_quarter' => $mIndex,
                            'ine' => $ine,
                            'team_name' => $t['team_name'],
                            'cnes' => $t['cnes'],
                            'facility_name' => $t['facility_name'],
                            'team_type' => $t['team_type'],
                            'indicator_code' => $code,
                            'numerator' => $mNum,
                            'denominator' => $mDen,
                            'score_percent' => $mRate,
                            'performance_level' => $mLevel,
                        ]);
                    }
                }

                // Persiste nominais em chunks
                $nominals = $indData['nominals'] ?? [];
                if (! empty($nominals)) {
                    $totalNominals += count($nominals);
                    foreach (array_chunk($nominals, 100) as $chunk) {
                        $records = array_map(static fn (array $d) => array_merge($d, [
                            'year' => $year,
                            'quarter' => $quarter,
                            'calculation_version' => 'dw-oral-2026-09-normative-v1.0',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]), $chunk);

                        OralHealthNominalPatient::query()->insert($records);
                    }
                }
            }
        });

        return [
            'year' => $year,
            'quarter' => $quarter,
            'teams_count' => count($teams),
            'indicators_processed' => 6,
            'nominals_count' => $totalNominals,
        ];
    }

    /**
     * Carrega estritamente as Equipes de Saúde Bucal (eSB), excluindo equipes de Saúde da Família (eSF), NASF, EMAD, etc.
     *
     * @return array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}>
     */
    private function loadOralHealthTeams(ConnectionInterface $pec): array
    {
        $rows = $pec->select("
            SELECT
                e.nu_ine AS ine,
                e.no_equipe AS name,
                e.tp_equipe AS type,
                u.nu_cnes AS cnes,
                u.no_unidade_saude AS facility_name
            FROM tb_equipe e
            LEFT JOIN tb_unidade_saude u ON u.co_seq_unidade_saude = e.co_unidade_saude
            WHERE (e.tp_equipe = '88' OR e.no_equipe ILIKE 'ESB %' OR e.no_equipe ILIKE '%SAUDE BUCAL%')
              AND e.no_equipe NOT ILIKE 'ESF %'
              AND e.no_equipe NOT ILIKE 'USF %'
              AND e.no_equipe NOT ILIKE 'PACS%'
              AND e.no_equipe NOT ILIKE '%E-MULTI%'
              AND e.no_equipe NOT ILIKE '%NASF%'
              AND e.no_equipe NOT ILIKE '%EMAD%'
              AND e.no_equipe NOT ILIKE '%EMAP%'
              AND e.nu_ine IS NOT NULL
              AND e.nu_ine <> ''
              AND e.nu_ine <> '-'
            ORDER BY e.no_equipe
        ");

        $teams = [];
        foreach ($rows as $r) {
            $ine = trim((string) $r->ine);
            $teams[$ine] = [
                'ine' => $ine,
                'name' => trim((string) $r->name),
                'type' => (string) $r->type,
                'cnes' => $r->cnes ? trim((string) $r->cnes) : null,
                'facility_name' => $r->facility_name ? trim((string) $r->facility_name) : null,
            ];
        }

        return $teams;
    }

    /**
     * @param array<string, array<string, mixed>> $teams
     * @return array<string, int>
     */
    private function loadLinkedPopulations(ConnectionInterface $pec, array $teams): array
    {
        $pop = [];
        $cnesList = array_unique(array_filter(array_column($teams, 'cnes')));

        if (! empty($cnesList)) {
            $marks = implode(',', array_fill(0, count($cnesList), '?'));
            $rows = $pec->select("
                SELECT nu_cnes_vinc_equipe AS cnes, count(*) AS total
                FROM tb_acomp_cidadaos_vinculados
                WHERE nu_cnes_vinc_equipe IN ({$marks})
                GROUP BY nu_cnes_vinc_equipe
            ", array_values($cnesList));

            $cnesPop = [];
            foreach ($rows as $r) {
                $cnesPop[trim((string) $r->cnes)] = (int) $r->total;
            }

            foreach ($teams as $ine => $t) {
                $c = $t['cnes'] ?? null;
                $pop[$ine] = ($c && isset($cnesPop[$c])) ? max(100, $cnesPop[$c]) : 2500;
            }
        } else {
            foreach ($teams as $ine => $t) {
                $pop[$ine] = 2500;
            }
        }

        return $pop;
    }

    /**
     * @param array<string, array<string, mixed>> $teams
     * @return array<string, int>
     */
    private function loadLinkedChildrenPopulations(ConnectionInterface $pec, array $teams): array
    {
        $pop = [];
        $cnesList = array_unique(array_filter(array_column($teams, 'cnes')));

        if (! empty($cnesList)) {
            $marks = implode(',', array_fill(0, count($cnesList), '?'));
            // Crianças de 6 a 12 anos
            $currentYear = (int) date('Y');
            $rows = $pec->select("
                SELECT nu_cnes_vinc_equipe AS cnes, count(*) AS total
                FROM tb_acomp_cidadaos_vinculados
                WHERE nu_cnes_vinc_equipe IN ({$marks})
                  AND ({$currentYear} - EXTRACT(YEAR FROM dt_nascimento_cidadao)) BETWEEN 6 AND 12
                GROUP BY nu_cnes_vinc_equipe
            ", array_values($cnesList));

            $cnesPop = [];
            foreach ($rows as $r) {
                $cnesPop[trim((string) $r->cnes)] = (int) $r->total;
            }

            foreach ($teams as $ine => $t) {
                $c = $t['cnes'] ?? null;
                $pop[$ine] = ($c && isset($cnesPop[$c])) ? max(20, $cnesPop[$c]) : 350;
            }
        } else {
            foreach ($teams as $ine => $t) {
                $pop[$ine] = 350;
            }
        }

        return $pop;
    }
}
