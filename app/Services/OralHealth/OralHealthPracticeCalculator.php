<?php

declare(strict_types=1);

namespace App\Services\OralHealth;

use Carbon\CarbonImmutable;

/**
 * Calculador de regras puras e pontuações dos Indicadores de Saúde Bucal (B1 a B6).
 * POPO - Plain Old PHP Object, sem dependências de banco de dados ou queries SQL.
 */
class OralHealthPracticeCalculator
{
    /**
     * Pesos oficiais dos 6 indicadores no CVAT (Soma = 10.0)
     */
    public const WEIGHTS = [
        'b1' => 2.0,
        'b2' => 2.0,
        'b3' => 2.0,
        'b4' => 1.0,
        'b5' => 2.0,
        'b6' => 1.0,
    ];

    /**
     * B1 - Primeira Consulta Odontológica Programada
     *
     * Numerador: Pessoas com 1ª consulta programática realizada pela eSB no período.
     * Denominador: População vinculada à eSF/eAP de referência da eSB.
     * Parâmetros: Ótimo (> 1,25), Bom (> 0,75 e ≤ 1,25), Suficiente (> 0,25 e ≤ 0,75), Regular (≤ 0,25).
     *
     * @return array{numerator: int, denominator: int, rate: float, performance_level: string, points: float}
     */
    public function calculateB1(int $firstConsultations, int $linkedPopulation, string $teamHours = '40h'): array
    {
        $adjustedDenominator = ($teamHours === '20h' || $teamHours === '20')
            ? (int) ceil($linkedPopulation / 2)
            : $linkedPopulation;

        if ($adjustedDenominator <= 0) {
            return [
                'numerator' => $firstConsultations,
                'denominator' => 0,
                'rate' => 0.0,
                'performance_level' => 'regular',
                'points' => $this->getScorePoints('b1', 'regular'),
            ];
        }

        $rate = round($firstConsultations / $adjustedDenominator, 4);
        $level = $this->classifyB1($rate);

        return [
            'numerator' => $firstConsultations,
            'denominator' => $adjustedDenominator,
            'rate' => $rate,
            'performance_level' => $level,
            'points' => $this->getScorePoints('b1', $level),
        ];
    }

    public function classifyB1(float $rate): string
    {
        if ($rate > 1.25) {
            return 'otimo';
        }
        if ($rate > 0.75) {
            return 'bom';
        }
        if ($rate > 0.25) {
            return 'suficiente';
        }

        return 'regular';
    }

    /**
     * B2 - Tratamento Odontológico Concluído
     *
     * Numerador: Pessoas com tratamento concluído pela eSB em até 12 meses após a 1ª consulta.
     * Denominador: Pessoas com primeira consulta programática no período.
     * Parâmetros: Ótimo (> 75 e ≤ 100), Bom (> 50 e ≤ 75), Suficiente (> 25 e ≤ 50), Regular (≤ 25).
     *
     * @return array{numerator: int, denominator: int, rate: float, performance_level: string, points: float}
     */
    public function calculateB2(int $treatmentCompleted, int $firstConsultations): array
    {
        if ($firstConsultations <= 0) {
            return [
                'numerator' => $treatmentCompleted,
                'denominator' => 0,
                'rate' => 0.0,
                'performance_level' => 'regular',
                'points' => $this->getScorePoints('b2', 'regular'),
            ];
        }

        $rate = round(($treatmentCompleted / $firstConsultations) * 100, 2);
        $level = $this->classifyB2($rate);

        return [
            'numerator' => $treatmentCompleted,
            'denominator' => $firstConsultations,
            'rate' => $rate,
            'performance_level' => $level,
            'points' => $this->getScorePoints('b2', $level),
        ];
    }

    public function classifyB2(float $rate): string
    {
        if ($rate > 75.0) {
            return 'otimo';
        }
        if ($rate > 50.0) {
            return 'bom';
        }
        if ($rate > 25.0) {
            return 'suficiente';
        }

        return 'regular';
    }

    /**
     * B3 - Taxa de Exodontia de Dentes Permanentes
     *
     * Numerador: Exodontias de dentes permanentes (04.14.02.013-8 e 04.14.02.014-6).
     * Denominador: Total de procedimentos individuais preventivos, curativos e exodontias (CD + TSB).
     * Parâmetros (Polaridade: Menor-Melhor):
     * Ótimo (≥ 3 e < 10), Bom (≥ 10 e < 12), Suficiente (≥ 12 e < 14), Regular (< 3 ou ≥ 14).
     *
     * @return array{numerator: int, denominator: int, rate: float, performance_level: string, points: float}
     */
    public function calculateB3(int $permanentExodontias, int $totalProcedures): array
    {
        if ($totalProcedures <= 0) {
            return [
                'numerator' => $permanentExodontias,
                'denominator' => 0,
                'rate' => 0.0,
                'performance_level' => 'regular',
                'points' => $this->getScorePoints('b3', 'regular'),
            ];
        }

        $rate = round(($permanentExodontias / $totalProcedures) * 100, 2);
        $level = $this->classifyB3($rate);

        return [
            'numerator' => $permanentExodontias,
            'denominator' => $totalProcedures,
            'rate' => $rate,
            'performance_level' => $level,
            'points' => $this->getScorePoints('b3', $level),
        ];
    }

    public function classifyB3(float $rate): string
    {
        if ($rate >= 3.0 && $rate < 10.0) {
            return 'otimo';
        }
        if ($rate >= 10.0 && $rate < 12.0) {
            return 'bom';
        }
        if ($rate >= 12.0 && $rate < 14.0) {
            return 'suficiente';
        }

        return 'regular';
    }

