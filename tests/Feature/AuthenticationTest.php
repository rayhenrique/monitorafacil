<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\FinancialSimulator;
use App\Livewire\Dashboard\QuarterSelector;
use App\Livewire\Dashboard\RegistrationsOverview;
use App\Livewire\Dashboard\TeamsOverview;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_dashboard_requires_authentication_and_public_registration_is_disabled(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/register')->assertNotFound();
        $this->get('/login')->assertOk();
    }

    public function test_manager_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create(['password' => 'senha-segura-123']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-segura-123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_municipality_name_appears_in_the_dashboard_layout(): void
    {
        Setting::query()->create(['key' => 'municipio_nome', 'value' => 'Município de Teste']);

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Município de Teste');
    }

    public function test_period_selector_uses_latest_snapshot_and_updates_quarter(): void
    {
        ConsolidationRegistration::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'mici_updated_count' => 1,
            'mici_outdated_count' => 0,
            'micdt_updated_count' => 1,
            'micdt_outdated_count' => 0,
        ]);

        Livewire::test(QuarterSelector::class)
            ->assertSet('year', 2026)
            ->assertSet('quarter', 3)
            ->set('quarter', 2)
            ->assertSee('2º quadrimestre de 2026');
    }

    public function test_team_overview_distinguishes_zero_from_missing_snapshot(): void
    {
        ConsolidationTeam::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'type' => 'esf',
            'total_active' => 0,
        ]);

        Livewire::test(TeamsOverview::class, ['year' => 2026, 'quarter' => 3])
            ->assertSee('Equipes homologadas')
            ->assertSee('Sem consolidação neste período')
            ->assertSee('Equipes no snapshot local');
    }

    public function test_registration_overview_labels_micdt_as_households(): void
    {
        ConsolidationRegistration::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'mici_updated_count' => 47_466,
            'mici_outdated_count' => 19_921,
            'micdt_updated_count' => 18_359,
            'micdt_outdated_count' => 7_367,
        ]);

        Livewire::test(RegistrationsOverview::class, ['year' => 2026, 'quarter' => 3])
            ->assertSee('fichas/domicílios')
            ->assertSee('18.359')
            ->assertSee('7.367');
    }

    public function test_financial_scenario_requires_all_esf_to_be_distributed(): void
    {
        ConsolidationTeam::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'type' => 'esf',
            'total_active' => 19,
        ]);

        Livewire::test(FinancialSimulator::class, ['year' => 2026, 'quarter' => 3])
            ->assertSee('Aguardando distribuição')
            ->call('useScenario', 'good')
            ->assertSet('good', 19)
            ->assertSee('114.000,00')
            ->set('optimal', 1)
            ->assertSee('excede o total')
            ->assertDontSee('122.000,00');
    }

    public function test_quality_overview_renders_family_and_oral_health_indicators(): void
    {
        Livewire::test(\App\Livewire\Dashboard\QualityOverview::class, ['year' => 2026, 'quarter' => 3])
            ->assertSee('Componente de Qualidade')
            ->assertSee('Mais Acesso')
            ->assertSee('Primeira Consulta Programada')
            ->assertSee('Ótimo');
    }
}
