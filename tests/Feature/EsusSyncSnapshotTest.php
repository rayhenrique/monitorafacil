<?php

namespace Tests\Feature;

use App\Console\Commands\EsusSyncSnapshot;
use App\Enums\SyncStatus;
use App\Enums\TeamType;
use App\Models\Setting;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PDO;
use Tests\TestCase;

class EsusSyncSnapshotTest extends TestCase
{
    private string $xmlFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Faz a conexão 'mysql' usar a mesma instância de banco SQLite in-memory usada nos testes
        $sqliteConnection = DB::connection('sqlite');
        app('db')->extend('mysql', static fn () => $sqliteConnection);

        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('consolidation_registrations');
        Schema::dropIfExists('consolidation_teams');
        Schema::dropIfExists('settings');

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('consolidation_teams', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('type');
            $table->unsignedInteger('total_active');
            $table->timestamps();
            $table->unique(['year', 'quarter', 'type']);
        });

        Schema::create('consolidation_registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedInteger('mici_updated_count');
            $table->unsignedInteger('mici_outdated_count');
            $table->unsignedInteger('micdt_updated_count');
            $table->unsignedInteger('micdt_outdated_count');
            $table->timestamps();
            $table->unique(['year', 'quarter']);
        });

        Schema::create('sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        $this->xmlFilePath = tempnam(sys_get_temp_dir(), 'esus_xml_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->xmlFilePath)) {
            @unlink($this->xmlFilePath);
        }

        Mockery::close();
        parent::tearDown();
    }

    private function createValidCnesXml(string $ibge = '2704302'): void
    {
        $content = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <ROOT>
            <IDENTIFICACAO CO_IBGE_MUN="{$ibge}"/>
            <DADOS_GERAIS_ESTABELECIMENTOS CNES="2005886">
                <DADOS_EQUIPES TP_EQUIPE="70" CO_INE="0000000001" DT_DESATIVACAO=""/>
                <DADOS_EQUIPES TP_EQUIPE="71" CO_INE="0000000002" DT_DESATIVACAO=""/>
                <DADOS_EQUIPES TP_EQUIPE="72" CO_INE="0000000003" DT_DESATIVACAO=""/>
            </DADOS_GERAIS_ESTABELECIMENTOS>
        </ROOT>
        XML;

        file_put_contents($this->xmlFilePath, $content);
        config()->set('esus.homologated_xml_path', $this->xmlFilePath);
    }

    /**
     * Retorna colunas mockadas para simular schema completo do e-SUS PEC.
     *
     * @return list<object>
     */
    private function getMockInformationSchemaColumns(): array
    {
        $required = [
            'tb_equipe' => ['nu_ine', 'co_unidade_saude', 'st_ativo'],
            'tb_unidade_saude' => ['co_seq_unidade_saude', 'nu_cnes'],
            'tb_fat_cad_individual' => ['co_fat_cidadao_pec', 'co_dim_tempo', 'co_dim_municipio', 'st_ficha_inativa', 'st_recusa_cadastro'],
            'tb_fat_cad_domiciliar' => ['co_dim_tempo', 'co_dim_municipio', 'st_ativo', 'st_recusa_cadastro'],
            'tb_dim_tempo' => ['co_seq_dim_tempo', 'dt_registro'],
            'tb_dim_municipio' => ['co_seq_dim_municipio', 'co_ibge'],
        ];

        $columns = [];
        foreach ($required as $table => $cols) {
            foreach ($cols as $column) {
                $columns[] = (object) ['table_name' => $table, 'column_name' => $column];
            }
        }

        return $columns;
    }

    /**
     * Cria e prepara o mock da conexão PostgreSQL para compatibilidade com o DatabaseManager do Laravel.
     */
    private function createPgsqlMock(): Connection&Mockery\MockInterface
    {
        $mock = Mockery::mock(Connection::class)->shouldIgnoreMissing();
        $mock->shouldReceive('setReadWriteType')->andReturnSelf();

        return $mock;
    }

    public function test_sync_snapshot_succeeds_and_persists_consolidated_data(): void
    {
        $this->createValidCnesXml('2704302');
        Setting::query()->create(['key' => 'municipio_ibge', 'value' => '2704302']);

        $pgsqlMock = $this->createPgsqlMock();
        $pdoMock = Mockery::mock(PDO::class);

        $pgsqlMock->shouldReceive('getPdo')->once()->andReturn($pdoMock);
        $pgsqlMock->shouldReceive('statement')->once()->with("SET statement_timeout TO '30s'")->andReturnTrue();

        // 1. verifySourceSchema
        $pgsqlMock->shouldReceive('select')
            ->once()
            ->with(Mockery::pattern('/information_schema\.columns/'), Mockery::any())
            ->andReturn($this->getMockInformationSchemaColumns());

        // 2. countTeams
        $pgsqlMock->shouldReceive('select')
            ->once()
            ->with(Mockery::pattern('/WITH homologated/'), Mockery::any())
            ->andReturn([
                (object) ['team_type' => TeamType::Esf->value, 'total_active' => 12],
                (object) ['team_type' => TeamType::SaudeBucal->value, 'total_active' => 8],
                (object) ['team_type' => TeamType::Emulti->value, 'total_active' => 2],
            ]);

        // 3. countRegistrations - Individual e Domiciliar
        $pgsqlMock->shouldReceive('selectOne')
            ->once()
            ->with(Mockery::pattern('/tb_fat_cad_individual/'), Mockery::any())
            ->andReturn((object) [
                'updated_count' => 15000,
                'outdated_count' => 3000,
            ]);

        $pgsqlMock->shouldReceive('selectOne')
            ->once()
            ->with(Mockery::pattern('/tb_fat_cad_domiciliar/'), Mockery::any())
            ->andReturn((object) [
                'updated_count' => 6000,
                'outdated_count' => 1200,
            ]);

        app('db')->extend('pgsql_esus', static fn () => $pgsqlMock);

        $today = Carbon::today();
        $expectedYear = $today->year;
        $expectedQuarter = intdiv($today->month - 1, 4) + 1;

        $this->artisan('esus:sync-snapshot')
            ->assertSuccessful()
            ->expectsOutputToContain("Snapshot {$expectedYear}/Q{$expectedQuarter} gravado: eSF=12, eSB=8, eMulti=2, MICI=15000/3000, MICDT=6000/1200.");

        // Valida registro de log com sucesso
        $this->assertDatabaseHas('sync_logs', [
            'status' => SyncStatus::Success->value,
            'error_message' => null,
        ]);

        // Valida gravação das equipes
        $this->assertDatabaseHas('consolidation_teams', [
            'year' => $expectedYear,
            'quarter' => $expectedQuarter,
            'type' => TeamType::Esf->value,
            'total_active' => 12,
        ]);
        $this->assertDatabaseHas('consolidation_teams', [
            'year' => $expectedYear,
            'quarter' => $expectedQuarter,
            'type' => TeamType::SaudeBucal->value,
            'total_active' => 8,
        ]);
        $this->assertDatabaseHas('consolidation_teams', [
            'year' => $expectedYear,
            'quarter' => $expectedQuarter,
            'type' => TeamType::Emulti->value,
            'total_active' => 2,
        ]);

        // Valida gravação dos cadastros
        $this->assertDatabaseHas('consolidation_registrations', [
            'year' => $expectedYear,
            'quarter' => $expectedQuarter,
            'mici_updated_count' => 15000,
            'mici_outdated_count' => 3000,
            'micdt_updated_count' => 6000,
            'micdt_outdated_count' => 1200,
        ]);
    }

    public function test_sync_snapshot_fails_when_ibge_in_xml_mismatches_settings(): void
    {
        $this->createValidCnesXml('2704302');
        Setting::query()->create(['key' => 'municipio_ibge', 'value' => '3550308']); // Outro município

        $this->artisan('esus:sync-snapshot')
            ->assertFailed()
            ->expectsOutputToContain('O código IBGE do XML difere do município configurado.');

        $this->assertDatabaseHas('sync_logs', [
            'status' => SyncStatus::Failed->value,
            'error_message' => 'O código IBGE do XML difere do município configurado.',
        ]);
    }

    public function test_sync_snapshot_fails_when_pec_schema_is_missing_required_columns(): void
    {
        $this->createValidCnesXml('2704302');
        Setting::query()->create(['key' => 'municipio_ibge', 'value' => '2704302']);

        $pgsqlMock = $this->createPgsqlMock();
        $pdoMock = Mockery::mock(PDO::class);

        $pgsqlMock->shouldReceive('getPdo')->once()->andReturn($pdoMock);
        $pgsqlMock->shouldReceive('statement')->once()->with("SET statement_timeout TO '30s'")->andReturnTrue();

        // Retorna schema incompleto (ex: sem st_ativo em tb_equipe)
        $incompleteColumns = array_filter(
            $this->getMockInformationSchemaColumns(),
            static fn ($col) => ! ($col->table_name === 'tb_equipe' && $col->column_name === 'st_ativo')
        );

        $pgsqlMock->shouldReceive('select')
            ->once()
            ->with(Mockery::pattern('/information_schema\.columns/'), Mockery::any())
            ->andReturn(array_values($incompleteColumns));

        app('db')->extend('pgsql_esus', static fn () => $pgsqlMock);

        $this->artisan('esus:sync-snapshot')
            ->assertFailed()
            ->expectsOutputToContain('Colunas ausentes no PEC: tb_equipe.st_ativo');

        $this->assertDatabaseHas('sync_logs', [
            'status' => SyncStatus::Failed->value,
            'error_message' => 'Colunas ausentes no PEC: tb_equipe.st_ativo',
        ]);
    }

    public function test_sync_snapshot_fails_gracefully_on_database_connection_timeout(): void
    {
        $this->createValidCnesXml('2704302');
        Setting::query()->create(['key' => 'municipio_ibge', 'value' => '2704302']);

        $pgsqlMock = $this->createPgsqlMock();
        $pgsqlMock->shouldReceive('getPdo')
            ->once()
            ->andThrow(new \RuntimeException('Connection timed out to PostgreSQL e-SUS'));

        app('db')->extend('pgsql_esus', static fn () => $pgsqlMock);

        $this->artisan('esus:sync-snapshot')
            ->assertFailed()
            ->expectsOutputToContain('Connection timed out to PostgreSQL e-SUS');

        $this->assertDatabaseHas('sync_logs', [
            'status' => SyncStatus::Failed->value,
            'error_message' => 'Connection timed out to PostgreSQL e-SUS',
        ]);
    }

    public function test_sync_snapshot_fails_when_xml_file_does_not_exist(): void
    {
        config()->set('esus.homologated_xml_path', '/caminho/inexistente/cnes.xml');

        $this->artisan('esus:sync-snapshot')
            ->assertFailed()
            ->expectsOutputToContain('Configure um XML CNES legível em ESUS_HOMOLOGATED_XML_PATH.');

        $this->assertDatabaseHas('sync_logs', [
            'status' => SyncStatus::Failed->value,
            'error_message' => 'Configure um XML CNES legível em ESUS_HOMOLOGATED_XML_PATH.',
        ]);
    }

    public function test_sync_snapshot_resolves_relative_xml_path(): void
    {
        $relative = 'importacao/XmlParaESUS31_270915.xml';
        config()->set('esus.homologated_xml_path', $relative);

        $command = new EsusSyncSnapshot;
        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('resolveXmlPath');

        $resolved = $method->invoke($command, $relative);
        $this->assertEquals(base_path($relative), $resolved);
        $this->assertFileExists($resolved);
    }
}
