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

    public function test_c1_legend_order_and_color_parameters(): void
    {
        $this->authenticateUser();

        $meta = \App\Services\FamilyHealthService::getIndicatorMeta('c1');
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

        $parser = new \App\Services\CnesXmlParserService();
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
        $this->assertTrue(\App\Services\EsusDataProcessingService::isEligibleC1Team('USF 08 GULANDIM', '70', '0000171220'));
        $this->assertTrue(\App\Services\EsusDataProcessingService::isEligibleC1Team('eAP Noturna Central', '76', '0001839253'));

        // Equipes que NÃO fazem parte de C1
        $this->assertFalse(\App\Services\EsusDataProcessingService::isEligibleC1Team('SEM EQUIPE', '70', 'SEM_INE'));
        $this->assertFalse(\App\Services\EsusDataProcessingService::isEligibleC1Team('INE NÃO ENCONTRADO', '70', '0000000000'));
        $this->assertFalse(\App\Services\EsusDataProcessingService::isEligibleC1Team('E-MULTI COMPLEMENTAR', '72', '0001477269'));
        $this->assertFalse(\App\Services\EsusDataProcessingService::isEligibleC1Team('EQUIPE AMPLIADA', '72', '0001508695'));
        $this->assertFalse(\App\Services\EsusDataProcessingService::isEligibleC1Team('EMAD I', '22', '0001503596'));
        $this->assertFalse(\App\Services\EsusDataProcessingService::isEligibleC1Team('EMAP I', '23', '0001503618'));
        $this->assertFalse(\App\Services\EsusDataProcessingService::isEligibleC1Team('ESB 008', '71', '0001749145'));
        $this->assertFalse(\App\Services\EsusDataProcessingService::isEligibleC1Team('ESB 009', '71', '0001749196'));
        $this->assertFalse(\App\Services\EsusDataProcessingService::isEligibleC1Team('Saúde Bucal Centro', '71', '0001749129'));
    }

    public function test_purge_invalid_c1_snapshots_removes_non_esf_and_non_eap_teams(): void
    {
        $this->authenticateUser();

        // Insere equipes válidas e inválidas no banco
        \App\Models\FamilyHealthIndicatorSnapshot::query()->create([
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

        \App\Models\FamilyHealthIndicatorSnapshot::query()->create([
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

        \App\Models\FamilyHealthIndicatorSnapshot::query()->create([
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

        \App\Models\FamilyHealthIndicatorSnapshot::query()->create([
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

        $purgedCount = \App\Services\FamilyHealthService::purgeInvalidC1Snapshots();
        $this->assertSame(3, $purgedCount);

        // Apenas a equipe válida 0000171220 deve permanecer
        $remaining = \App\Models\FamilyHealthIndicatorSnapshot::query()
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
        \App\Models\FamilyHealthIndicatorSnapshot::query()->create([
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

    public function test_c2_metadata_has_weight_two_and_five_good_practices_of_twenty_points(): void
    {
        $this->authenticateUser();

        $meta = \App\Services\FamilyHealthService::getIndicatorMeta('c2');
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
        $this->assertSame(2.00, \App\Services\FamilyHealthService::calculateComponentIIIPoints('otimo', 2.0));
        $this->assertSame(1.50, \App\Services\FamilyHealthService::calculateComponentIIIPoints('bom', 2.0));
        $this->assertSame(1.00, \App\Services\FamilyHealthService::calculateComponentIIIPoints('suficiente', 2.0));
        $this->assertSame(0.50, \App\Services\FamilyHealthService::calculateComponentIIIPoints('regular', 2.0));
    }

    public function test_c2_monthly_tracking_and_filters(): void
    {
        $this->authenticateUser();

        // Seed team snapshot for C2
        \App\Models\FamilyHealthIndicatorSnapshot::query()->create([
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
            ->assertSee('Síntese da Avaliação Quadrimestral · C2')
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
        $this->artisan('esus:process-data', ['--scope' => 'c2', '--year' => 2026, '--quarter' => 1])
            ->assertExitCode(1);

        $c2Snapshots = \App\Models\FamilyHealthIndicatorSnapshot::query()
            ->where('indicator_code', 'c2')
            ->where('year', 2026)
            ->where('quarter', 1)
            ->count();

        $this->assertSame(0, $c2Snapshots);
    }

    public function test_old_c2_snapshots_are_hidden_until_recalculated_from_dw(): void
    {
        $this->authenticateUser();
        \App\Models\FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026, 'quarter' => 1, 'ine' => null,
            'team_name' => 'Consolidado Municipal', 'team_type' => '70',
            'indicator_code' => 'c2', 'numerator' => 800, 'denominator' => 10,
            'score_percent' => 80, 'performance_level' => 'otimo',
        ]);

        $service = app(\App\Services\FamilyHealthService::class);
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
        $source = \Mockery::mock(\App\Services\C2DwService::class);
        $source->shouldReceive('extract')->once()->andReturn([
            '0000171220' => [
                1 => ['numerator' => 80, 'denominator' => 2, 'score_percent' => 40.0,
                    'practices' => ['A' => 1, 'B' => 1, 'C' => 1, 'D' => 1, 'E' => 0], 'incomplete' => 2],
                3 => ['numerator' => 80, 'denominator' => 1, 'score_percent' => 80.0,
                    'practices' => ['A' => 1, 'B' => 1, 'C' => 1, 'D' => 1, 'E' => 0], 'incomplete' => 1],
            ],
        ]);
        $writer = new \App\Services\C2SnapshotService($source);
        $stats = $writer->process(DB::connection('sqlite'), 2026, 1, $team);

        $this->assertSame(['children' => 3, 'teams' => 1, 'months' => 2], $stats);
        $detail = app(\App\Services\FamilyHealthService::class)->getIndicatorDetail('c2', 2026, 1);
        $this->assertSame(60.0, $detail['current']['score_percent']);
        $this->assertSame(2, $detail['quarter_summary']['valid_months']);
        $this->assertNull($detail['monthly_evolution'][1]['score_percent']);
        $this->assertSame(3, $detail['current']['denominator']);
        $this->assertSame(160, $detail['current']['numerator']);
    }

    public function test_c2_read_failure_preserves_existing_snapshots(): void
    {
        \App\Models\FamilyHealthIndicatorSnapshot::query()->create([
            'year' => 2026, 'quarter' => 1, 'ine' => '0000171220',
            'team_name' => 'ESF CENTRO', 'team_type' => '70',
            'indicator_code' => 'c2', 'numerator' => 40, 'denominator' => 1,
            'score_percent' => 40, 'performance_level' => 'suficiente',
        ]);
        $source = \Mockery::mock(\App\Services\C2DwService::class);
        $source->shouldReceive('extract')->once()->andThrow(new \RuntimeException('DW indisponível'));

        try {
            (new \App\Services\C2SnapshotService($source))->process(DB::connection('sqlite'), 2026, 1, [
                '0000171220' => ['ine' => '0000171220', 'name' => 'ESF CENTRO', 'type' => '70'],
            ]);
            $this->fail('A leitura deveria falhar.');
        } catch (\RuntimeException $e) {
            $this->assertSame('DW indisponível', $e->getMessage());
        }

        $this->assertSame(1, \App\Models\FamilyHealthIndicatorSnapshot::query()
            ->where('indicator_code', 'c2')->count());
    }
}
