<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\FinancialSimulator;
use App\Livewire\Dashboard\QualityOverview;
use App\Livewire\Dashboard\QuarterSelector;
use App\Livewire\Dashboard\RegistrationsOverview;
use App\Livewire\Dashboard\TeamsOverview;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\Setting;
use App\Models\User;
use App\Services\C1DwService;
use App\Services\C2DwService;
use App\Services\C3DwService;
use App\Services\DashboardSnapshotService;
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
            $table->string('role', 20)->default(User::ROLE_ADMIN);
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 150)->nullable();
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

        Schema::create('family_health_indicator_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 150)->nullable();
            $table->string('team_type', 10)->nullable();
            $table->string('indicator_code', 10)->index();
            $table->unsignedInteger('numerator')->default(0);
            $table->unsignedInteger('denominator')->default(0);
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('performance_level', 20)->default('regular');
            $table->json('good_practices_breakdown')->nullable();
            $table->unsignedInteger('active_search_count')->default(0);
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
        Livewire::test(QualityOverview::class, ['year' => 2026, 'quarter' => 3])
            ->assertSee('Componente de Qualidade')
            ->assertSee('Mais Acesso')
            ->assertSee('Primeira Consulta Programada')
            ->assertSee('Atendimentos por pessoa')
            ->assertSee('Ações interprofissionais')
            ->assertSee('M1')
            ->assertSee('M2')
            ->assertSee('Sem consolidação disponível neste período');
    }

    public function test_quality_overview_uses_current_team_snapshots_for_c1_to_c3(): void
    {
        $rows = [
            ['code' => 'c1', 'ine' => '1111111111', 'level' => 'otimo', 'version' => C1DwService::VERSION],
            ['code' => 'c1', 'ine' => '2222222222', 'level' => 'bom', 'version' => C1DwService::VERSION],
            ['code' => 'c2', 'ine' => '1111111111', 'level' => 'suficiente', 'version' => C2DwService::VERSION],
            ['code' => 'c3', 'ine' => '1111111111', 'level' => 'regular', 'version' => C3DwService::VERSION],
            ['code' => 'c3', 'ine' => '9999999999', 'level' => 'otimo', 'version' => 'versao-antiga'],
        ];

        foreach ($rows as $row) {
            FamilyHealthIndicatorSnapshot::query()->create([
                'year' => 2026,
                'quarter' => 3,
                'ine' => $row['ine'],
                'team_name' => 'Equipe '.$row['ine'],
                'team_type' => '70',
                'indicator_code' => $row['code'],
                'score_percent' => 50,
                'performance_level' => $row['level'],
                'good_practices_breakdown' => ['calculation_version' => $row['version']],
            ]);
        }

        FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => null,
            'team_name' => 'Consolidado Municipal',
            'team_type' => '70',
            'indicator_code' => 'c1',
            'score_percent' => 50,
            'performance_level' => 'regular',
            'good_practices_breakdown' => ['calculation_version' => C1DwService::VERSION],
        ]);

        $distribution = app(DashboardSnapshotService::class)->familyHealthPerformance(2026, 3);

        $this->assertSame([
            'optimal' => 1,
            'good' => 1,
            'sufficient' => 0,
            'regular' => 0,
            'evaluated_teams' => 2,
            'has_data' => true,
        ], $distribution['c1']);
        $this->assertSame(1, $distribution['c2']['sufficient']);
        $this->assertSame(1, $distribution['c3']['regular']);
        $this->assertSame(1, $distribution['c3']['evaluated_teams']);

        Livewire::test(QualityOverview::class, ['year' => 2026, 'quarter' => 3])
            ->assertSee('2 equipes avaliadas com consolidação válida')
            ->assertSee('1 equipe avaliada com consolidação válida')
            ->assertSee('Indicador ainda não processado');
    }
}
