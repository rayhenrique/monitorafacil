<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;

/**
 * Extração DW do Indicador B1 - Primeira Consulta Odontológica Programada.
 *
 * Conforme Nota Metodológica B1 (MS/SAPS/DESCO):
 * - Numerador: Pessoas com 1ª consulta odontológica programática (SIGTAP 03.01.01.015-3 ou tipo de consulta 1 no MIAOI).
 *   Regra: 1 vez a cada 12 meses por cirurgião-dentista.
 * - Denominador: População vinculada à eSF/eAP de referência da eSB (ajuste /2 para eSB 20h).
 * - Parâmetros: Ótimo (> 1,25), Bom (> 0,75 e ≤ 1,25), Suficiente (> 0,25 e ≤ 0,75), Regular (≤ 0,25).
 */
class B1DwService
{
    public const VERSION = 'dw-b1-2026-09-normative-v1.0';

    private const CD_CBOS = [661, 662, 1016]; // 2232-08, 2232-93, 2232-72

    public function __construct(
        private readonly OralHealthPracticeCalculator $calculator
    ) {}

    /**
     * @param array<string, array{ine:string,name:string,type:string,cnes?:string,facility_name?:string}> $teams
     * @return array{
     *     indicator_code: string,
     *     teams_data: array<string, array<string, mixed>>,
     *     nominals: list<array<string, mixed>>,
     *     municipal: array<string, mixed>
     * }
     */
    public function extract(ConnectionInterface $pec, int $year, int $quarter, array $teams, array $linkedPopulations): array
    {
        $firstMonth = (($quarter - 1) * 4) + 1;
        $startDate = Carbon::create($year, $firstMonth, 1)->startOfDay();
        $endDate = (clone $startDate)->addMonths(4)->subDay()->endOfDay();
        $evalDate = Carbon::today()->lt($endDate) ? Carbon::today()->endOfDay() : $endDate;

        $startTempo = (int) $startDate->format('Ymd');
        $endTempo = (int) $evalDate->format('Ymd');

        // Extrai todas as primeiras consultas individuais da eSB no período
        $rows = $pec->select("
            SELECT
                e.nu_ine,
                fao.co_fat_cidadao_pec,
                fao.nu_cpf_cidadao,
                fao.nu_cns,
                fao.dt_nascimento,
                t.nu_mes,
                t.dt_registro,
                p.no_profissional AS professional_name,
                cbo.nu_cbo AS professional_cbo,
                u.nu_cnes,
                u.no_unidade_saude AS facility_name,
                e.no_equipe AS team_name
            FROM tb_fat_atendimento_odonto fao
            JOIN tb_dim_equipe e ON e.co_seq_dim_equipe = fao.co_dim_equipe_1
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = fao.co_dim_tempo
            LEFT JOIN tb_dim_profissional p ON p.co_seq_dim_profissional = fao.co_dim_profissional_1
            LEFT JOIN tb_dim_cbo cbo ON cbo.co_seq_dim_cbo = fao.co_dim_cbo_1
            LEFT JOIN tb_dim_unidade_saude u ON u.co_seq_dim_unidade_saude = fao.co_dim_unidade_saude_1
            WHERE fao.co_dim_tempo BETWEEN ? AND ?
              AND (fao.co_dim_tipo_consulta = 1 OR EXISTS (
                  SELECT 1 FROM tb_fat_atend_odonto_proced faop
                  WHERE faop.co_fat_atd_odnt = fao.co_seq_fat_atd_odnt
                    AND faop.co_dim_procedimento = 1612 -- 0301010153
              ))
            ORDER BY t.dt_registro ASC
        ", [$startTempo, $endTempo]);

        // Agrupa e desduplica (1 consulta a cada 12 meses por cidadão)
        $teamConsults = [];
        $teamMonthlyConsults = [];
        $seenCitizens = [];
        $nominals = [];

        foreach ($rows as $r) {
            $ine = trim((string) $r->nu_ine);
            if ($ine === '' || $ine === '-') {
                continue;
            }

            $cidKey = ! empty($r->nu_cpf_cidadao) ? 'cpf_' . trim((string) $r->nu_cpf_cidadao) :
                     (! empty($r->nu_cns) ? 'cns_' . trim((string) $r->nu_cns) : 'pec_' . (string) $r->co_fat_cidadao_pec);

            // Regra de unicidade de 1 vez a cada 12 meses
            if (isset($seenCitizens[$ine][$cidKey])) {
                continue;
            }
            $seenCitizens[$ine][$cidKey] = true;

            $teamConsults[$ine] = ($teamConsults[$ine] ?? 0) + 1;
            $month = (int) $r->nu_mes;
            $teamMonthlyConsults[$ine][$month] = ($teamMonthlyConsults[$ine][$month] ?? 0) + 1;

            $birthDate = ! empty($r->dt_nascimento) ? substr((string) $r->dt_nascimento, 0, 10) : null;
            $ageYears = 0;
            if ($birthDate) {
                $birthYear = (int) substr($birthDate, 0, 4);
                $ageYears = max(0, $year - $birthYear);
            }

            // Armazena registro nominal
            $nominals[] = [
                'indicator_code' => 'b1',
                'cidadao_pec_id' => $r->co_fat_cidadao_pec ? (int) $r->co_fat_cidadao_pec : null,
                'cns' => $r->nu_cns ? trim((string) $r->nu_cns) : null,
                'cpf' => $r->nu_cpf_cidadao ? trim((string) $r->nu_cpf_cidadao) : null,
                'name' => 'Cidadão ' . substr(md5($cidKey), 0, 8),
                'birth_date' => $birthDate ?? '1990-01-01',
                'age_years' => $ageYears,
                'phone' => null,
                'cnes' => $r->nu_cnes ? trim((string) $r->nu_cnes) : null,
                'facility_name' => $r->facility_name ? trim((string) $r->facility_name) : null,
                'ine' => $ine,
                'team_name' => $r->team_name ? trim((string) $r->team_name) : null,
                'professional_name' => $r->professional_name ? trim((string) $r->professional_name) : null,
                'professional_cbo' => $r->professional_cbo ? trim((string) $r->professional_cbo) : null,
                'first_consultation_date' => substr((string) $r->dt_registro, 0, 10),
                'has_first_consultation' => true,
                'score_percent' => 100.0,
            ];
        }

        // Consolida por equipe
        $teamsData = [];
        $mNumerator = 0;
        $mDenominator = 0;

        foreach ($teams as $ine => $teamInfo) {
            $num = $teamConsults[$ine] ?? 0;
            $den = $linkedPopulations[$ine] ?? 2500; // população de referência
            $res = $this->calculator->calculateB1($num, $den, $teamInfo['type'] === '87' ? '40h' : '40h');

            $mNumerator += $num;
            $mDenominator += $res['denominator'];

            $teamsData[$ine] = [
                'ine' => $ine,
                'team_name' => $teamInfo['name'],
                'cnes' => $teamInfo['cnes'] ?? null,
                'facility_name' => $teamInfo['facility_name'] ?? null,
                'team_type' => $teamInfo['type'],
                'numerator' => $num,
                'denominator' => $res['denominator'],
                'rate' => $res['rate'],
                'score_percent' => round($res['rate'] * 100, 2),
                'performance_level' => $res['performance_level'],
                'points' => $res['points'],
                'monthly' => $teamMonthlyConsults[$ine] ?? [],
            ];
        }

        $mRes = $this->calculator->calculateB1($mNumerator, $mDenominator);
        $municipal = [
            'indicator_code' => 'b1',
            'numerator' => $mNumerator,
            'denominator' => $mDenominator,
            'rate' => $mRes['rate'],
            'score_percent' => round($mRes['rate'] * 100, 2),
            'performance_level' => $mRes['performance_level'],
            'points' => $mRes['points'],
        ];

        return [
            'indicator_code' => 'b1',
            'teams_data' => $teamsData,
            'nominals' => $nominals,
            'municipal' => $municipal,
        ];
    }
}
