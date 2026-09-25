<?php

declare(strict_types=1);

namespace Tests\Unit\OralHealth;

use App\Services\OralHealth\OralHealthPracticeCalculator;
use PHPUnit\Framework\TestCase;

class OralHealthPracticeCalculatorTest extends TestCase
{
    private OralHealthPracticeCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new OralHealthPracticeCalculator();
    }

    /**
     * B1: Primeira Consulta Odontológica Programática
     */
    public function test_b1_calculation_and_classifications(): void
    {
        // Ótimo: > 1.25
        $res = $this->calculator->calculateB1(1300, 1000);
        $this->assertSame(1.3, $res['rate']);
        $this->assertSame('otimo', $res['performance_level']);
        $this->assertSame(2.0, $res['points']); // peso 2.0 * 1.0

        // Bom: > 0.75 e <= 1.25
        $res = $this->calculator->calculateB1(1000, 1000);
        $this->assertSame(1.0, $res['rate']);
        $this->assertSame('bom', $res['performance_level']);
        $this->assertSame(1.5, $res['points']); // peso 2.0 * 0.75

        // Suficiente: > 0.25 e <= 0.75
        $res = $this->calculator->calculateB1(500, 1000);
        $this->assertSame(0.5, $res['rate']);
        $this->assertSame('suficiente', $res['performance_level']);
        $this->assertSame(1.0, $res['points']); // peso 2.0 * 0.50

        // Regular: <= 0.25
        $res = $this->calculator->calculateB1(200, 1000);
        $this->assertSame(0.2, $res['rate']);
        $this->assertSame('regular', $res['performance_level']);
        $this->assertSame(0.5, $res['points']); // peso 2.0 * 0.25
    }

    /**
     * B1: Ajuste de carga horária diferenciada (20h divide denominador por 2)
     */
    public function test_b1_20h_team_hours_adjustment(): void
    {
        $res = $this->calculator->calculateB1(300, 1000, '20h');
        // Denominador ajustado = 500. Rate = 300 / 500 = 0.60 -> suficiente
        $this->assertSame(500, $res['denominator']);
        $this->assertSame(0.6, $res['rate']);
        $this->assertSame('suficiente', $res['performance_level']);
    }

    /**
     * B2: Tratamento Odontológico Concluído
     */
    public function test_b2_calculation_and_classifications(): void
    {
        // Ótimo: > 75 e <= 100
        $res = $this->calculator->calculateB2(80, 100);
        $this->assertSame(80.0, $res['rate']);
        $this->assertSame('otimo', $res['performance_level']);
        $this->assertSame(2.0, $res['points']);

        // Bom: > 50 e <= 75
        $res = $this->calculator->calculateB2(60, 100);
        $this->assertSame(60.0, $res['rate']);
        $this->assertSame('bom', $res['performance_level']);
        $this->assertSame(1.5, $res['points']);

        // Suficiente: > 25 e <= 50
        $res = $this->calculator->calculateB2(40, 100);
        $this->assertSame(40.0, $res['rate']);
        $this->assertSame('suficiente', $res['performance_level']);
        $this->assertSame(1.0, $res['points']);

        // Regular: <= 25
        $res = $this->calculator->calculateB2(20, 100);
        $this->assertSame(20.0, $res['rate']);
        $this->assertSame('regular', $res['performance_level']);
        $this->assertSame(0.5, $res['points']);
    }

    /**
     * B3: Taxa de Exodontia de Dentes Permanentes (Polaridade: Menor-Melhor)
     */
    public function test_b3_calculation_and_polarity_menor_melhor(): void
    {
        // Ótimo: >= 3 e < 10
        $res = $this->calculator->calculateB3(5, 100);
        $this->assertSame(5.0, $res['rate']);
        $this->assertSame('otimo', $res['performance_level']);
        $this->assertSame(2.0, $res['points']);

        // Bom: >= 10 e < 12
        $res = $this->calculator->calculateB3(11, 100);
        $this->assertSame(11.0, $res['rate']);
        $this->assertSame('bom', $res['performance_level']);
        $this->assertSame(1.5, $res['points']);

        // Suficiente: >= 12 e < 14
        $res = $this->calculator->calculateB3(13, 100);
        $this->assertSame(13.0, $res['rate']);
        $this->assertSame('suficiente', $res['performance_level']);
        $this->assertSame(1.0, $res['points']);

        // Regular: < 3 ou >= 14
        $resLow = $this->calculator->calculateB3(2, 100);
        $this->assertSame(2.0, $resLow['rate']);
        $this->assertSame('regular', $resLow['performance_level']);

        $resHigh = $this->calculator->calculateB3(15, 100);
        $this->assertSame(15.0, $resHigh['rate']);
        $this->assertSame('regular', $resHigh['performance_level']);
    }

    /**
     * B4: Ação Coletiva de Escovação Supervisionada
     */
    public function test_b4_calculation_and_classifications(): void
    {
        // Ótimo: > 1
        $res = $this->calculator->calculateB4(150, 100);
        $this->assertSame(1.5, $res['rate']);
        $this->assertSame('otimo', $res['performance_level']);
        $this->assertSame(1.0, $res['points']); // peso 1.0 * 1.0

        // Bom: > 0.5 e <= 1
        $res = $this->calculator->calculateB4(70, 100);
        $this->assertSame(0.7, $res['rate']);
        $this->assertSame('bom', $res['performance_level']);
        $this->assertSame(0.75, $res['points']);

        // Suficiente: > 0.25 e <= 0.5
        $res = $this->calculator->calculateB4(30, 100);
        $this->assertSame(0.3, $res['rate']);
        $this->assertSame('suficiente', $res['performance_level']);
        $this->assertSame(0.5, $res['points']);

        // Regular: <= 0.25
        $res = $this->calculator->calculateB4(10, 100);
        $this->assertSame(0.1, $res['rate']);
        $this->assertSame('regular', $res['performance_level']);
        $this->assertSame(0.25, $res['points']);
    }

    /**
     * B5: Procedimentos Odontológicos Preventivos Individuais
     */
    public function test_b5_calculation_and_classifications(): void
    {
        // Ótimo: >= 65 e <= 85
        $res = $this->calculator->calculateB5(70, 100);
        $this->assertSame(70.0, $res['rate']);
        $this->assertSame('otimo', $res['performance_level']);
        $this->assertSame(2.0, $res['points']);

        // Bom: >= 55 e < 65
        $res = $this->calculator->calculateB5(60, 100);
        $this->assertSame(60.0, $res['rate']);
        $this->assertSame('bom', $res['performance_level']);
        $this->assertSame(1.5, $res['points']);

        // Suficiente: >= 40 e < 55
        $res = $this->calculator->calculateB5(45, 100);
        $this->assertSame(45.0, $res['rate']);
        $this->assertSame('suficiente', $res['performance_level']);
        $this->assertSame(1.0, $res['points']);

        // Regular: < 40 ou > 85
        $resLow = $this->calculator->calculateB5(35, 100);
        $this->assertSame(35.0, $resLow['rate']);
        $this->assertSame('regular', $resLow['performance_level']);

        $resHigh = $this->calculator->calculateB5(90, 100);
        $this->assertSame(90.0, $resHigh['rate']);
        $this->assertSame('regular', $resHigh['performance_level']);
    }

    /**
     * B6: Tratamento Restaurador Atraumático (ART/TRA)
     */
    public function test_b6_calculation_and_classifications(): void
    {
        // Ótimo: > 8
        $res = $this->calculator->calculateB6(10, 100);
        $this->assertSame(10.0, $res['rate']);
        $this->assertSame('otimo', $res['performance_level']);
        $this->assertSame(1.0, $res['points']);

        // Bom: > 6 e <= 8
        $res = $this->calculator->calculateB6(7, 100);
        $this->assertSame(7.0, $res['rate']);
        $this->assertSame('bom', $res['performance_level']);
        $this->assertSame(0.75, $res['points']);

        // Suficiente: > 3 e <= 6
        $res = $this->calculator->calculateB6(5, 100);
        $this->assertSame(5.0, $res['rate']);
        $this->assertSame('suficiente', $res['performance_level']);
        $this->assertSame(0.5, $res['points']);

        // Regular: <= 3
        $res = $this->calculator->calculateB6(2, 100);
        $this->assertSame(2.0, $res['rate']);
        $this->assertSame('regular', $res['performance_level']);
        $this->assertSame(0.25, $res['points']);
    }

    /**
     * Caso de Borda 5: Denominador zero previne DivisionByZeroError em todos os indicadores.
     */
    public function test_zero_denominator_in_all_indicators_prevents_division_by_zero(): void
    {
        $b1 = $this->calculator->calculateB1(10, 0);
        $this->assertSame(0.0, $b1['rate']);
        $this->assertSame('regular', $b1['performance_level']);

        $b2 = $this->calculator->calculateB2(10, 0);
        $this->assertSame(0.0, $b2['rate']);
        $this->assertSame('regular', $b2['performance_level']);

        $b3 = $this->calculator->calculateB3(10, 0);
        $this->assertSame(0.0, $b3['rate']);
        $this->assertSame('regular', $b3['performance_level']);

        $b4 = $this->calculator->calculateB4(10, 0);
        $this->assertSame(0.0, $b4['rate']);
        $this->assertSame('regular', $b4['performance_level']);

        $b5 = $this->calculator->calculateB5(10, 0);
        $this->assertSame(0.0, $b5['rate']);
        $this->assertSame('regular', $b5['performance_level']);

        $b6 = $this->calculator->calculateB6(10, 0);
        $this->assertSame(0.0, $b6['rate']);
        $this->assertSame('regular', $b6['performance_level']);
    }
}
