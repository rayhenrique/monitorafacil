<?php

namespace Tests\Feature;

use App\Livewire\FamilyHealth\FamilyHealthOverview;
use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class FamilyHealthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $sqliteConnection = DB::connection('sqlite');
        app('db')->extend('mysql', static fn () => $sqliteConnection);

        Schema::dropIfExists('users');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('consolidation_teams');
        Schema::dropIfExists('consolidation_registrations');
        Schema::dropIfExists('family_health_indicator_snapshots');

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

        DB::table('consolidation_teams')->insert([
            'year' => 2026,
            'quarter' => 2,
            'type' => 'esf',
            'total_active' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

        Schema::dropIfExists('family_health_monthly_snapshots');
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

    public function test_unauthenticated_user_cannot_access_family_health_routes(): void
    {
        $this->get('/saude-da-familia')->assertRedirect('/login');
        $this->get('/saude-da-familia/c1')->assertRedirect('/login');
        $this->get('/saude-da-familia/c7')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_overview(): void
    {
        $this->authenticateUser();

        $response = $this->get('/saude-da-familia');
        $response->assertOk();
        $response->assertSee('Saúde da Família');
        $response->assertSee('Desempenho Geral nos Indicadores Clínicos');
        $response->assertSee('Portaria GM/MS nº 3.493/2024');

        // Check that all 7 indicators are mentioned on the overview
        $response->assertSee('Mais Acesso');
        $response->assertSee('Desenvolvimento Infantil');
        $response->assertSee('Gestação e Puerpério');
        $response->assertSee('Pessoas com Diabetes');
        $response->assertSee('Pessoas com Hipertensão');
        $response->assertSee('Pessoa Idosa');
        $response->assertSee('Prevenção do Câncer');
    }

    public function test_authenticated_user_can_access_each_indicator_detail_from_c1_to_c7(): void
    {
        $this->authenticateUser();

        $indicators = ['c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7'];

        foreach ($indicators as $ind) {
            $response = $this->get("/saude-da-familia/{$ind}");
            $response->assertOk();
            $response->assertSee(strtoupper($ind));
            $response->assertSee('Desempenho por Equipe');
            $response->assertSee('Busca Ativa');
            $response->assertSee('Nota Metodológica Oficial');
        }
    }

    public function test_invalid_indicator_aborts_with_404(): void
    {
        $this->authenticateUser();

        $response = $this->get('/saude-da-familia/c99');
        $response->assertNotFound();
    }

    public function test_indicator_detail_livewire_tab_switching_and_filtering(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c4'])
            ->assertSet('activeTab', 'dashboard')
            ->assertSee('Cuidado da Pessoa com Diabetes')
            ->call('setTab', 'teams')
            ->assertSet('activeTab', 'teams')
            ->assertSee('Desempenho por Equipe')
            ->call('setTab', 'active_search')
            ->assertSet('activeTab', 'active_search')
            ->assertSee('Lista de Busca Ativa')
            ->call('setTab', 'rules')
            ->assertSet('activeTab', 'rules')
            ->assertSee('Caderno Metodológico Oficial')
            ->call('selectTeam', '0001234567')
            ->assertSet('selectedIne', '0001234567');
    }

    public function test_overview_livewire_period_selection(): void
    {
        $this->authenticateUser();

        Livewire::test(FamilyHealthOverview::class)
            ->call('setPeriod', 2026, 2)
            ->assertSet('year', 2026)
            ->assertSet('quarter', 2);
    }
}
