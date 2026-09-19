<?php

namespace Tests\Unit;

use App\Services\C1DwService;
use Illuminate\Database\ConnectionInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class C1DwServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_extract_uses_only_the_official_c1_population_and_demand_types(): void
    {
        $capturedSql = '';
        $capturedBindings = [];
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('select')
            ->once()
            ->withArgs(function (string $sql, array $bindings) use (&$capturedSql, &$capturedBindings): bool {
                $capturedSql = $sql;
                $capturedBindings = $bindings;

                return true;
            })
            ->andReturn([
                (object) ['nu_mes' => 9, 'nu_ine' => '0000171220', 'num_programada' => 40, 'den_total' => 100],
            ]);

        $result = (new C1DwService)->extract(
            $connection,
            2026,
            [9, 10],
            ['0000171220']
        );

        $this->assertSame(40, $result['0000171220'][9]['numerator']);
        $this->assertSame(100, $result['0000171220'][9]['denominator']);
        $this->assertStringContainsString("ta.nu_identificador::text IN ('1', '2')", $capturedSql);
        $this->assertStringContainsString("ta.nu_identificador::text IN ('1', '2', '4', '5', '6')", $capturedSql);
        $this->assertStringContainsString('INNER JOIN tb_dim_profissional', $capturedSql);
        $this->assertStringContainsString("COALESCE(p.nu_cns::text, '')", $capturedSql);
        $this->assertStringContainsString("~ '^[0-9]{15}$'", $capturedSql);
        $this->assertStringContainsString('fai.dt_nascimento IS NOT NULL', $capturedSql);
        $this->assertStringContainsString('fai.nu_cpf_cidadao', $capturedSql);
        $this->assertStringContainsString('fai.nu_cns', $capturedSql);
        $this->assertStringNotContainsString(' LIKE ', $capturedSql);
        $this->assertSame(
            ['225142', '225170', '225130', '225125', '225250', '223565', '223505'],
            array_slice($capturedBindings, -7)
        );
    }

    public function test_extract_does_not_query_without_competencies_or_eligible_teams(): void
    {
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldNotReceive('select');
        $service = new C1DwService;

        $this->assertSame([], $service->extract($connection, 2026, [], ['0000171220']));
        $this->assertSame([], $service->extract($connection, 2026, [9], []));
    }
}
