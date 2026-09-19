<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvatNominalMetric extends Model
{
    protected $fillable = [
        'year',
        'month',
        'last_record_date',
        'mici_total',
        'mici_updated',
        'mici_outdated',
        'mici_without_micdt_total',
        'mici_updated_micdt_outdated_or_none',
        'mici_updated_without_micdt',
        'mici_with_micdt_total',
        'mici_and_micdt_updated',
        'mici_and_micdt_outdated',
        'citizens_linked',
        'citizens_not_linked',
        'no_criteria_total',
        'elderly_or_child_total',
        'bpc_or_pbf_total',
        'elderly_child_and_benefit_total',
        'no_criteria_accompanied',
        'elderly_or_child_accompanied',
        'bpc_or_pbf_accompanied',
        'elderly_child_and_benefit_accompanied',
        'no_criteria_not_accompanied',
        'elderly_or_child_not_accompanied',
        'bpc_or_pbf_not_accompanied',
        'elderly_child_and_benefit_not_accompanied',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'last_record_date' => 'date',
            'mici_total' => 'integer',
            'mici_updated' => 'integer',
            'mici_outdated' => 'integer',
            'mici_without_micdt_total' => 'integer',
            'mici_updated_micdt_outdated_or_none' => 'integer',
            'mici_updated_without_micdt' => 'integer',
            'mici_with_micdt_total' => 'integer',
            'mici_and_micdt_updated' => 'integer',
            'mici_and_micdt_outdated' => 'integer',
            'citizens_linked' => 'integer',
            'citizens_not_linked' => 'integer',
            'no_criteria_total' => 'integer',
            'elderly_or_child_total' => 'integer',
            'bpc_or_pbf_total' => 'integer',
            'elderly_child_and_benefit_total' => 'integer',
            'no_criteria_accompanied' => 'integer',
            'elderly_or_child_accompanied' => 'integer',
            'bpc_or_pbf_accompanied' => 'integer',
            'elderly_child_and_benefit_accompanied' => 'integer',
            'no_criteria_not_accompanied' => 'integer',
            'elderly_or_child_not_accompanied' => 'integer',
            'bpc_or_pbf_not_accompanied' => 'integer',
            'elderly_child_and_benefit_not_accompanied' => 'integer',
        ];
    }

    /**
     * Parâmetro populacional municipal normativo (Anexo XCIX da Portaria nº 6/2017 e Item 3.5 da NT nº 30/2025).
     * Municípios Porte 2 (20 mil a 50 mil hab): 2.500 pessoas por eSF (19 eSF = 47.500).
     */
    public function getTargetPopulationAttribute(): int
    {
        return 47500;
    }

    /**
     * Índice Ponderado de Cadastro (X) conforme item 3.5 da NT nº 30/2025:
     * X = ((MICI_apenas * 0.75) + (MICI_e_MICDT * 1.5)) / Populacao_parametro * 100
     */
    public function getIndexXAttribute(): float
    {
        $target = $this->target_population;
        if ($target <= 0) {
            return 0.0;
        }

        $weighted = ($this->mici_updated_micdt_outdated_or_none * 0.75) + ($this->mici_and_micdt_updated * 1.5);

        return round(($weighted / $target) * 100, 2);
    }

    /**
     * Escore da Dimensão Cadastro (até 3,00 pontos) conforme item 3.6 da NT nº 30/2025.
     */
    public function getScoreXAttribute(): float
    {
        $x = $this->index_x;

        return match (true) {
            $x > 85.0 => 3.00,
            $x >= 65.0 => 2.25,
            $x >= 45.0 => 1.50,
            default => 0.75,
        };
    }

    /**
     * Classificação da Dimensão Cadastro conforme item 3.6 da NT nº 30/2025.
     */
    public function getClassificationXAttribute(): string
    {
        $x = $this->index_x;

        return match (true) {
            $x > 85.0 => 'Ótimo',
            $x >= 65.0 => 'Bom',
            $x >= 45.0 => 'Suficiente',
            default => 'Regular',
        };
    }

    /**
     * Índice Ponderado de Acompanhamento (Y) conforme itens 3.10 e 3.11 da NT nº 30/2025:
     * Y = ((A * 1.0) + (B * 1.2) + (C * 1.3) + (D * 2.5)) / Populacao_parametro * 100
     */
    public function getIndexYAttribute(): float
    {
        $target = $this->target_population;
        if ($target <= 0) {
            return 0.0;
        }

        $a = $this->no_criteria_accompanied * 1.0;
        $b = $this->elderly_or_child_accompanied * 1.2;
        $c = $this->bpc_or_pbf_accompanied * 1.3;
        $d = $this->elderly_child_and_benefit_accompanied * 2.5;

        return round((($a + $b + $c + $d) / $target) * 100, 2);
    }

    /**
     * Escore da Dimensão Acompanhamento (até 7,00 pontos) conforme item 3.12 da NT nº 30/2025.
     */
    public function getScoreYAttribute(): float
    {
        $y = $this->index_y;

        return match (true) {
            $y > 85.0 => 7.00,
            $y >= 65.0 => 5.25,
            $y >= 45.0 => 3.50,
            default => 1.75,
        };
    }

    /**
     * Classificação da Dimensão Acompanhamento conforme item 3.12 da NT nº 30/2025.
     */
    public function getClassificationYAttribute(): string
    {
        $y = $this->index_y;

        return match (true) {
            $y > 85.0 => 'Ótimo',
            $y >= 65.0 => 'Bom',
            $y >= 45.0 => 'Suficiente',
            default => 'Regular',
        };
    }

    /**
     * Escore Final Combinado (X + Y) conforme item 3.14 da NT nº 30/2025 (de 0 a 10,00 pontos).
     */
    public function getFinalScoreAttribute(): float
    {
        return round($this->score_x + $this->score_y, 2);
    }

    /**
     * Classificação Final do Município conforme item 3.14 da NT nº 30/2025.
     */
    public function getFinalClassificationAttribute(): string
    {
        $score = $this->final_score;

        return match (true) {
            $score > 8.5 => 'Ótimo',
            $score >= 7.0 => 'Bom',
            $score >= 5.0 => 'Suficiente',
            default => 'Regular',
        };
    }
}
