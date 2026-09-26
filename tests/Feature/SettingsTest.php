<?php

namespace Tests\Feature;

use App\Enums\SyncStatus;
use App\Livewire\Settings\AuditLogs;
use App\Livewire\Settings\DataProcessing;
use App\Livewire\Settings\MunicipalitySettings;
use App\Livewire\Settings\UsersManager;
use App\Models\SyncLog;
use App\Models\User;
use App\Services\CnesXmlParserService;
use App\Services\SettingsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $sqliteConnection = DB::connection('sqlite');
        app('db')->extend('mysql', static fn () => $sqliteConnection);

        config([
            'database.connections.pgsql_esus.host' => '127.0.0.1',
            'database.connections.pgsql_esus.port' => 54339,
            'database.connections.pgsql_esus.connect_timeout' => 1,
        ]);
        DB::purge('pgsql_esus');

        Schema::dropIfExists('users');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('consolidation_registrations');
        Schema::dropIfExists('consolidation_teams');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 20)->default(User::ROLE_OPERATOR);
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 150)->nullable();
            $table->string('last_seen_version')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('consolidation_teams', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('type');
            $table->unsignedInteger('total_active');
            $table->timestamps();
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
        });

        Schema::dropIfExists('family_health_indicator_snapshots');
        Schema::dropIfExists('family_health_monthly_snapshots');

        Schema::create('family_health_indicator_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 150)->nullable();
            $table->string('team_type', 10)->default('70');
            $table->string('indicator_code', 10)->index();
            $table->unsignedInteger('numerator')->default(0);
            $table->unsignedInteger('denominator')->default(0);
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('performance_level', 20)->default('regular');
            $table->json('good_practices_breakdown')->nullable();
            $table->unsignedInteger('active_search_count')->default(0);
            $table->timestamps();

            $table->unique(['year', 'quarter', 'ine', 'indicator_code'], 'unique_team_indicator_period');
        });

        Schema::create('family_health_monthly_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedTinyInteger('month_in_quarter');
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 150)->nullable();
            $table->string('team_type', 10)->default('70');
            $table->string('indicator_code', 10)->index();
            $table->unsignedInteger('numerator')->default(0);
            $table->unsignedInteger('denominator')->default(0);
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('performance_level', 20)->default('regular');
            $table->timestamps();

            $table->unique(['year', 'month', 'ine', 'indicator_code'], 'unique_monthly_team_indicator');
        });
    }

    public function test_settings_routes_require_authentication(): void
    {
        $this->get('/configuracoes/usuarios')->assertRedirect('/login');
        $this->get('/configuracoes/municipio')->assertRedirect('/login');
        $this->get('/configuracoes/logs-auditoria')->assertRedirect('/login');
        $this->get('/configuracoes/conexao-esus')->assertRedirect('/login');
        $this->get('/configuracoes/processar-dados')->assertRedirect('/login');
        $this->get('/configuracoes/importar-cnes-xml')->assertRedirect('/login');
    }

    public function test_admin_user_can_access_settings_pages(): void
    {
        $admin = User::query()->create([
            'name' => 'Gestor APS',
            'email' => 'gestor@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        $this->get('/configuracoes/usuarios')->assertOk()->assertSee('Usuários do Sistema');
        $this->get('/configuracoes/municipio')->assertOk()->assertSee('Dados do Município');
        $this->get('/configuracoes/logs-auditoria')->assertOk()->assertSee('Log de Auditoria');
        $this->get('/configuracoes/conexao-esus')->assertOk()->assertSee('Conexão com o e-SUS PEC');
        $this->get('/configuracoes/processar-dados')->assertOk()->assertSee('Processamento de Dados');
        $this->get('/configuracoes/importar-cnes-xml')->assertOk()->assertSee('Importar CNES / XML');
    }

    public function test_operator_user_is_forbidden_from_settings_pages(): void
    {
        $operator = User::query()->create([
            'name' => 'Operador UBS',
            'email' => 'operador@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_OPERATOR,
            'cnes' => '2719738',
            'facility_name' => 'UBS CENTRO',
        ]);

        $this->actingAs($operator);

        $this->get('/configuracoes/usuarios')->assertForbidden();
        $this->get('/configuracoes/municipio')->assertForbidden();
        $this->get('/configuracoes/logs-auditoria')->assertForbidden();
        $this->get('/configuracoes/conexao-esus')->assertForbidden();
        $this->get('/configuracoes/processar-dados')->assertForbidden();
        $this->get('/configuracoes/importar-cnes-xml')->assertForbidden();
    }

    public function test_users_manager_can_create_operator_with_cnes(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersManager::class)
            ->call('create')
            ->set('name', 'Novo Operador')
            ->set('email', 'operador@monitorafacil.gov.br')
            ->set('password', 'senhaSegura123')
            ->set('password_confirmation', 'senhaSegura123')
            ->set('role', User::ROLE_OPERATOR)
            ->set('cnes', '2719738')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'operador@monitorafacil.gov.br',
            'name' => 'Novo Operador',
            'role' => User::ROLE_OPERATOR,
            'cnes' => '2719738',
        ]);
    }

    public function test_users_manager_can_create_admin_without_cnes(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersManager::class)
            ->call('create')
            ->set('name', 'Novo Administrador')
            ->set('email', 'novoadmin@monitorafacil.gov.br')
            ->set('password', 'senhaSegura123')
            ->set('password_confirmation', 'senhaSegura123')
            ->set('role', User::ROLE_ADMIN)
            ->set('cnes', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'novoadmin@monitorafacil.gov.br',
            'role' => User::ROLE_ADMIN,
            'cnes' => null,
        ]);
    }

    public function test_users_manager_requires_cnes_for_operator(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersManager::class)
            ->call('create')
            ->set('name', 'Operador Sem UBS')
            ->set('email', 'semubs@monitorafacil.gov.br')
            ->set('password', 'senhaSegura123')
            ->set('password_confirmation', 'senhaSegura123')
            ->set('role', User::ROLE_OPERATOR)
            ->set('cnes', '')
            ->call('save')
            ->assertHasErrors(['cnes']);

        $this->assertDatabaseMissing('users', [
            'email' => 'semubs@monitorafacil.gov.br',
        ]);
    }

    public function test_users_manager_can_update_user(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
        ]);

        $other = User::query()->create([
            'name' => 'Operador Antigo',
            'email' => 'antigo@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_OPERATOR,
            'cnes' => '2719738',
            'facility_name' => 'CENTRO DE SAUDE',
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersManager::class)
            ->call('edit', $other->id)
            ->set('name', 'Operador Renomeado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $other->id,
            'name' => 'Operador Renomeado',
        ]);
    }

    public function test_users_manager_cannot_delete_self(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersManager::class)
            ->call('confirmDelete', $admin->id)
            ->assertSee('Você não pode excluir o usuário atualmente conectado.');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_users_manager_can_delete_another_user(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
        ]);

        $other = User::query()->create([
            'name' => 'Para Deletar',
            'email' => 'deletar@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersManager::class)
            ->call('confirmDelete', $other->id)
            ->call('delete')
            ->assertSee('Usuário excluído com sucesso.');

        $this->assertDatabaseMissing('users', [
            'id' => $other->id,
        ]);
    }

    public function test_municipality_settings_can_update_values(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin);

        Livewire::test(MunicipalitySettings::class)
            ->set('municipio_nome', 'Pão de Açúcar')
            ->set('municipio_ibge', '2706401')
            ->set('municipio_cnes_sede', '2709151')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Configurações do município atualizadas com sucesso!');

        $settings = app(SettingsService::class);
        $this->assertSame('Pão de Açúcar', $settings->get('municipio_nome'));
        $this->assertSame('2706401', $settings->get('municipio_ibge'));
        $this->assertSame('2709151', $settings->get('municipio_cnes_sede'));
    }

    public function test_audit_logs_displays_records_and_filters(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin);

        SyncLog::query()->create([
            'status' => SyncStatus::Success,
            'started_at' => now()->subMinutes(10),
            'finished_at' => now()->subMinutes(9),
        ]);

        SyncLog::query()->create([
            'status' => SyncStatus::Failed,
            'started_at' => now()->subMinutes(5),
            'finished_at' => now()->subMinutes(4),
            'error_message' => 'Erro de conexão com o banco.',
        ]);

        Livewire::test(AuditLogs::class)
            ->assertSee('Sucesso')
            ->assertSee('Falha')
            ->set('statusFilter', 'failed')
            ->assertSee('Falha');
    }

    public function test_cnes_xml_parser_service_parses_valid_xml(): void
    {
        $xmlContent = <<<'XML'
<?xml version="1.0" encoding="ISO-8859-1"?>
<DADOS_EXPORTADOS>
  <IDENTIFICACAO CO_IBGE_MUN="2706401" />
  <DADOS_GERAIS_ESTABELECIMENTOS CNES="2709151">
    <DADOS_EQUIPES TP_EQUIPE="70" CO_INE="0001234567" DT_DESATIVACAO="" />
    <DADOS_EQUIPES TP_EQUIPE="71" CO_INE="0001234568" DT_DESATIVACAO="" />
    <DADOS_EQUIPES TP_EQUIPE="72" CO_INE="0001234569" DT_DESATIVACAO="" />
  </DADOS_GERAIS_ESTABELECIMENTOS>
</DADOS_EXPORTADOS>
XML;

        $tempPath = tempnam(sys_get_temp_dir(), 'cnes_test_').'.xml';
        file_put_contents($tempPath, $xmlContent);

        try {
            $parser = new CnesXmlParserService;
            $result = $parser->parse($tempPath);

            $this->assertSame('2706401', $result['ibge']);
            $this->assertSame(3, $result['counts']['total']);
            $this->assertSame(1, $result['counts']['esf']);
            $this->assertSame(1, $result['counts']['saude_bucal']);
            $this->assertSame(1, $result['counts']['emulti']);
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    public function test_data_processing_livewire_can_run_processing(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(DataProcessing::class)
            ->assertSet('progressPercent', 0)
            ->assertSee('Progresso estimado')
            ->assertSeeHtml('x-on:click="startProgress(\'o Indicador C1\')"')
            ->call('processNow')
            ->assertSet('progressPercent', 100)
            ->assertSet('processStatus', 'error')
            ->assertSee('A leitura do C2 exige conexão com o DW do PEC');
    }

    public function test_data_processing_livewire_can_run_scoped_c1_and_general(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        // Processamento Apenas C1
        Livewire::test(DataProcessing::class)
            ->call('processC1')
            ->assertSet('progressPercent', 100)
            ->assertSet('selectedScope', 'c1')
            ->assertSee('Foco selecionado: Indicador C1 (Mais Acesso)');

        // Processamento Apenas C2
        Livewire::test(DataProcessing::class)
            ->call('processC2')
            ->assertSet('progressPercent', 100)
            ->assertSet('processStatus', 'error')
            ->assertSet('selectedScope', 'c2')
            ->assertSee('A leitura do C2 exige conexão com o DW do PEC');

        // Processamento Apenas C3
        Livewire::test(DataProcessing::class)
            ->call('processC3')
            ->assertSet('progressPercent', 100)
            ->assertSet('processStatus', 'error')
            ->assertSet('selectedScope', 'c3')
            ->assertSee('A leitura do C3 exige conexão com o DW do PEC');

        // Processamento Apenas Saúde Bucal (B1 a B6)
        Livewire::test(DataProcessing::class)
            ->call('processOralHealth')
            ->assertSet('progressPercent', 100)
            ->assertSet('processStatus', 'error')
            ->assertSet('selectedScope', 'oral-health');

        // Processamento Geral Completo
        Livewire::test(DataProcessing::class)
            ->call('processAll')
            ->assertSet('progressPercent', 100)
            ->assertSet('processStatus', 'error')
            ->assertSet('selectedScope', 'all')
            ->assertSee('A leitura do C2 exige conexão com o DW do PEC');
    }

    public function test_esus_process_data_command_runs_with_scopes(): void
    {
        $this->artisan('esus:process-data', ['--scope' => 'c1'])
            ->expectsOutputToContain('[Escopo: C1]')
            ->assertExitCode(0);

        $this->artisan('esus:process-data', ['--scope' => 'c2'])
            ->expectsOutputToContain('[Escopo: C2]')
            ->assertExitCode(1);

        $this->artisan('esus:process-data', ['--scope' => 'c3'])
            ->expectsOutputToContain('[Escopo: C3]')
            ->assertExitCode(1);

        $this->artisan('esus:process-data', ['--scope' => 'oral-health'])
            ->expectsOutputToContain('[Escopo: ORAL-HEALTH]')
            ->assertExitCode(1);

        $this->artisan('esus:process-data', ['--scope' => 'all'])
            ->expectsOutputToContain('[Escopo: ALL]')
            ->assertExitCode(1);
    }

    public function test_data_processing_can_configure_nightly_routine(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(DataProcessing::class)
            ->assertSet('nightlyRoutineTime', '03:00')
            ->assertSet('nightlyRoutineEnabled', true)
            ->set('nightlyRoutineTime', '04:15')
            ->set('nightlyRoutineEnabled', false)
            ->call('saveNightlySettings')
            ->assertHasNoErrors()
            ->assertSee('Configurações da rotina noturna salvas com sucesso!')
            ->assertSee('04:15');

        $settings = app(SettingsService::class);
        $this->assertSame('04:15', $settings->get('nightly_routine_time'));
        $this->assertSame('0', $settings->get('nightly_routine_enabled'));
    }

    public function test_data_processing_validates_nightly_routine_time_format(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(DataProcessing::class)
            ->set('nightlyRoutineTime', '99:99')
            ->call('saveNightlySettings')
            ->assertSet('nightlyErrorMessage', 'O horário informado deve estar no formato HH:MM (ex: 03:00).');
    }

    public function test_data_processing_can_view_nightly_log_modal(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        Livewire::test(DataProcessing::class)
            ->assertSet('showNightlyLogModal', false)
            ->call('viewNightlyLog')
            ->assertSet('showNightlyLogModal', true)
            ->call('closeNightlyLogModal')
            ->assertSet('showNightlyLogModal', false);
    }

    public function test_settings_service_can_record_and_get_last_indicators_processed_at(): void
    {
        $settings = app(SettingsService::class);
        \Illuminate\Support\Facades\Cache::forget(SettingsService::INDICATORS_CACHE_KEY);

        $this->assertNull($settings->getLastIndicatorsProcessedAt());

        $settings->recordIndicatorsProcessedNow();

        $lastProcessed = $settings->getLastIndicatorsProcessedAt();
        $this->assertNotNull($lastProcessed);
        $this->assertSame(now()->setTimezone('America/Maceio')->format('d/m/Y H:i'), $lastProcessed->format('d/m/Y H:i'));
    }

    public function test_sidebar_displays_last_indicators_processed_at(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $settings = app(SettingsService::class);
        $settings->recordIndicatorsProcessedNow();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Última Atualização');
        $response->assertSee(now()->setTimezone('America/Maceio')->format('d/m/Y H:i'));
    }
}

