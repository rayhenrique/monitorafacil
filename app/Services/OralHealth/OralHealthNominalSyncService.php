<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use App\Models\CvatNominalCitizen;
use App\Models\OralHealth\OralHealthNominalCitizen;
use App\Models\OralHealth\OralHealthNominalPatient;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class OralHealthNominalSyncService
{
    /**
     * Sincroniza e consolida a Relação Nominal Geral de Saúde Bucal para o quadrimestre.
     *
     * @return array{
     *     year: int,
     *     quarter: int,
     *     citizens_processed: int,
     *     dental_attendances_found: int
     * }
     */
    public function sync(?ConnectionInterface $pec, int $year, int $quarter): array
    {
        $firstMonth = (($quarter - 1) * 4) + 1;
        $lastMonth = $firstMonth + 3;

        $dentalMap = [];

        if ($pec !== null) {
            $dentalMap = $this->extractDentalMetricsFromDw($pec, $year, $firstMonth, $lastMonth);
        } else {
            $dentalMap = $this->extractDentalMetricsFromLocalPatients($year, $quarter);
        }

        // Limpa registros anteriores deste período
        OralHealthNominalCitizen::where('year', $year)->where('quarter', $quarter)->delete();

        // Obtém cidadãos da base territorial municipal
        $citizensQuery = CvatNominalCitizen::query()
            ->orderBy('id');

        $now = now();
        $batch = [];
        $totalProcessed = 0;

        foreach ($citizensQuery->cursor() as $c) {
            $pecId = (int) $c->cidadao_pec_id;
            $d = $dentalMap[$pecId] ?? null;

            $b1Count = (int) ($d['b1_count'] ?? 0);
            $b2Count = (int) ($d['b2_count'] ?? 0);
            $b3Count = (int) ($d['b3_count'] ?? 0);
            $b4Count = (int) ($d['b4_count'] ?? 0);
            $b5Count = (int) ($d['b5_count'] ?? 0);
            $b6Count = (int) ($d['b6_count'] ?? 0);

            $age = (int) ($c->age ?? 0);
            $b4Eligible = ($age >= 6 && $age <= 12);

            $status = 'nao_iniciado';
            if ($b2Count > 0) {
                $status = 'concluido';
            } elseif ($b1Count > 0 || ($b5Count + $b6Count + $b3Count) > 0) {
                $status = 'em_andamento';
            }

            $batch[] = [
                'year' => $year,
                'quarter' => $quarter,
                'month' => (int) ($c->month ?? 9),
                'cidadao_pec_id' => $pecId,
                'cns' => $c->cns ? trim((string) $c->cns) : null,
                'cpf' => $c->cpf ? trim((string) $c->cpf) : null,
                'name' => $c->name ?: 'CIDADÃO NÃO INFORMADO',
                'mother_name' => null,
                'birth_date' => $c->birth_date ? Carbon::parse($c->birth_date)->toDateString() : null,
                'age_years' => $age,
                'gender' => $c->gender ? (string) $c->gender : null,
                'race_color' => $c->race_color ? (string) $c->race_color : null,
                'cnes' => $c->cnes ? trim((string) $c->cnes) : null,
                'facility_name' => $c->facility_name ? trim((string) $c->facility_name) : null,
                'ine' => $c->ine ? trim((string) $c->ine) : null,
                'team_name' => $c->team_name ? trim((string) $c->team_name) : null,
                'microarea' => $c->microarea ? trim((string) $c->microarea) : '00',
                'district' => 'Distrito Municipal',
                'professional_cns' => $c->professional_cns ? trim((string) $c->professional_cns) : ($d['last_prof_cns'] ?? null),
                'professional_name' => $c->professional_name ? trim((string) $c->professional_name) : ($d['last_prof_name'] ?? null),
                'mici_updated' => (bool) $c->mici_updated,
                'micdt_updated' => (bool) $c->micdt_updated,
                'is_linked' => (bool) $c->is_linked,
                'b1_count' => $b1Count,
                'b2_count' => $b2Count,
                'b3_count' => $b3Count,
                'b4_count' => $b4Count,
                'b4_eligible' => $b4Eligible,
                'b5_count' => $b5Count,
                'b6_count' => $b6Count,
                'first_consultation_date' => $d['first_consult_date'] ?? null,
                'treatment_completed_date' => $d['treatment_completed_date'] ?? null,
                'last_brushing_date' => $d['last_brushing_date'] ?? null,
                'last_attendance_date' => $d['last_attendance_date'] ?? null,
                'last_professional_name' => $d['last_prof_name'] ?? null,
                'last_professional_cbo' => $d['last_prof_cbo'] ?? null,
                'treatment_status' => $status,
                'calculation_version' => 'dw-oral-general-2026-v1.0',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 500) {
                OralHealthNominalCitizen::insert($batch);
                $totalProcessed += count($batch);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            OralHealthNominalCitizen::insert($batch);
            $totalProcessed += count($batch);
        }

        return [
            'year' => $year,
            'quarter' => $quarter,
            'citizens_processed' => $totalProcessed,
            'dental_attendances_found' => count($dentalMap),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractDentalMetricsFromDw(ConnectionInterface $pec, int $year, int $firstMonth, int $lastMonth): array
    {
        $map = [];

        // 1. Atendimentos odontológicos (B1 e B2)
        $atendimentos = $pec->select("
            SELECT
                f.co_fat_cidadao_pec AS pec_id,
                SUM(CASE WHEN f.co_dim_tipo_consulta = 1 OR f.nu_atendimento = 1 THEN 1 ELSE 0 END) AS b1_count,
                SUM(CASE WHEN f.co_dim_tipo_atendimento = 4 THEN 1 ELSE 0 END) AS b2_count,
                MIN(CASE WHEN f.co_dim_tipo_consulta = 1 OR f.nu_atendimento = 1 THEN t.dt_registro ELSE NULL END) AS first_consult_date,
                MAX(CASE WHEN f.co_dim_tipo_atendimento = 4 THEN t.dt_registro ELSE NULL END) AS treatment_completed_date,
                MAX(t.dt_registro) AS last_attendance_date,
                MAX(p.no_profissional) AS last_prof_name,
                MAX(cbo.nu_cbo) AS last_prof_cbo
            FROM tb_fat_atendimento_odonto f
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = f.co_dim_tempo
            LEFT JOIN tb_dim_profissional p ON p.co_seq_dim_profissional = f.co_dim_profissional_1
            LEFT JOIN tb_dim_cbo cbo ON cbo.co_seq_dim_cbo = f.co_dim_cbo_1
            WHERE t.nu_ano = ? AND t.nu_mes BETWEEN ? AND ? AND f.co_fat_cidadao_pec IS NOT NULL
            GROUP BY f.co_fat_cidadao_pec
        ", [$year, $firstMonth, $lastMonth]);

        foreach ($atendimentos as $row) {
            $id = (int) $row->pec_id;
            $map[$id] = [
                'b1_count' => (int) $row->b1_count,
                'b2_count' => (int) $row->b2_count,
                'b3_count' => 0,
                'b4_count' => 0,
                'b5_count' => 0,
                'b6_count' => 0,
                'first_consult_date' => $row->first_consult_date ? (string) $row->first_consult_date : null,
                'treatment_completed_date' => $row->treatment_completed_date ? (string) $row->treatment_completed_date : null,
                'last_attendance_date' => $row->last_attendance_date ? (string) $row->last_attendance_date : null,
                'last_prof_name' => $row->last_prof_name ? trim((string) $row->last_prof_name) : null,
                'last_prof_cbo' => $row->last_prof_cbo ? trim((string) $row->last_prof_cbo) : null,
                'last_prof_cns' => null,
                'last_brushing_date' => null,
            ];
        }

        // 2. Procedimentos odontológicos (B3, B5, B6)
        $procedimentos = $pec->select("
            SELECT
                p.co_fat_cidadao_pec AS pec_id,
                SUM(CASE WHEN p.co_dim_procedimento IN (1604, 2065) THEN p.qt_procedimentos ELSE 0 END) AS b3_count,
                SUM(CASE WHEN p.co_dim_procedimento IN (1279, 1335, 1374, 1591, 1592, 1618, 1622) THEN p.qt_procedimentos ELSE 0 END) AS b5_count,
                SUM(CASE WHEN p.co_dim_procedimento IN (1374, 1592) THEN p.qt_procedimentos ELSE 0 END) AS b6_count
            FROM tb_fat_atend_odonto_proced p
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = p.co_dim_tempo
            WHERE t.nu_ano = ? AND t.nu_mes BETWEEN ? AND ? AND p.co_fat_cidadao_pec IS NOT NULL
            GROUP BY p.co_fat_cidadao_pec
        ", [$year, $firstMonth, $lastMonth]);

        foreach ($procedimentos as $row) {
            $id = (int) $row->pec_id;
            if (! isset($map[$id])) {
                $map[$id] = [
                    'b1_count' => 0,
                    'b2_count' => 0,
                    'b3_count' => 0,
                    'b4_count' => 0,
                    'b5_count' => 0,
                    'b6_count' => 0,
                    'first_consult_date' => null,
                    'treatment_completed_date' => null,
                    'last_attendance_date' => null,
                    'last_prof_name' => null,
                    'last_prof_cbo' => null,
                    'last_prof_cns' => null,
                    'last_brushing_date' => null,
                ];
            }
            $map[$id]['b3_count'] = (int) $row->b3_count;
            $map[$id]['b5_count'] = (int) $row->b5_count;
            $map[$id]['b6_count'] = (int) $row->b6_count;
        }

        // 3. Atividades coletivas (B4 - Escovação Supervisionada)
        $atividadesColetivas = $pec->select("
            SELECT
                cp.co_fat_cidadao_pec AS pec_id,
                COUNT(*) AS b4_count,
                MAX(t.dt_registro) AS last_brushing_date
            FROM tb_fat_atvdd_coletiva_part cp
            JOIN tb_dim_tempo t ON t.co_seq_dim_tempo = cp.co_dim_tempo
            WHERE t.nu_ano = ? AND t.nu_mes BETWEEN ? AND ? AND cp.co_fat_cidadao_pec IS NOT NULL
            GROUP BY cp.co_fat_cidadao_pec
        ", [$year, $firstMonth, $lastMonth]);

        foreach ($atividadesColetivas as $row) {
            $id = (int) $row->pec_id;
            if (! isset($map[$id])) {
                $map[$id] = [
                    'b1_count' => 0,
                    'b2_count' => 0,
                    'b3_count' => 0,
                    'b4_count' => 0,
                    'b5_count' => 0,
                    'b6_count' => 0,
                    'first_consult_date' => null,
                    'treatment_completed_date' => null,
                    'last_attendance_date' => null,
                    'last_prof_name' => null,
                    'last_prof_cbo' => null,
                    'last_prof_cns' => null,
                    'last_brushing_date' => null,
                ];
            }
            $map[$id]['b4_count'] = (int) $row->b4_count;
            $map[$id]['last_brushing_date'] = $row->last_brushing_date ? (string) $row->last_brushing_date : null;
        }

        return $map;
    }

    /**
     * Fallback caso conexão DW não esteja ativa: extrai dos registros locais.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractDentalMetricsFromLocalPatients(int $year, int $quarter): array
    {
        $map = [];
        $patients = OralHealthNominalPatient::where('year', $year)
            ->where('quarter', $quarter)
            ->get();

        foreach ($patients as $p) {
            $id = (int) $p->cidadao_pec_id;
            if (! $id) {
                continue;
            }

            if (! isset($map[$id])) {
                $map[$id] = [
                    'b1_count' => 0,
                    'b2_count' => 0,
                    'b3_count' => 0,
                    'b4_count' => 0,
                    'b5_count' => 0,
                    'b6_count' => 0,
                    'first_consult_date' => null,
                    'treatment_completed_date' => null,
                    'last_attendance_date' => null,
                    'last_prof_name' => $p->professional_name,
                    'last_prof_cbo' => $p->professional_cbo,
                    'last_prof_cns' => null,
                    'last_brushing_date' => null,
                ];
            }

            if ($p->has_first_consultation) {
                $map[$id]['b1_count']++;
                $map[$id]['first_consult_date'] = $p->first_consultation_date?->toDateString();
            }

            if ($p->has_treatment_completed) {
                $map[$id]['b2_count']++;
                $map[$id]['treatment_completed_date'] = $p->treatment_completed_date?->toDateString();
            }

            if ($p->has_supervised_brushing) {
                $map[$id]['b4_count']++;
                $map[$id]['last_brushing_date'] = $p->last_brushing_date?->toDateString();
            }

            $map[$id]['b3_count'] += (int) $p->exodontia_procedures_count;
            $map[$id]['b5_count'] += (int) $p->preventive_procedures_count;
            $map[$id]['b6_count'] += (int) $p->art_procedures_count;
        }

        return $map;
    }
}
