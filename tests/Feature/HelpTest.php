<?php

namespace Tests\Feature;

use App\Livewire\Help\FillingGuide;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class HelpTest extends TestCase
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

    private function authenticateUser(): User
    {
        $user = User::query()->create([
            'name' => 'Gestor APS',
            'email' => 'gestor@monitorafacil.com.br',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_unauthenticated_user_cannot_access_help_module(): void
    {
        $response = $this->get('/ajuda/guia-preenchimento');
        $response->assertRedirect('/login');

        $rootResponse = $this->get('/ajuda');
        $rootResponse->assertRedirect('/login');
    }

    public function test_help_root_redirects_to_guia_preenchimento(): void
    {
        $this->authenticateUser();

        $response = $this->get('/ajuda');
        $response->assertRedirect('/ajuda/guia-preenchimento');
    }

    public function test_authenticated_user_can_view_filling_guide_page(): void
    {
        $this->authenticateUser();

        $response = $this->get('/ajuda/guia-preenchimento');

        $response->assertOk();
        $response->assertSee('Guia de Preenchimento | Ministério da Saúde');
        $response->assertSee('https://sisaps.saude.gov.br/sistemas/esusaps/docs/guias-preenchimento/');
        $response->assertSee('Portal Oficial SISAPS');
        $response->assertSee('Saúde da Família (eSF / eAP)');
        $response->assertSee('Saúde Bucal (eSB)');
        $response->assertSee('Equipes eMulti');
        $response->assertSee('Cadastros (MICI / MICDT)');
    }

    public function test_filling_guide_livewire_component_renders_and_switches_categories(): void
    {
        $this->authenticateUser();

        Livewire::test(FillingGuide::class)
            ->assertSet('activeCategory', 'esf')
            ->assertSee('Equipe de Atenção Primária e Saúde da Família (eSF / eAP)')
            ->assertSee('C1')
            ->assertSee('C2')
            ->assertSee('C3')
            ->assertSee('C7')
            ->call('setCategory', 'esb')
            ->assertSet('activeCategory', 'esb')
            ->assertSee('Equipe de Saúde Bucal (eSB)')
            ->assertSee('B1')
            ->assertSee('B2')
            ->assertSee('B6')
            ->call('setCategory', 'emulti')
            ->assertSet('activeCategory', 'emulti')
            ->assertSee('Equipes Multiprofissionais (eMulti)')
            ->assertSee('M1')
            ->assertSee('M2')
            ->call('setCategory', 'cadastros')
            ->assertSet('activeCategory', 'cadastros')
            ->assertSee('Cadastros Individuais e Domiciliares (MICI e MICDT)');
    }
}
