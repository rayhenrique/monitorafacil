<?php

declare(strict_types=1);

namespace Tests\Feature\OralHealth;

use App\Livewire\OralHealth\IndicatorDetail;
use App\Livewire\OralHealth\Overview;
use App\Models\OralHealth\OralHealthIndicatorSnapshot;
use App\Models\OralHealth\OralHealthNominalPatient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OralHealthTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_guest_is_redirected_to_login_on_oral_health_routes(): void
    {
        $response = $this->get(route('oral-health.overview'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('oral-health.indicator', 'b1'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_oral_health_overview(): void
    {
        $this->authenticateUser();

        // Seed municipal snapshot
        OralHealthIndicatorSnapshot::create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => null,
            'indicator_code' => 'b1',
            'numerator' => 120,
            'denominator' => 100,
            'score_percent' => 1.20,
            'performance_level' => 'bom',
            'active_search_count' => 120,
        ]);

        $response = $this->get(route('oral-health.overview', ['ano' => 2026, 'quadrimestre' => 3]));
        $response->assertOk();
        $response->assertSee('Saúde Bucal (eSB)');
        $response->assertSee('Pontuação Municipal');
        $response->assertSee('B1');
        $response->assertSee('B2');
        $response->assertSee('B3');
        $response->assertSee('B4');
        $response->assertSee('B5');
        $response->assertSee('B6');
        $response->assertSee('Primeira Consulta');
        $response->assertSee('Tratamento Concluído');
        $response->assertSee('Taxa de Exodontia');
        $response->assertSee('Escovação Supervisionada');
        $response->assertSee('Procedimentos Preventivos');
        $response->assertSee('Restauração Atraumática');
    }

    public function test_authenticated_user_can_access_all_six_indicator_detail_pages(): void
    {
        $this->authenticateUser();

        $indicators = ['b1', 'b2', 'b3', 'b4', 'b5', 'b6'];

        foreach ($indicators as $ind) {
            OralHealthIndicatorSnapshot::create([
                'year' => 2026,
                'quarter' => 3,
                'ine' => '1234567890',
                'team_name' => 'ESB TESTE 01',
                'cnes' => '9999999',
                'facility_name' => 'USF CENTRAL',
                'team_type' => '88',
                'indicator_code' => $ind,
                'numerator' => 50,
                'denominator' => 100,
                'score_percent' => 50.00,
                'performance_level' => 'suficiente',
                'active_search_count' => 10,
            ]);

            $response = $this->get(route('oral-health.indicator', ['indicator' => $ind, 'ano' => 2026, 'quadrimestre' => 3]));
            $response->assertOk();
            $response->assertSee(strtoupper($ind));
            $response->assertSee('Resumo das Equipes');
            $response->assertSee('Busca Ativa');
            $response->assertSee('ESB TESTE 01');
        }
    }

    public function test_invalid_indicator_returns_404(): void
    {
        $this->authenticateUser();

        $response = $this->get('/saude-bucal/b99');
        $response->assertNotFound();
    }

    public function test_overview_livewire_component_filters_and_properties(): void
    {
        $this->authenticateUser();

        Livewire::test(Overview::class, ['year' => 2026, 'quarter' => 3])
            ->assertSet('year', 2026)
            ->assertSet('quarter', 3)
            ->assertSet('advancedSearchOpen', false)
            ->call('toggleAdvancedSearch')
            ->assertSet('advancedSearchOpen', true)
            ->call('toggleAdvancedSearch')
            ->assertSet('advancedSearchOpen', false);
    }

    public function test_indicator_detail_livewire_tab_switching_and_nominal_search(): void
    {
        $this->authenticateUser();

        // Seed snapshot and nominal patient
        OralHealthIndicatorSnapshot::create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => '1111222233',
            'team_name' => 'ESB VILA PROGRESSO',
            'cnes' => '8888888',
            'facility_name' => 'USF VILA PROGRESSO',
            'team_type' => '88',
            'indicator_code' => 'b1',
            'numerator' => 1,
            'denominator' => 1,
            'score_percent' => 1.0,
            'performance_level' => 'bom',
            'active_search_count' => 1,
        ]);

        OralHealthNominalPatient::create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => '1111222233',
            'team_name' => 'ESB VILA PROGRESSO',
            'cnes' => '8888888',
            'facility_name' => 'USF VILA PROGRESSO',
            'cpf' => '12345678901',
            'cns' => '700000000000001',
            'name' => 'MARIA DAS GRACAS SILVA',
            'birth_date' => '1990-05-15',
            'indicator_code' => 'b1',
            'professional_name' => 'DRA ANA ODONTOLOGA',
            'professional_cbo' => '223208',
            'calculation_version' => '1.0.0',
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'b1', 'year' => 2026, 'quarter' => 3])
            ->assertSet('activeTab', 'monthly_summary')
            ->assertSee('ESB VILA PROGRESSO')
            ->call('setTab', 'nominal')
            ->assertSet('activeTab', 'nominal')
            ->assertSee('MARIA DAS GRACAS SILVA')
            ->set('search', 'SILVA')
            ->assertSee('MARIA DAS GRACAS SILVA')
            ->set('search', 'NOME_INEXISTENTE')
            ->assertDontSee('MARIA DAS GRACAS SILVA');
    }

    public function test_zero_denominator_renders_gracefully(): void
    {
        $this->authenticateUser();

        OralHealthIndicatorSnapshot::create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => '9999000011',
            'team_name' => 'ESB NOVA SEM ATENDIMENTO',
            'cnes' => '7777777',
            'facility_name' => 'USF NOVA',
            'team_type' => '88',
            'indicator_code' => 'b2',
            'numerator' => 0,
            'denominator' => 0,
            'score_percent' => 0.00,
            'performance_level' => 'regular',
            'active_search_count' => 0,
        ]);

        $response = $this->get(route('oral-health.indicator', ['indicator' => 'b2', 'ano' => 2026, 'quadrimestre' => 3]));
        $response->assertOk();
        $response->assertSee('ESB NOVA SEM ATENDIMENTO');
        $response->assertSee('0,00%');
        $response->assertSee('Regular');
    }

    public function test_citizen_without_cpf_and_only_cns_is_supported_in_nominal_list(): void
    {
        $this->authenticateUser();

        OralHealthNominalPatient::create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => '5555444433',
            'team_name' => 'ESB BOA VISTA',
            'cnes' => '6666666',
            'facility_name' => 'USF BOA VISTA',
            'cpf' => null, // Cidadão sem CPF
            'cns' => '700123456789012', // Apenas CNS válido
            'name' => 'JOAO CIDADÃO SEM CPF',
            'birth_date' => '1985-03-20',
            'indicator_code' => 'b1',
            'professional_name' => 'DRA FLAVIA DENTISTA',
            'professional_cbo' => '223208',
            'calculation_version' => '1.0.0',
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'b1', 'year' => 2026, 'quarter' => 3])
            ->call('setTab', 'nominal')
            ->assertSee('JOAO CIDADÃO SEM CPF')
            ->assertSee('700123456789012')
            ->set('search', '700123456789012')
            ->assertSee('JOAO CIDADÃO SEM CPF');
    }

    public function test_metadata_defines_strict_authorized_cbos_for_indicators(): void
    {
        $this->authenticateUser();

        $metaB1 = \App\Services\OralHealth\OralHealthService::getIndicatorMeta('b1');
        $this->assertNotEmpty($metaB1['cbos']);
        // Cirurgião-Dentista
        $this->assertTrue(collect($metaB1['cbos'])->contains(fn ($cbo) => str_contains($cbo, '2232-08')));

        $metaB5 = \App\Services\OralHealth\OralHealthService::getIndicatorMeta('b5');
        // Preventivos inclui CD e TSB
        $this->assertTrue(collect($metaB5['cbos'])->contains(fn ($cbo) => str_contains($cbo, '3224-05') || str_contains($cbo, '3224-25')));
    }

    public function test_single_footer_policy_intact(): void
    {
        $this->authenticateUser();

        $response = $this->get(route('oral-health.overview'));
        $response->assertOk();

        // Count <footer> occurrences: must be exactly 1 (global layout footer)
        $content = $response->getContent();
        $footerCount = substr_count($content, '<footer');
        $this->assertSame(1, $footerCount, 'There must be exactly 1 <footer> in the entire HTML document.');
        $response->assertSee('Dados consolidados para apoio à gestão municipal da APS');
    }
}
