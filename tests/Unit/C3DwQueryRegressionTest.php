<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class C3DwQueryRegressionTest extends TestCase
{
    public function test_c3_query_keeps_the_production_timeout_regressions_removed(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/app/Services/C3DwService.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private const CHUNK_SIZE = 100;', $source);
        $this->assertStringContainsString('t.dt_registro BETWEEN ? AND ?', $source);
        $this->assertStringContainsString('tb_dim_tempo_dum', $source);
        $this->assertStringNotContainsString("LIKE 'O%'", $source);
        $this->assertStringNotContainsString('EXISTS (', $source);
        $this->assertStringNotContainsString('subMonths(5)', $source);
        $this->assertStringNotContainsString('count($p[\'prenatal_consults\']) >= 2', $source);
    }
}
