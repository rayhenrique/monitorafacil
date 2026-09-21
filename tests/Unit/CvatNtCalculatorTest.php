<?php

namespace Tests\Unit;

use App\Services\CvatNtCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CvatNtCalculatorTest extends TestCase
{
    public function test_team_parameters_follow_population_band_and_eap_workload(): void
    {
        $calc = new CvatNtCalculator;
        $this->assertSame(2500, $calc->parameter(39143, '70'));
        $this->assertSame(1875, $calc->parameter(39143, '76', 30));
        $this->assertSame(1250, $calc->parameter(39143, '76', 20));
        $this->assertSame(900, $calc->parameter(900, '70'));
        $this->expectException(InvalidArgumentException::class);
        $calc->parameter(39143, '76');
    }

    public function test_nt30_weighting_and_strict_85_percent_boundary(): void
    {
        $calc = new CvatNtCalculator;
        $this->assertSame(3.0, $calc->registration(0, 142, 250)['score']);
        $this->assertSame(2.25, $calc->registration(0, 141, 250)['score']);
        $this->assertSame(7.0, $calc->monitoring(0, 0, 0, 86, 250)['score']);
        $this->assertSame(5.25, $calc->monitoring(0, 0, 0, 85, 250)['score']);
    }

    public function test_nt8_uses_four_month_mean_and_largest_bonus_once(): void
    {
        $calc = new CvatNtCalculator;
        $months = [
            ['registration' => 3.0, 'monitoring' => 5.25, 'bonus' => 0.15],
            ['registration' => 3.0, 'monitoring' => 5.25, 'bonus' => 0.30],
            ['registration' => 2.25, 'monitoring' => 7.0, 'bonus' => 0.0],
            ['registration' => 3.0, 'monitoring' => 7.0, 'bonus' => 0.15],
        ];
        $result = $calc->quarter($months);
        $this->assertSame(2.8125, $result['registration']);
        $this->assertSame(6.125, $result['monitoring']);
        $this->assertSame(0.30, $result['bonus']);
        $this->assertSame(9.24, $result['final']);
        $this->assertSame('BOM', $calc->quarter($months, true)['classification']);
    }

    public function test_quarter_refuses_missing_month(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new CvatNtCalculator)->quarter([['registration' => 3.0, 'monitoring' => 7.0, 'bonus' => 0.0]]);
    }
}
