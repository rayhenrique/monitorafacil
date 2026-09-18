<?php

namespace Tests\Unit;

use App\Services\FinancialProjectionService;
use PHPUnit\Framework\TestCase;

class FinancialProjectionServiceTest extends TestCase
{
    public function test_monthly_rates_and_distribution_validation(): void
    {
        $service = new FinancialProjectionService;

        self::assertSame(20_000, $service->monthlyAmount([
            'optimal' => 1,
            'good' => 1,
            'sufficient' => 1,
            'regular' => 1,
        ], 4));

        self::assertNull($service->monthlyAmount([
            'optimal' => 1,
            'good' => 0,
            'sufficient' => 0,
            'regular' => 0,
        ], 2));

        self::assertNull($service->monthlyAmount([
            'optimal' => -1,
            'good' => 2,
            'sufficient' => 0,
            'regular' => 0,
        ], 1));
    }
}
