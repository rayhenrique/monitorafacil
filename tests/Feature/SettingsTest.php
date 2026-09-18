<?php

namespace Tests\Feature;

use App\Enums\SyncStatus;
use App\Enums\TeamType;
use App\Livewire\Settings\AuditLogs;
use App\Livewire\Settings\CnesImport;
use App\Livewire\Settings\DataProcessing;
use App\Livewire\Settings\EsusConnection;
use App\Livewire\Settings\MunicipalitySettings;
use App\Livewire\Settings\UsersManager;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\Setting;
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

    public function test_authenticated_user_can_access_settings_pages(): void
    {
        $user = User::query()->create([
            'name' => 'Gestor APS',
            'email' => 'gestor@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);

        $this->get('/configuracoes/usuarios')->assertOk()->assertSee('Usuários do Sistema');
        $this->get('/configuracoes/municipio')->assertOk()->assertSee('Dados do Município');
        $this->get('/configuracoes/logs-auditoria')->assertOk()->assertSee('Log de Auditoria');
        $this->get('/configuracoes/conexao-esus')->assertOk()->assertSee('Conexão com o e-SUS PEC');
        $this->get('/configuracoes/processar-dados')->assertOk()->assertSee('Processamento de Dados');
        $this->get('/configuracoes/importar-cnes-xml')->assertOk()->assertSee('Importar CNES / XML');
    }

    public function test_users_manager_can_create_user(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrador',
            'email' => 'admin@monitorafacil.gov.br',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersManager::class)
            ->call('create')
            ->set('name', 'Novo Operador')
            ->set('email', 'operador@monitorafacil.gov.br')
            ->set('password', 'senhaSegura123')
            ->set('password_confirmation', 'senhaSegura123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'operador@monitorafacil.gov.br',
            'name' => 'Novo Operador',
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

        $tempPath = tempnam(sys_get_temp_dir(), 'cnes_test_') . '.xml';
        file_put_contents($tempPath, $xmlContent);

        try {
            $parser = new CnesXmlParserService();
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
}
