<?php

namespace Tests\Feature;

use App\Livewire\FamilyHealth\FamilyHealthOverview;
use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Models\C2NominalChild;
use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\User;
use App\Services\C1DwService;
use App\Services\C2ActiveSearchService;
use App\Services\C2DwService;
use App\Services\C2SnapshotService;
use App\Services\CnesXmlParserService;
use App\Services\EsusDataProcessingService;
use App\Services\FamilyHealthService;
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
        Schema::dropIfExists('c2_cohort_snapshots');
        Schema::dropIfExists('c2_nominal_children');
        Schema::dropIfExists('c3_cohort_snapshots');
        Schema::dropIfExists('c3_nominal_pregnancies');
        Schema::dropIfExists('c4_cohort_snapshots');
        Schema::dropIfExists('c4_nominal_diabetics');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 20)->default(User::ROLE_ADMIN);
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

        Schema::create('c2_cohort_snapshots', function (Blueprint $table): void {
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

        Schema::create('c4_cohort_snapshots', function (Blueprint $table): void {
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

        Schema::create('c4_nominal_diabetics', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter');
            $table->unsignedBigInteger('cidadao_pec_id')->nullable()->index();
            $table->string('cns', 20)->nullable()->index();
            $table->string('cpf', 20)->nullable()->index();
            $table->string('name', 200)->index();
            $table->string('social_name', 200)->nullable();
            $table->date('birth_date');
            $table->unsignedTinyInteger('age_years')->default(0);
            $table->string('phone', 30)->nullable();
            $table->string('race_color', 50)->nullable();
            $table->string('cnes', 20)->nullable()->index();
            $table->string('facility_name', 200)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('ine', 20)->nullable()->index();
            $table->string('team_name', 200)->nullable();
            $table->string('professional_cns', 20)->nullable();
            $table->string('professional_name', 200)->nullable();
            $table->string('microarea', 20)->nullable();
            $table->string('ciap_codes', 100)->nullable();
            $table->string('cid_codes', 100)->nullable();
            $table->date('first_diagnosis_date')->nullable();
            $table->date('last_diagnosis_date')->nullable();
            $table->string('condition_status', 30)->default('ativo');
            $table->string('month_ref', 10)->nullable();
            $table->boolean('mici_updated')->default(false);
            $table->boolean('is_accompanied')->default(false);
            $table->unsignedSmallInteger('practice_a')->default(0);
            $table->boolean('practice_a_met')->default(false);
            $table->date('last_consultation_date')->nullable();
            $table->unsignedSmallInteger('practice_b')->default(0);
            $table->boolean('practice_b_met')->default(false);
            $table->date('last_pa_date')->nullable();
            $table->string('last_pa_value', 30)->nullable();
            $table->unsignedSmallInteger('practice_c')->default(0);
            $table->boolean('practice_c_met')->default(false);
            $table->date('last_anthropometry_date')->nullable();
            $table->decimal('last_weight', 5, 2)->nullable();
            $table->decimal('last_height', 5, 2)->nullable();
            $table->unsignedSmallInteger('practice_d')->default(0);
            $table->boolean('practice_d_met')->default(false);
            $table->date('last_acs_visit_date')->nullable();
            $table->unsignedSmallInteger('practice_e')->default(0);
            $table->boolean('practice_e_met')->default(false);
            $table->date('last_hba1c_date')->nullable();
            $table->decimal('last_hba1c_value', 4, 1)->nullable();
            $table->unsignedSmallInteger('practice_f')->default(0);
            $table->boolean('practice_f_met')->default(false);
            $table->date('last_foot_exam_date')->nullable();
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->string('performance_level', 20)->default('regular');
            $table->text('clinical_alerts')->nullable();
            $table->date('as_of');
            $table->string('calculation_version', 40);
            $table->timestamps();
        });

        Schema::dropIfExists('sync_logs');
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

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c7'])
            ->assertSet('c7SubTab', 'monthly_summary')
            ->assertSee('Resumo Mensal por Equipe')
            ->call('switchC7SubTab', 'nominal')
            ->assertSet('c7SubTab', 'nominal')
            ->assertSee('Busca Ativa Nominal')
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

    public function test_c1_legend_order_and_color_parameters(): void
    {
        $this->authenticateUser();

        $meta = FamilyHealthService::getIndicatorMeta('c1');
        $this->assertNotNull($meta);

        $keys = array_keys($meta['parameters']);
        // Ordem deve ser Regular, Suficiente, Bom e Ótimo
        $this->assertSame(['regular', 'sufficient', 'good', 'optimal'], $keys);

        // Cores respectivas: vermelho, amarelo, verde e azul
        $this->assertSame('red', $meta['parameters']['regular']['color']);
        $this->assertSame('yellow', $meta['parameters']['sufficient']['color']);
        $this->assertSame('green', $meta['parameters']['good']['color']);
        $this->assertSame('blue', $meta['parameters']['optimal']['color']);

        // No template HTML do C1, a ordem da legenda é Regular, Suficiente, Bom e Ótimo
        $response = $this->get('/saude-da-familia/c1');
        $response->assertOk();
        $response->assertSeeInOrder([
            'Regular · 0,25 pt',
            'Suficiente · 0,50 pt',
            'Bom · 0,75 pt',
            'Ótimo · 1,00 pt',
        ]);
    }

    public function test_cnes_xml_parser_extracts_only_19_esf_teams_for_teotonio_vilela(): void
    {
        $xmlPath = base_path('importacao/XmlParaESUS31_270915.xml');
        if (! file_exists($xmlPath)) {
            $this->markTestSkipped('XML de Teotônio Vilela não encontrado.');
        }

        $parser = new CnesXmlParserService;
        $eligibleTeams = $parser->getEligibleC1Teams($xmlPath);

        // Teotônio Vilela possui exatamente 19 equipes eSF (Tipo 70)
        $this->assertCount(19, $eligibleTeams);

        foreach ($eligibleTeams as $team) {
            $this->assertSame('70', $team['type']);
            $this->assertMatchesRegularExpression('/^\d{10}$/', $team['ine']);
            $this->assertStringStartsNotWith('ESB', strtoupper($team['name']));
            $this->assertStringNotContainsString('E-MULTI', strtoupper($team['name']));
            $this->assertStringNotContainsString('EQUIPE AMPLIADA', strtoupper($team['name']));
            $this->assertStringNotContainsString('EMAD', strtoupper($team['name']));
            $this->assertStringNotContainsString('EMAP', strtoupper($team['name']));
        }
    }

    public function test_is_eligible_c1_team_filters_out_esb_emulti_emad_emap_and_unknown_teams(): void
    {
        // Equipes válidas
        $this->assertTrue(EsusDataProcessingService::isEligibleC1Team('USF 08 GULANDIM', '70', '0000171220'));
        $this->assertTrue(EsusDataProcessingService::isEligibleC1Team('eAP Noturna Central', '76', '0001839253'));

        // Equipes que NÃO fazem parte de C1
        $this->assertFalse(EsusDataProcessingService::isEligibleC1Team('SEM EQUIPE', '70', 'SEM_INE'));
        $this->assertFalse(EsusDataProcessingService::isEligibleC1Team('INE NÃO ENCONTRADO', '70', '0000000000'));
        $this->assertFalse(EsusDataProcessingService::isEligibleC1Team('E-MULTI COMPLEMENTAR', '72', '0001477269'));
        $this->assertFalse(EsusDataProcessingService::isEligibleC1Team('EQUIPE AMPLIADA', '72', '0001508695'));
        $this->assertFalse(EsusDataProcessingService::isEligibleC1Team('EMAD I', '22', '0001503596'));
        $this->assertFalse(EsusDataProcessingService::isEligibleC1Team('EMAP I', '23', '0001503618'));
        $this->assertFalse(EsusDataProcessingService::isEligibleC1Team('ESB 008', '71', '0001749145'));
        $this->assertFalse(EsusDataProcessingService::isEligibleC1Team('ESB 009', '71', '0001749196'));
        $this->assertFalse(EsusDataProcessingService::isEligibleC1Team('Saúde Bucal Centro', '71', '0001749129'));
    }

    public function test_purge_invalid_c1_snapshots_removes_non_esf_and_non_eap_teams(): void
    {
        $this->authenticateUser();

        // Insere equipes válidas e inválidas no banco
        FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026,
            'quarter' => 1,
            'ine' => '0000171220',
            'team_name' => 'USF 08 GULANDIM',
            'team_type' => '70',
            'indicator_code' => 'c1',
            'numerator' => 50,
            'denominator' => 100,
            'score_percent' => 50.00,
        ]);

        FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026,
            'quarter' => 1,
            'ine' => '0001749145',
            'team_name' => 'ESB 008',
            'team_type' => '71',
            'indicator_code' => 'c1',
            'numerator' => 30,
            'denominator' => 100,
            'score_percent' => 30.00,
        ]);

        FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026,
            'quarter' => 1,
            'ine' => '0001477269',
            'team_name' => 'E-MULTI COMPLEMENTAR',
            'team_type' => '72',
            'indicator_code' => 'c1',
            'numerator' => 20,
            'denominator' => 100,
            'score_percent' => 20.00,
        ]);

        FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026,
            'quarter' => 1,
            'ine' => 'SEM_INE',
            'team_name' => 'SEM EQUIPE',
            'team_type' => '70',
            'indicator_code' => 'c1',
            'numerator' => 10,
            'denominator' => 50,
            'score_percent' => 20.00,
        ]);

        $purgedCount = FamilyHealthService::purgeInvalidC1Snapshots();
        $this->assertSame(3, $purgedCount);

        // Apenas a equipe válida 0000171220 deve permanecer
        $remaining = FamilyHealthIndicatorSnapshot::query()
            ->where('indicator_code', 'c1')
            ->whereNotNull('ine')
            ->get();

        $this->assertCount(1, $remaining);
        $this->assertSame('0000171220', $remaining->first()->ine);
    }

    public function test_sidebar_does_not_contain_c1_c7_badge(): void
    {
        $this->authenticateUser();

        $response = $this->get(route('family-health.overview'));
        $response->assertOk();
        $response->assertSee('Saúde da Família');
        $response->assertDontSee('C1-C7');
        $response->assertDontSee('C1 - C7');
    }

    public function test_c1_monthly_tracking_and_filters(): void
    {
        $this->authenticateUser();

        // Seed team snapshot for C1
        FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026,
            'quarter' => 1,
            'ine' => '0000171220',
            'team_name' => 'ESF CENTRO',
            'team_type' => '70',
            'indicator_code' => 'c1',
            'numerator' => 45,
            'denominator' => 100,
            'score_percent' => 45.00,
            'performance_level' => 'bom',
            'component_iii_points' => 0.75,
            'good_practices_breakdown' => [
                'calculation_version' => C1DwService::VERSION,
                'valid_months' => 4,
                'is_preview' => false,
            ],
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c1', 'year' => 2026, 'quarter' => 1])
            ->assertSee('Acompanhamento Mensal da Demanda')
            ->assertSee('Filtros do Acompanhamento Mensal')
            ->assertSee('Equipe (eSF / eAP):')
            ->assertSee('Mês de Competência:')
            ->assertSee('Quadrimestre / Período:')
            ->assertSee('Classificação Oficial:')
            ->call('setMonth', 2)
            ->assertSet('selectedMonth', 2)
            ->call('setClassification', 'bom')
            ->assertSet('selectedClassification', 'bom')
            ->call('selectTeam', '0000171220')
            ->assertSet('selectedIne', '0000171220')
            ->call('setPeriodString', '2026-2')
            ->assertSet('year', 2026)
            ->assertSet('quarter', 2)
            ->call('resetFilters')
            ->assertSet('selectedIne', null)
            ->assertSet('selectedMonth', null)
            ->assertSet('selectedClassification', null);
    }

    public function test_c1_without_a_valid_dw_snapshot_shows_no_result_and_creates_no_baseline(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c1', 'year' => 2026, 'quarter' => 3])
            ->assertSee('Sem resultado C1 validado para este período.')
            ->assertSee('não gera valores simulados');

        $this->assertDatabaseMissing('family_health_indicator_snapshots', [
            'year' => 2026,
            'quarter' => 3,
            'indicator_code' => 'c1',
        ]);
        $this->assertDatabaseMissing('family_health_monthly_snapshots', [
            'year' => 2026,
            'quarter' => 3,
            'indicator_code' => 'c1',
        ]);
    }

    public function test_c2_metadata_has_weight_two_and_five_good_practices_of_twenty_points(): void
    {
        $this->authenticateUser();

        $meta = FamilyHealthService::getIndicatorMeta('c2');
        $this->assertNotNull($meta);

        // Peso 2.0 oficial conforme NT 08/2026 Quadro 2
        $this->assertSame(2.0, (float) $meta['weight']);

        // 5 Boas Práticas Oficiais de 20 pontos cada = 100 pontos
        $this->assertCount(5, $meta['good_practices']);
        $totalPoints = 0;
        foreach ($meta['good_practices'] as $practice) {
            $this->assertSame(20, $practice['points']);
            $totalPoints += $practice['points'];
        }
        $this->assertSame(100, $totalPoints);

        // Ordem oficial da régua: Regular, Suficiente, Bom e Ótimo
        $keys = array_keys($meta['parameters']);
        $this->assertSame(['regular', 'sufficient', 'good', 'optimal'], $keys);

        // Cores respectivas: vermelho, amarelo, verde e azul
        $this->assertSame('red', $meta['parameters']['regular']['color']);
        $this->assertSame('yellow', $meta['parameters']['sufficient']['color']);
        $this->assertSame('green', $meta['parameters']['good']['color']);
        $this->assertSame('blue', $meta['parameters']['optimal']['color']);

        // Pontuação Componente III com peso 2.0
        $this->assertSame(2.00, FamilyHealthService::calculateComponentIIIPoints('otimo', 2.0));
        $this->assertSame(1.50, FamilyHealthService::calculateComponentIIIPoints('bom', 2.0));
        $this->assertSame(1.00, FamilyHealthService::calculateComponentIIIPoints('suficiente', 2.0));
        $this->assertSame(0.50, FamilyHealthService::calculateComponentIIIPoints('regular', 2.0));
    }

    public function test_c2_monthly_tracking_and_filters(): void
    {
        $this->authenticateUser();

        // Seed team snapshot for C2
        FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026,
            'quarter' => 1,
            'ine' => '0000171220',
            'team_name' => 'ESF CENTRO',
            'team_type' => '70',
            'indicator_code' => 'c2',
            'numerator' => 82,
            'denominator' => 100,
            'score_percent' => 82.00,
            'performance_level' => 'otimo',
            'component_iii_points' => 2.00,
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c2', 'year' => 2026, 'quarter' => 1])
            ->assertSee('Prévia Quadrimestral · C2')
            ->assertSee('As 5 Boas Práticas Oficiais do Cuidado Infantil')
            ->assertSee('1ª Consulta até 30 Dias')
            ->assertSee('≥ 9 Consultas até 2 Anos')
            ->assertSee('≥ 9 Registros Peso e Altura')
            ->assertSee('≥ 2 Visitas Domiciliares ACS')
            ->assertSee('Vacinação Completa Recomendada')
            ->assertSee('Acompanhamento Mensal do Desenvolvimento Infantil')
            ->assertSee('Filtros do Acompanhamento Mensal · C2')
            ->call('setMonth', 1)
            ->assertSet('selectedMonth', 1)
            ->call('setClassification', 'otimo')
            ->assertSet('selectedClassification', 'otimo')
            ->call('selectTeam', '0000171220')
            ->assertSet('selectedIne', '0000171220')
            ->call('resetFilters')
            ->assertSet('selectedIne', null)
            ->assertSet('selectedMonth', null)
            ->assertSet('selectedClassification', null);
    }

    public function test_c2_command_does_not_generate_scores_without_pec(): void
    {
        config(['database.connections.pgsql_esus.host' => '127.0.0.1', 'database.connections.pgsql_esus.port' => 54339]);
        \Illuminate\Support\Facades\DB::purge('pgsql_esus');

        $this->artisan('esus:process-data', ['--scope' => 'c2', '--year' => 2026, '--quarter' => 1])
            ->assertExitCode(1);

        $c2Snapshots = FamilyHealthIndicatorSnapshot::query()
            ->where('indicator_code', 'c2')
            ->where('year', 2026)
            ->where('quarter', 1)
            ->count();

        $this->assertSame(0, $c2Snapshots);
    }

    public function test_old_c2_snapshots_are_hidden_until_recalculated_from_dw(): void
    {
        $this->authenticateUser();
        FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026, 'quarter' => 1, 'ine' => null,
            'team_name' => 'Consolidado Municipal', 'team_type' => '70',
            'indicator_code' => 'c2', 'numerator' => 800, 'denominator' => 10,
            'score_percent' => 80, 'performance_level' => 'otimo',
        ]);

        $service = app(FamilyHealthService::class);
        $detail = $service->getIndicatorDetail('c2', 2026, 1);
        $this->assertNull($detail['current']['score_percent']);
        $this->assertSame(0, $detail['quarter_summary']['valid_months']);
        $this->assertSame([], $detail['active_search_list']);
        $this->get('/saude-da-familia/c2?ano=2026&quadrimestre=1')
            ->assertOk()->assertSee('Sem resultado C2 validado');
    }

    public function test_c2_snapshot_uses_only_months_with_two_year_old_cohort(): void
    {
        $team = ['0000171220' => ['ine' => '0000171220', 'name' => 'ESF CENTRO', 'type' => '70']];
        $source = \Mockery::mock(C2DwService::class);
        $source->shouldReceive('extract')->once()->andReturn([
            'scores' => ['0000171220' => [
                1 => ['numerator' => 80, 'denominator' => 2, 'score_percent' => 40.0,
                    'practices' => ['A' => 1, 'B' => 1, 'C' => 1, 'D' => 1, 'E' => 0], 'incomplete' => 2],
                3 => ['numerator' => 80, 'denominator' => 1, 'score_percent' => 80.0,
                    'practices' => ['A' => 1, 'B' => 1, 'C' => 1, 'D' => 1, 'E' => 0], 'incomplete' => 1],
            ]],
            'cohort' => ['0000171220' => [1 => 2, 3 => 1]],
            'completed' => ['0000171220' => [1 => 2, 3 => 1]],
            'as_of' => '2026-03-18',
        ]);
        $writer = new C2SnapshotService($source);
        $stats = $writer->process(DB::connection('sqlite'), 2026, 1, $team);

        $this->assertSame(['children' => 3, 'cohort_children' => 3, 'completed_children' => 3, 'teams' => 1, 'months' => 2], $stats);
        $detail = app(FamilyHealthService::class)->getIndicatorDetail('c2', 2026, 1);
        $this->assertSame(60.0, $detail['current']['score_percent']);
        $this->assertSame(2, $detail['quarter_summary']['valid_months']);
        $this->assertNull($detail['monthly_evolution'][1]['score_percent']);
        $this->assertSame(3, $detail['current']['denominator']);
        $this->assertSame(3, $detail['current']['cohort_total']);
        $this->assertSame(3, $detail['current']['evaluated_total']);
        $this->assertNull($detail['monthly_evolution'][3]['cohort_total']);
        $this->assertSame(160, $detail['current']['numerator']);
    }

    public function test_c2_future_only_cohort_has_local_preview_score(): void
    {
        $team = ['0000171220' => ['ine' => '0000171220', 'name' => 'ESF CENTRO', 'type' => '70']];
        $source = \Mockery::mock(C2DwService::class);
        $source->shouldReceive('extract')->once()->andReturn([
            'scores' => ['0000171220' => [12 => [
                'numerator' => 80, 'denominator' => 4, 'score_percent' => 20.0,
                'practices' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 4, 'E' => 0], 'incomplete' => 4,
            ]]],
            'cohort' => ['0000171220' => [12 => 4]],
            'completed' => [],
            'as_of' => '2026-09-18',
        ]);

        $stats = (new C2SnapshotService($source))
            ->process(DB::connection('sqlite'), 2026, 3, $team);

        $this->assertSame(4, $stats['cohort_children']);
        $this->assertSame(4, $stats['children']);
        $this->assertSame(0, $stats['completed_children']);
        $detail = app(FamilyHealthService::class)->getIndicatorDetail('c2', 2026, 3);
        $this->assertSame(4, $detail['current']['cohort_total']);
        $this->assertSame(0, $detail['current']['evaluated_total']);
        $this->assertSame(20.0, $detail['current']['score_percent']);
        $this->assertSame(4, $detail['monthly_evolution'][3]['cohort_total']);
        $this->assertSame(20.0, $detail['monthly_evolution'][3]['score_percent']);
        $this->assertTrue($detail['monthly_evolution'][3]['is_preview']);

        $overview = app(FamilyHealthService::class)->getMunicipalOverview(2026, 3);
        $this->assertSame(4, $overview['indicators']['c2']['cohort_total']);
        $this->assertSame(20.0, $overview['indicators']['c2']['score_percent']);
        $this->assertTrue($overview['indicators']['c2']['is_preview']);

        $this->authenticateUser();
        Livewire::test(IndicatorDetail::class, ['indicator' => 'c2', 'year' => 2026, 'quarter' => 3])
            ->assertSee('As 4 crianças da coorte têm pontuação calculada')
            ->assertSee('Prévia · mês em andamento ou futuro')
            ->assertSee('ESF CENTRO');
    }

    public function test_c2_read_failure_preserves_existing_snapshots(): void
    {
        FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026, 'quarter' => 1, 'ine' => '0000171220',
            'team_name' => 'ESF CENTRO', 'team_type' => '70',
            'indicator_code' => 'c2', 'numerator' => 40, 'denominator' => 1,
            'score_percent' => 40, 'performance_level' => 'suficiente',
        ]);
        $source = \Mockery::mock(C2DwService::class);
        $source->shouldReceive('extract')->once()->andThrow(new \RuntimeException('DW indisponível'));

        try {
            (new C2SnapshotService($source))->process(DB::connection('sqlite'), 2026, 1, [
                '0000171220' => ['ine' => '0000171220', 'name' => 'ESF CENTRO', 'type' => '70'],
            ]);
            $this->fail('A leitura deveria falhar.');
        } catch (\RuntimeException $e) {
            $this->assertSame('DW indisponível', $e->getMessage());
        }

        $this->assertSame(1, FamilyHealthIndicatorSnapshot::query()
            ->where('indicator_code', 'c2')->count());
    }

    public function test_c2_active_search_tab_displays_kpis_and_nominal_list(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c2', 'year' => 2026, 'quarter' => 3])
            ->set('activeTab', 'active_search')
            ->assertSee('Componente de Qualidade / Saúde da Família - C2 Cuidado no Desenvolvimento Infantil')
            ->assertSee('Consulta até 30º dia de vida (A)')
            ->assertSee('Consultas (B)')
            ->assertSee('Peso e Altura (C)')
            ->assertSee('Visitas (D)')
            ->assertSee('Vacinas (E)')
            ->assertSee('Denominador')
            ->assertSee('1.016')
            ->assertSee('Busca Avançada')
            ->assertSee('ABNER VALENTIM')
            ->assertSee('ABYMAEL GAEL')
            ->assertSee('Detalhes');
    }

    public function test_c2_active_search_quick_filters(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c2', 'year' => 2026, 'quarter' => 3])
            ->set('activeTab', 'active_search')
            ->set('searchName', 'Abner')
            ->assertSee('ABNER VALENTIM')
            ->assertDontSee('ABYMAEL GAEL')
            ->set('searchName', '')
            ->set('searchCpf', '303')
            ->assertSee('ABNER VALENTIM')
            ->assertDontSee('ABYMAEL GAEL');
    }

    public function test_c2_active_search_advanced_modal_and_filtering(): void
    {
        $this->authenticateUser();

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c2', 'year' => 2026, 'quarter' => 3])
            ->set('activeTab', 'active_search')
            ->assertSet('showAdvancedModal', false)
            ->set('showAdvancedModal', true)
            ->assertSee('Busca Avançada')
            ->assertSee('Idade (meses)')
            ->set('advPracticeA', 'nao')
            ->call('applyAdvancedSearch')
            ->assertSet('showAdvancedModal', false);

        // Adrian Gael has practice A = 0 (nao)
        $component->assertSee('ADRIAN GAEL')
            ->assertDontSee('ABNER VALENTIM'); // Abner has practice A = 3 (sim)

        // Clear previous boolean filter and test month filter (Image 1 & 2)
        $component->call('clearAdvancedFilters')
            ->call('setMonthFilter', '10/2026')
            ->call('setMonthOption', 'only_selected')
            ->assertSee('ADYLLA SOPHIA') // Month ref 10/2026
            ->assertDontSee('ABNER VALENTIM'); // Month ref 01/2028

        // Test blank month and filtering by current quarter alone
        $component->call('clearMonthFilter')
            ->call('setMonthOption', '')
            ->set('advQuarter', 'current')
            ->assertSee('ADYLLA SOPHIA') // Month ref 10/2026 -> 2026/Q3
            ->assertDontSee('ABNER VALENTIM'); // Month ref 01/2028 -> 2028/Q1

        // Test filtering by a future quarter alone (2028-1)
        $component->set('advQuarter', '2028-1')
            ->assertSee('ABNER VALENTIM') // Month ref 01/2028 -> 2028/Q1
            ->assertDontSee('ADYLLA SOPHIA'); // Month ref 10/2026 -> 2026/Q3

        // Test age group chips (Image 3)
        $component->call('clearAdvancedFilters')
            ->call('setAgeGroup', '0-6')
            ->assertSee('ÁDAN LAEL') // 6 months
            ->assertSee('AGATHA ANTONELLA') // 1 month
            ->assertDontSee('ADYLLA SOPHIA'); // 22 months

        // Test age months multiselect (Image 4)
        $component->call('clearAdvancedFilters')
            ->call('toggleAgeMonth', 22)
            ->assertSee('ADYLLA SOPHIA')
            ->assertDontSee('ÁDAN LAEL');

        // Clear filters
        $component->call('clearAdvancedFilters')
            ->assertSee('ABNER VALENTIM')
            ->assertSee('ADRIAN GAEL');
    }

    public function test_c2_active_search_columns_customization_and_child_detail(): void
    {
        $this->authenticateUser();

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c2', 'year' => 2026, 'quarter' => 3])
            ->set('activeTab', 'active_search');

        // Column toggling
        $this->assertTrue(in_array('cns', $component->get('visibleColumns')));
        $component->call('toggleColumn', 'cns');
        $this->assertFalse(in_array('cns', $component->get('visibleColumns')));
        $component->call('selectAllColumns');
        $this->assertCount(count(C2ActiveSearchService::getAvailableColumns()), $component->get('visibleColumns'));
        $component->call('resetDefaultColumns');
        $this->assertCount(count(C2ActiveSearchService::getDefaultVisibleColumns()), $component->get('visibleColumns'));

        // Child detail modal
        $component->call('openChildDetail', 399008)
            ->assertSet('showDetailModal', true)
            ->assertSee('Status das 5 Boas Práticas Clínicas')
            ->assertSee('ABNER VALENTIM')
            ->assertSee('(A) Consulta até 30º dia de vida')
            ->call('closeChildDetail')
            ->assertSet('showDetailModal', false);
    }

    public function test_c2_nominal_children_real_data_flow(): void
    {
        $this->authenticateUser();

        // Insere registro real na tabela c2_nominal_children
        C2NominalChild::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'cidadao_pec_id' => 998877,
            'cns' => '701234567890123',
            'cpf' => '123.456.789-00',
            'name' => 'MARIA FLOR DA SILVA REAL',
            'mother_name' => 'ANA CLARA DA SILVA',
            'birth_date' => '2025-11-20',
            'age_months' => 10,
            'race_color' => 'Parda',
            'cnes' => '0111791',
            'facility_name' => 'USF CENTRO DE SAUDE CENTRAL',
            'district' => 'Centro',
            'ine' => '0001715364',
            'team_name' => 'eSF 01 · Centro',
            'professional_cns' => '700000000000001',
            'professional_name' => 'ACS JOANA',
            'month_ref' => '11/2027',
            'microarea' => '01',
            'mici_updated' => true,
            'micdt_updated' => true,
            'is_accompanied' => true,
            'practice_a' => 1,
            'practice_b' => 9,
            'practice_c' => 9,
            'practice_d' => 2,
            'practice_e' => 10,
            'practice_a_met' => true,
            'practice_b_met' => true,
            'practice_c_met' => true,
            'practice_d_met' => true,
            'practice_e_met' => true,
            'score_percent' => 100.0,
            'calculation_version' => C2DwService::VERSION,
        ]);

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c2', 'year' => 2026, 'quarter' => 3])
            ->set('activeTab', 'active_search')
            ->assertSee('Base Real e-SUS PEC')
            ->assertSee('MARIA FLOR DA SILVA REAL')
            ->assertDontSee('ABNER VALENTIM'); // Quando a base real tem registros, os dados mockados deixam de aparecer
    }

    public function test_c2_seven_quarters_window_and_future_quarters_inclusion(): void
    {
        $this->authenticateUser();

        $pairs = C2ActiveSearchService::getActiveSearchQuarterPairs(2026, 3);
        $this->assertCount(7, $pairs);
        $this->assertSame([
            ['year' => 2026, 'quarter' => 3],
            ['year' => 2027, 'quarter' => 1],
            ['year' => 2027, 'quarter' => 2],
            ['year' => 2027, 'quarter' => 3],
            ['year' => 2028, 'quarter' => 1],
            ['year' => 2028, 'quarter' => 2],
            ['year' => 2028, 'quarter' => 3],
        ], $pairs);

        // Insere uma criança cujo 2º aniversário cai em 2027/Q2 (quadrimestre futuro +2)
        C2NominalChild::query()->create([
            'year' => 2027,
            'quarter' => 2,
            'cidadao_pec_id' => 887766,
            'cns' => '709999999999999',
            'cpf' => '999.888.777-66',
            'name' => 'BEBE FUTURO DOIS ANOS REAL',
            'mother_name' => 'MAE DO BEBE',
            'birth_date' => '2025-06-15',
            'age_months' => 15,
            'race_color' => 'Branca',
            'cnes' => '0111791',
            'facility_name' => 'USF CENTRO DE SAUDE CENTRAL',
            'district' => 'Centro',
            'ine' => '0001715364',
            'team_name' => 'eSF 01 · Centro',
            'month_ref' => '06/2027',
            'microarea' => '02',
            'mici_updated' => true,
            'micdt_updated' => true,
            'is_accompanied' => true,
            'practice_a' => 1,
            'practice_b' => 9,
            'practice_c' => 9,
            'practice_d' => 2,
            'practice_e' => 10,
            'score_percent' => 100.0,
            'calculation_version' => C2DwService::VERSION,
        ]);

        // Ao visualizar 2026/Q3, a criança do quadrimestre futuro deve ser carregada na busca ativa
        Livewire::test(IndicatorDetail::class, ['indicator' => 'c2', 'year' => 2026, 'quarter' => 3])
            ->set('activeTab', 'active_search')
            ->assertSee('BEBE FUTURO DOIS ANOS REAL');
    }
}
