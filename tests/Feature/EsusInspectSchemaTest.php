<?php

namespace Tests\Feature;

use Illuminate\Database\Connection;
use Mockery;
use PDO;
use RuntimeException;
use Tests\TestCase;

class EsusInspectSchemaTest extends TestCase
{
    private string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pec-schema-inventory-test-'.uniqid().'.json';
    }

    protected function tearDown(): void
    {
        if (is_file($this->outputPath)) {
            unlink($this->outputPath);
        }

        Mockery::close();
        parent::tearDown();
    }

    public function test_it_generates_a_metadata_only_inventory_inside_a_read_only_transaction(): void
    {
        $connection = $this->mockConnection();

        $connection->shouldReceive('getPdo')->once()->andReturn(Mockery::mock(PDO::class));
        $connection->shouldReceive('beginTransaction')->once();
        $connection->shouldReceive('statement')->once()->with('SET TRANSACTION READ ONLY')->andReturnTrue();
        $connection->shouldReceive('statement')->once()->with("SET LOCAL statement_timeout TO '60s'")->andReturnTrue();
        $connection->shouldReceive('selectOne')->once()->with(Mockery::pattern('/current_database/'))->andReturn((object) [
            'database_name' => 'esus',
            'postgres_version' => '16.4',
            'transaction_read_only' => 'on',
        ]);
        $connection->shouldReceive('select')->once()->with(Mockery::pattern('/information_schema\.tables/'))->andReturn([
            (object) [
                'table_schema' => 'public',
                'table_name' => 'tb_fat_atendimento_individual',
                'table_type' => 'BASE TABLE',
                'ordinal_position' => 1,
                'column_name' => 'co_seq_fat_atd_ind',
                'data_type' => 'bigint',
                'udt_name' => 'int8',
                'is_nullable' => 'NO',
                'character_maximum_length' => null,
                'numeric_precision' => 64,
                'numeric_scale' => 0,
            ],
            (object) [
                'table_schema' => 'public',
                'table_name' => 'tb_fat_atendimento_individual',
                'table_type' => 'BASE TABLE',
                'ordinal_position' => 2,
                'column_name' => 'co_dim_tempo',
                'data_type' => 'bigint',
                'udt_name' => 'int8',
                'is_nullable' => 'YES',
                'character_maximum_length' => null,
                'numeric_precision' => 64,
                'numeric_scale' => 0,
            ],
            (object) [
                'table_schema' => 'public',
                'table_name' => 'tb_dim_versao',
                'table_type' => 'BASE TABLE',
                'ordinal_position' => 1,
                'column_name' => 'ds_versao',
                'data_type' => 'character varying',
                'udt_name' => 'varchar',
                'is_nullable' => 'YES',
                'character_maximum_length' => 50,
                'numeric_precision' => null,
                'numeric_scale' => null,
            ],
        ]);
        $connection->shouldReceive('select')->once()->with(Mockery::pattern('/pg_indexes/'))->andReturn([
            (object) [
                'table_schema' => 'public',
                'table_name' => 'tb_fat_atendimento_individual',
                'index_name' => 'pk_fat_atendimento_individual',
                'index_definition' => 'CREATE UNIQUE INDEX pk_fat_atendimento_individual ON public.tb_fat_atendimento_individual USING btree (co_seq_fat_atd_ind)',
            ],
        ]);
        $connection->shouldReceive('select')->once()->with(Mockery::pattern('/pg_class/'))->andReturn([
            (object) [
                'table_schema' => 'public',
                'table_name' => 'tb_fat_atendimento_individual',
                'estimated_rows' => 1250,
            ],
        ]);
        $connection->shouldReceive('commit')->once();

        app('db')->extend('pgsql_esus', static fn () => $connection);

        $this->artisan('esus:inspect-schema', ['--output' => $this->outputPath])
            ->assertSuccessful()
            ->expectsOutputToContain('Inventário concluído em modo somente leitura: 2 tabelas, 3 colunas e 1 índices.');

        $inventory = json_decode((string) file_get_contents($this->outputPath), true, flags: JSON_THROW_ON_ERROR);

        $this->assertTrue($inventory['database']['transaction_read_only']);
        $this->assertSame(2, $inventory['summary']['tables']);
        $this->assertSame(1250, $inventory['tables'][0]['estimated_rows']);
        $this->assertSame('ds_versao', $inventory['pec_version_candidates'][0]['column_name']);
        $this->assertArrayNotHasKey('rows', $inventory['tables'][0]);
    }

    public function test_it_rolls_back_and_does_not_write_a_report_when_the_inventory_fails(): void
    {
        $connection = $this->mockConnection();

        $connection->shouldReceive('getPdo')->once()->andReturn(Mockery::mock(PDO::class));
        $connection->shouldReceive('beginTransaction')->once();
        $connection->shouldReceive('statement')->twice()->andReturnTrue();
        $connection->shouldReceive('selectOne')->once()->andThrow(new RuntimeException('schema indisponível'));
        $connection->shouldReceive('rollBack')->once();

        app('db')->extend('pgsql_esus', static fn () => $connection);

        $this->artisan('esus:inspect-schema', ['--output' => $this->outputPath])
            ->assertFailed()
            ->expectsOutputToContain('Falha no inventário do PEC: schema indisponível');

        $this->assertFileDoesNotExist($this->outputPath);
    }

    private function mockConnection(): Connection&Mockery\MockInterface
    {
        $connection = Mockery::mock(Connection::class)->shouldIgnoreMissing();
        $connection->shouldReceive('setReadWriteType')->andReturnSelf();

        return $connection;
    }
}