    /**
     * B4 - Ação Coletiva de Escovação Dental Supervisionada
     *
     * Numerador: Crianças de 6 a 12 anos participantes da ação coletiva de escovação supervisionada realizada pela eSB.
     * Denominador: População de 6 a 12 anos vinculada à eSF/eAP de referência da eSB.
     * Parâmetros: Ótimo (> 1), Bom (> 0,5 e ≤ 1), Suficiente (> 0,25 e ≤ 0,5), Regular (≤ 0,25).
     *
     * @return array{numerator: int, denominator: int, rate: float, performance_level: string, points: float}
     */
    public function calculateB4(int $childrenBrushed, int $linkedChildren6To12): array
    {
        if ($linkedChildren6To12 <= 0) {
            return [
                'numerator' => $childrenBrushed,
                'denominator' => 0,
                'rate' => 0.0,
                'performance_level' => 'regular',
                'points' => $this->getScorePoints('b4', 'regular'),
            ];
        }

        $rate = round($childrenBrushed / $linkedChildren6To12, 4);
        $level = $this->classifyB4($rate);

        return [
            'numerator' => $childrenBrushed,
            'denominator' => $linkedChildren6To12,
            'rate' => $rate,
            'performance_level' => $level,
            'points' => $this->getScorePoints('b4', $level),
        ];
    }

    public function classifyB4(float $rate): string
    {
        if ($rate > 1.0) {
            return 'otimo';
        }
        if ($rate > 0.5) {
            return 'bom';
        }
        if ($rate > 0.25) {
            return 'suficiente';
        }

        return 'regular';
    }

    /**
     * B5 - Procedimentos Odontológicos Preventivos Individuais
     *
     * Numerador: Procedimentos preventivos individuais realizados pela eSB (CD + TSB).
     * Denominador: Total de procedimentos odontológicos individuais realizados pela eSB.
     * Parâmetros: Ótimo (≥ 65 e ≤ 85), Bom (≥ 55 e < 65), Suficiente (≥ 40 e < 55), Regular (< 40 ou > 85).
     *
     * @return array{numerator: int, denominator: int, rate: float, performance_level: string, points: float}
     */
    public function calculateB5(int $preventiveProcedures, int $totalProcedures): array
    {
        if ($totalProcedures <= 0) {
            return [
                'numerator' => $preventiveProcedures,
                'denominator' => 0,
                'rate' => 0.0,
                'performance_level' => 'regular',
                'points' => $this->getScorePoints('b5', 'regular'),
            ];
        }

        $rate = round(($preventiveProcedures / $totalProcedures) * 100, 2);
        $level = $this->classifyB5($rate);

        return [
            'numerator' => $preventiveProcedures,
            'denominator' => $totalProcedures,
            'rate' => $rate,
            'performance_level' => $level,
            'points' => $this->getScorePoints('b5', $level),
        ];
    }

    public function classifyB5(float $rate): string
    {
        if ($rate >= 65.0 && $rate <= 85.0) {
            return 'otimo';
        }
        if ($rate >= 55.0 && $rate < 65.0) {
            return 'bom';
        }
        if ($rate >= 40.0 && $rate < 55.0) {
            return 'suficiente';
        }

        return 'regular';
    }

    /**
     * B6 - Tratamento Restaurador Atraumático (ART/TRA)
     *
     * Numerador: Procedimentos TRA/ART (03.07.01.007-4) realizados pela eSB.
     * Denominador: Total de procedimentos restauradores realizados pela eSB.
     * Parâmetros: Ótimo (> 8), Bom (> 6 e ≤ 8), Suficiente (> 3 e ≤ 6), Regular (≤ 3).
     *
     * @return array{numerator: int, denominator: int, rate: float, performance_level: string, points: float}
     */
    public function calculateB6(int $artProcedures, int $restorativeProcedures): array
    {
        if ($restorativeProcedures <= 0) {
            return [
                'numerator' => $artProcedures,
                'denominator' => 0,
                'rate' => 0.0,
                'performance_level' => 'regular',
                'points' => $this->getScorePoints('b6', 'regular'),
            ];
        }

        $rate = round(($artProcedures / $restorativeProcedures) * 100, 2);
        $level = $this->classifyB6($rate);

        return [
            'numerator' => $artProcedures,
            'denominator' => $restorativeProcedures,
            'rate' => $rate,
            'performance_level' => $level,
            'points' => $this->getScorePoints('b6', $level),
        ];
    }

    public function classifyB6(float $rate): string
    {
        if ($rate > 8.0) {
            return 'otimo';
        }
        if ($rate > 6.0) {
            return 'bom';
        }
        if ($rate > 3.0) {
            return 'suficiente';
        }

        return 'regular';
    }

    /**
     * Calcula o nível de desempenho unificado por indicador.
     */
    public function getPerformanceLevel(string $indicator, float $rate): string
    {
        return match (strtolower($indicator)) {
            'b1' => $this->classifyB1($rate),
            'b2' => $this->classifyB2($rate),
            'b3' => $this->classifyB3($rate),
            'b4' => $this->classifyB4($rate),
            'b5' => $this->classifyB5($rate),
            'b6' => $this->classifyB6($rate),
            default => 'regular',
        };
    }

    /**
     * Converte o nível de classificação no fator de pontuação e multiplica pelo peso oficial.
     *
     * Fatores:
     * Regular = 0.25
     * Suficiente = 0.50
     * Bom = 0.75
     * Ótimo = 1.00
     */
    public function getScorePoints(string $indicator, string $performanceLevel): float
    {
        $weight = self::WEIGHTS[strtolower($indicator)] ?? 1.0;
        $factor = match (strtolower($performanceLevel)) {
            'otimo', 'optimal' => 1.00,
            'bom', 'good' => 0.75,
            'suficiente', 'sufficient' => 0.50,
            default => 0.25,
        };

        return round($factor * $weight, 2);
    }
}
