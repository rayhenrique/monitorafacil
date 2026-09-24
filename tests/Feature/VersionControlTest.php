<?php

namespace Tests\Feature;

use App\Livewire\Common\WhatsNewModal;
use App\Livewire\Help\WhatsNew;
use App\Models\User;
use App\Services\VersionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class VersionControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $sqliteConnection = DB::connection('sqlite');
        app('db')->extend('mysql', static fn () => $sqliteConnection);

        Schema::dropIfExists('users');
        Schema::dropIfExists('settings');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
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
    }

    private function createUser(?string $lastSeenVersion = null): User
    {
        return User::query()->create([
            'name' => 'Gestor Municipal',
            'email' => 'gestor@monitorafacil.com.br',
            'password' => Hash::make('password123'),
            'last_seen_version' => $lastSeenVersion,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_whats_new(): void
    {
        $response = $this->get('/ajuda/novidades');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_whats_new_page(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->get('/ajuda/novidades');

        $response->assertOk();
        $response->assertSee('Novidades da Versão');
        $response->assertSee('Histórico de Atualizações do Monitora Fácil');
        $response->assertSee('v1.5.0');
        $response->assertSee('v1.4.0');
        $response->assertSee('v1.3.0');
        $response->assertSee('v1.2.0');
        $response->assertSee('v1.1.0');
        $response->assertSee('v1.0.0');
        $response->assertSee('versoes.md');
    }

    public function test_version_service_methods(): void
    {
        $user = $this->createUser(null);

        $this->assertSame(VersionService::CURRENT_VERSION, VersionService::getLatestVersion());
        $this->assertTrue(VersionService::shouldShowModal($user));

        $user->update(['last_seen_version' => 'v1.5.0']);
        $this->assertTrue(VersionService::shouldShowModal($user->fresh()));

        VersionService::markAsSeen($user);
        $this->assertSame(VersionService::CURRENT_VERSION, $user->fresh()->last_seen_version);
        $this->assertFalse(VersionService::shouldShowModal($user->fresh()));

        $allReleases = VersionService::getAllReleases();
        $this->assertGreaterThanOrEqual(48, count($allReleases));
        $this->assertSame(VersionService::CURRENT_VERSION, $allReleases[0]['version']);
        $this->assertSame('v1.0.0', end($allReleases)['version']);
    }

    public function test_whats_new_livewire_component_renders_and_filters(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        Livewire::test(WhatsNew::class)
            ->assertSet('filter', 'all')
            ->assertSee(VersionService::CURRENT_VERSION)
            ->assertSee('Saúde da Família')
            ->call('setFilter', 'novo')
            ->assertSet('filter', 'novo')
            ->assertSee(VersionService::CURRENT_VERSION)
            ->call('setFilter', 'correcao')
            ->assertSet('filter', 'correcao')
            ->assertSee('v1.3.0');
    }

    public function test_whats_new_modal_shows_for_user_with_pending_version_and_acknowledges(): void
    {
        $user = $this->createUser(null);
        $this->actingAs($user);

        // Modal deve abrir pois last_seen_version é null
        Livewire::test(WhatsNewModal::class)
            ->assertSet('show', true)
            ->assertSee('Atualização do Sistema · '.VersionService::CURRENT_VERSION)
            ->assertSee('Entendido, Continuar')
            ->call('acknowledge')
            ->assertSet('show', false);

        // Verifica se persistiu no banco de dados
        $this->assertSame(VersionService::CURRENT_VERSION, $user->fresh()->last_seen_version);

        // Ao renderizar novamente para o mesmo usuário, não deve abrir
        Livewire::test(WhatsNewModal::class)
            ->assertSet('show', false);
    }
}
