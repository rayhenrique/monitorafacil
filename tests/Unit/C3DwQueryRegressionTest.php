<?php

namespace Tests\Unit;

use App\Services\C3DwService;
use App\Services\C3PracticeCalculator;
use Illuminate\Database\ConnectionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class C3DwQueryRegressionTest extends TestCase
{
    public function test_c3_query_keeps_the_production_timeout_regressions_removed(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/app/Services/C3DwService.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private const CHUNK_SIZE = 100;', $source);
        $this->assertStringContainsString('t.dt_registro BETWEEN ? AND ?', $source);
        $this->assertStringContainsString('tb_dim_tempo_dum', $source);
        $this->assertStringContainsString("'dum_table'] = 'tb_dim_tempo'", $source);
        $this->assertStringContainsString("'dum_pk'] = 'co_seq_dim_tempo'", $source);
        $this->assertStringNotContainsString("LIKE 'O%'", $source);
        $this->assertStringNotContainsString('EXISTS (', $source);
        $this->assertStringNotContainsString('subMonths(5)', $source);
        $this->assertStringNotContainsString('count($p[\'prenatal_consults\']) >= 2', $source);
    }

    public function test_c3_resolves_dum_with_the_legacy_time_dimension(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection->expects($this->exactly(3))
            ->method('select')
            ->willReturnCallback(fn (string $query, array $bindings): array => match ($bindings[0]) {
                'tb_fat_atendimento_individual' => [(object) ['column_name' => 'co_dim_tempo_dum']],
                'tb_dim_tempo_dum' => [],
                'tb_dim_tempo' => [
                    (object) ['column_name' => 'co_seq_dim_tempo'],
                    (object) ['column_name' => 'dt_registro'],
                ],
            });

        $method = new ReflectionMethod(C3DwService::class, 'resolveIndividualCareSchema');
        $schema = $method->invoke(new C3DwService(new C3PracticeCalculator), $connection);

        $this->assertTrue($schema['dum_is_fk']);
        $this->assertSame('tb_dim_tempo', $schema['dum_table']);
        $this->assertSame('co_seq_dim_tempo', $schema['dum_pk']);
    }
}
