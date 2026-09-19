<?php

namespace Tests\Feature;

use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Livewire\Settings\DataProcessing;
use App\Models\C3NominalPregnancy;
use App\Models\User;
use App\Services\C3ActiveSearchService;
use App\Services\C3DwService;
use App\Services\FamilyHealthService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class FamilyHealthC3Test extends TestCase
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
        Schema::dropIfExists('family_health_monthly_snapshots');
        Schema::dropIfExists('c2_nominal_children');
        Schema::dropIfExists('c3_cohort_snapshots');
        Schema::dropIfExists('c3_nominal_pregnancies');
        Schema::dropIfExists('sync_logs');

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

        DB::table('consolidation_teams')->insert([
            'year' => 2026,
            'quarter' => 2,
            'type' => 'esf',
            'total_active' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('consolidation_registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedInteger('mici_updated_count')->default(0);
            $table->unsignedInteger('mici_outdated_count')->default(0);
            $table->unsignedInteger('micdt_updated_count')->default(0);
            $table->unsignedInteger('micdt_outdated_count')->default(0);
            $table->timestamps();
            $table->unique(['year', 'quarter']);
        });

        DB::table('consolidation_registrations')->insert([
            'year' => 2026,
            'quarter' => 2,
            'mici_updated_count' => 1500,
            'mici_outdated_count' => 200,
            'micdt_updated_count' => 1450,
            'micdt_outdated_count' => 250,
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

        Schema::create('c2_nominal_children', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedBigInteger('cidadao_pec_id')->nullable()->index();
            $table->string('cns', 20)->nullable()->index();
            $table->string('cpf', 20)->nullable()->index();
            $table->string('name', 200)->index();
            $table->string('mother_name', 200)->nullable();
            $table->date('birth_date');
            $table->unsignedSmallInteger('age_months')->default(0);
            $table->string('race_color', 50)->nullable();
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 200)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('professional_cns', 20)->nullable();
            $table->string('professional_name', 200)->nullable();
            $table->string('month_ref', 10)->nullable();
            $table->string('microarea', 20)->nullable();
            $table->boolean('mici_updated')->default(false);
            $table->boolean('micdt_updated')->default(false);
            $table->boolean('is_accompanied')->default(false);
            $table->unsignedSmallInteger('practice_a')->default(0);
            $table->unsignedSmallInteger('practice_b')->default(0);
            $table->unsignedSmallInteger('practice_c')->default(0);
            $table->unsignedSmallInteger('practice_d')->default(0);
            $table->unsignedSmallInteger('practice_e')->default(0);
            $table->boolean('practice_a_met')->default(false);
            $table->boolean('practice_b_met')->default(false);
            $table->boolean('practice_c_met')->default(false);
            $table->boolean('practice_d_met')->default(false);
            $table->boolean('practice_e_met')->default(false);
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('calculation_version', 50);
            $table->timestamps();
        });

        Schema::create('c3_cohort_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 150);
            $table->string('team_type', 10);
            $table->unsignedInteger('cohort_total');
            $table->unsignedInteger('evaluated_total');
            $table->json('monthly_counts');
            $table->date('as_of');
            $table->string('calculation_version', 40);
            $table->timestamps();
        });

        Schema::create('c3_nominal_pregnancies', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedBigInteger('cidadao_pec_id')->nullable()->index();
            $table->string('cns', 20)->nullable()->index();
            $table->string('cpf', 20)->nullable()->index();
            $table->string('name', 200)->index();
            $table->date('birth_date');
            $table->unsignedSmallInteger('age_years')->default(0);
            $table->string('race_color', 50)->nullable();
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 200)->nullable();
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('microarea', 20)->nullable();
            $table->string('current_status', 30)->default('gestante');
            $table->date('dum')->nullable();
            $table->date('dpp')->nullable();
            $table->date('outcome_date')->nullable();
            $table->date('puerperium_end_date')->nullable();
            $table->unsignedSmallInteger('gestational_age_weeks')->default(0);
            $table->string('trimester', 20)->nullable();
            $table->string('risk_classification', 20)->default('habitual');
            $table->boolean('is_evaluated_cohort')->default(false);
            $table->date('first_prenatal_date')->nullable();
            $table->unsignedSmallInteger('first_prenatal_ga_weeks')->nullable();
            $table->boolean('practice_a_met')->default(false);
            $table->unsignedSmallInteger('prenatal_visits_count')->default(0);
            $table->boolean('practice_b_met')->default(false);
            $table->unsignedSmallInteger('blood_pressure_records_count')->default(0);
            $table->boolean('practice_c_met')->default(false);
            $table->unsignedSmallInteger('weight_height_records_count')->default(0);
            $table->boolean('practice_d_met')->default(false);
            $table->unsignedSmallInteger('acs_prenatal_visits_count')->default(0);
            $table->boolean('practice_e_met')->default(false);
            $table->boolean('practice_f_met')->default(false);
            $table->boolean('has_syphilis_1st_tri')->default(false);
            $table->boolean('has_hiv_1st_tri')->default(false);
            $table->boolean('has_hepb_1st_tri')->default(false);
            $table->boolean('has_hepc_1st_tri')->default(false);
            $table->boolean('practice_g_met')->default(false);
            $table->boolean('has_syphilis_3rd_tri')->default(false);
            $table->boolean('has_hiv_3rd_tri')->default(false);
            $table->boolean('practice_h_met')->default(false);
            $table->unsignedSmallInteger('puerperal_consultations_count')->default(0);
            $table->boolean('practice_i_met')->default(false);
            $table->unsignedSmallInteger('puerperal_acs_visits_count')->default(0);
            $table->boolean('practice_j_met')->default(false);
            $table->boolean('practice_k_met')->default(false);
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('calculation_version', 50);
            $table->timestamps();
        });

        Schema::create('sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    private function authenticateUser(): User
    {
        $user = User::query()->create([
            'name' => 'Dr. Auditor C3',
            'email' => 'auditor.c3@monitorafacil.local',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_c3_indicator_metadata_in_family_health_service(): void
    {
        $indicator = FamilyHealthService::getIndicatorMeta('c3');

        $this->assertNotNull($indicator);
        $this->assertSame('C3', $indicator['code']);
        $this->assertSame('c3', $indicator['slug']);
        $this->assertSame('Gestação e Puerpério', $indicator['short_title']);
        $this->assertEquals(2.0, $indicator['weight']);

        $practices = $indicator['good_practices'] ?? [];
        $this->assertCount(11, $practices);

        // Good practice A should have 10 points
        $this->assertSame('A', $practices[0]['letter']);
        $this->assertEquals(10, $practices[0]['points']);

        // Good practices B through K should each have 9 points
        $totalPoints = 0;
        foreach ($practices as $p) {
            $totalPoints += $p['points'];
            if ($p['letter'] !== 'A') {
                $this->assertEquals(9, $p['points'], "Practice {$p['letter']} should have 9 points");
            }
        }
        $this->assertEquals(100, $totalPoints, 'Total points across all 11 practices must be exactly 100');
    }

    public function test_c3_active_search_service_columns_and_defaults(): void
    {
        $columns = C3ActiveSearchService::getAvailableColumns();

        $this->assertGreaterThanOrEqual(20, count($columns));
        $this->assertArrayHasKey('name', $columns);
        $this->assertArrayHasKey('current_status', $columns);
        $this->assertArrayHasKey('gestational_age', $columns);
        $this->assertArrayHasKey('dum', $columns);
        $this->assertArrayHasKey('dpp', $columns);
        $this->assertArrayHasKey('practice_a', $columns);
        $this->assertArrayHasKey('practice_f', $columns);
        $this->assertArrayHasKey('score_percent', $columns);

        $defaultSelected = C3ActiveSearchService::getDefaultVisibleColumns();
        $this->assertContains('name', $defaultSelected);
        $this->assertContains('current_status', $defaultSelected);
        $this->assertContains('team', $defaultSelected);
        $this->assertContains('score_percent', $defaultSelected);
    }

    public function test_c3_active_search_service_simulation_fallback(): void
    {
        $service = app(C3ActiveSearchService::class);
        $cohort = $service->getBaseCohort(2026, 2);

        $this->assertNotEmpty($cohort);
        $this->assertGreaterThanOrEqual(10, $cohort->count());

        $summary = $service->getSummaryKpis($cohort, 2026, 2);
        $this->assertArrayHasKey('total_gestantes', $summary);
        $this->assertArrayHasKey('total_puerperas', $summary);
        $this->assertArrayHasKey('total_evaluated_cohort', $summary);
        $this->assertArrayHasKey('practice_kpi', $summary);
        $this->assertCount(11, $summary['practice_kpi']);
    }

    public function test_c3_active_search_with_real_database_records(): void
    {
        C3NominalPregnancy::query()->create([
            'year' => 2026,
            'quarter' => 2,
            'cidadao_pec_id' => 999111,
            'cns' => '701111111111111',
            'cpf' => '123.456.789-00',
            'name' => 'ANA JULIA DA SILVA TESTE',
            'birth_date' => '1998-05-10',
            'age_years' => 28,
            'race_color' => 'Parda',
            'cnes' => '0111791',
            'facility_name' => 'UBS CENTRO INTEGRADO',
            'ine' => '0001715364',
            'team_name' => 'eSF 01 · Centro',
            'microarea' => '01',
            'current_status' => 'gestante',
            'dum' => '2026-01-10',
            'dpp' => '2026-10-17',
            'gestational_age_weeks' => 22,
            'trimester' => '2º Trimestre',
            'risk_classification' => 'habitual',
            'is_evaluated_cohort' => false,
            'first_prenatal_date' => '2026-02-15',
            'first_prenatal_ga_weeks' => 5,
            'practice_a_met' => true,
            'prenatal_visits_count' => 7,
            'practice_b_met' => true,
            'blood_pressure_records_count' => 7,
            'practice_c_met' => true,
            'weight_height_records_count' => 7,
            'practice_d_met' => true,
            'acs_prenatal_visits_count' => 4,
            'practice_e_met' => true,
            'practice_f_met' => true,
            'practice_g_met' => true,
            'practice_h_met' => false,
            'practice_i_met' => false,
            'practice_j_met' => false,
            'practice_k_met' => true,
            'score_percent' => 64.0,
            'calculation_version' => C3DwService::VERSION,
        ]);

        $service = app(C3ActiveSearchService::class);
        $cohort = $service->getBaseCohort(2026, 2);

        $filtered = $service->filterCohort($cohort, [
            'searchName' => 'ANA JULIA',
            'advTeam' => '',
            'advStatus' => 'gestante',
        ]);

        $this->assertCount(1, $filtered);
        $row = $filtered->first();
        $this->assertSame('ANA JULIA DA SILVA TESTE', $row['name']);
        $this->assertSame('0001715364', $row['ine']);
        $this->assertTrue((bool) $row['practice_a_met']);
        $this->assertTrue((bool) $row['practice_b_met']);
        $this->assertTrue((bool) $row['practice_k_met']);
    }

    public function test_indicator_detail_livewire_renders_c3_correctly(): void
    {
        $this->authenticateUser();

        // Test dashboard tab
        Livewire::test(IndicatorDetail::class, ['indicator' => 'c3', 'year' => 2026, 'quarter' => 2])
            ->assertSet('indicator', 'c3')
            ->assertSee('Cuidado na Gestação e Puerpério')
            ->assertSee('Peso 2.0')
            ->assertSee('11 Boas Práticas (100 pts)')
            ->assertSee('Captação Precoce')
            ->assertSee('10 pts')
            ->assertSee('9 pts');

        // Test active search tab
        Livewire::test(IndicatorDetail::class, ['indicator' => 'c3', 'year' => 2026, 'quarter' => 2])
            ->set('activeTab', 'active_search')
            ->assertSee('C3 Cuidado na Gestação e Puerpério')
            ->assertSee('Colunas Visíveis')
            ->assertSee('Busca Avançada')
            ->assertSee('Captação Precoce (até 12ª sem)', false)
            ->assertSee('Gestantes Ativas');

        // Test rules tab
        Livewire::test(IndicatorDetail::class, ['indicator' => 'c3', 'year' => 2026, 'quarter' => 2])
            ->set('activeTab', 'rules')
            ->assertSee('Componente III - Qualidade: Quadro 2 da NT 08/2026')
            ->assertSee('Multiplicador 2.0×')
            ->assertSee('Exceção Oficial para eAP (tipo 76)');
    }

    public function test_data_processing_livewire_includes_c3_scope(): void
    {
        $this->authenticateUser();

        Livewire::test(DataProcessing::class)
            ->assertSee('Processar C3')
            ->call('processC3')
            ->assertSet('selectedScope', 'c3')
            ->assertSet('isProcessing', false);
    }
}
