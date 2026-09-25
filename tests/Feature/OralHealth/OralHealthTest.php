<?php

declare(strict_types=1);

namespace Tests\Feature\OralHealth;

use App\Livewire\OralHealth\IndicatorDetail;
use App\Livewire\OralHealth\MonthlyDashboard;
use App\Livewire\OralHealth\NominalList;
use App\Livewire\OralHealth\Overview;
use App\Models\OralHealth\OralHealthIndicatorSnapshot;
use App\Models\OralHealth\OralHealthMonthlySnapshot;
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

    public function test_authenticated_user_can_access_oral_health_nominal_list(): void
    {
        $this->authenticateUser();

        \App\Models\OralHealth\OralHealthNominalCitizen::create([
            'year' => 2026,
            'quarter' => 3,
            'month' => 9,
            'cidadao_pec_id' => 999991,
            'cns' => '700123456789012',
            'cpf' => '12345678901',
            'name' => 'MARIA DENTAL TESTE',
            'birth_date' => '1995-05-10',
            'age_years' => 31,
            'cnes' => '2722569',
            'facility_name' => 'UBS CENTRO',
            'ine' => '0001749196',
            'team_name' => 'ESB 001',
            'microarea' => '01',
            'mici_updated' => true,
            'b1_count' => 1,
            'b2_count' => 1,
            'b3_count' => 0,
            'b4_count' => 0,
            'b4_eligible' => false,
            'b5_count' => 2,
            'b6_count' => 0,
            'treatment_status' => 'concluido',
            'calculation_version' => 'test-v1',
        ]);

        $response = $this->get(route('oral-health.nominal'));
        $response->assertOk();
        $response->assertSee('Componente de Qualidade / Saúde Bucal');
        $response->assertSee('MARIA DENTAL TESTE');
        $response->assertSee('Busca Avançada');
        $response->assertSee('Legenda dos Indicadores de Saúde Bucal');

        // Single footer policy
        $footerCount = substr_count($response->getContent(), '<footer');
        $this->assertSame(1, $footerCount);
    }

    public function test_nominal_list_livewire_component_filters_and_properties(): void
    {
        $this->authenticateUser();

        \App\Models\OralHealth\OralHealthNominalCitizen::create([
            'year' => 2026,
            'quarter' => 3,
            'month' => 9,
            'cidadao_pec_id' => 999992,
            'cns' => '700987654321098',
            'cpf' => '98765432100',
            'name' => 'CARLOS PACIENTE FILTRO',
            'birth_date' => '2015-02-15',
            'age_years' => 11,
            'cnes' => '2719738',
            'facility_name' => 'UBS ALVORADA',
            'ine' => '0001748734',
            'team_name' => 'ESB 002',
            'microarea' => '03',
            'mici_updated' => false,
            'b1_count' => 0,
            'b2_count' => 0,
            'b3_count' => 0,
            'b4_count' => 1,
            'b4_eligible' => true,
            'b5_count' => 1,
            'b6_count' => 0,
            'treatment_status' => 'em_andamento',
            'calculation_version' => 'test-v1',
        ]);

        Livewire::test(\App\Livewire\OralHealth\NominalList::class)
            ->assertSee('CARLOS PACIENTE FILTRO')
            ->set('filterName', 'CARLOS')
            ->assertSee('CARLOS PACIENTE FILTRO')
            ->set('filterName', 'INEXISTENTE')
            ->assertDontSee('CARLOS PACIENTE FILTRO')
            ->set('filterName', '')
            ->set('filterCns', '700987654321098')
            ->assertSee('CARLOS PACIENTE FILTRO')
            ->set('filterCns', '')
            ->set('filterCpf', '98765432100')
            ->assertSee('CARLOS PACIENTE FILTRO');
    }

    public function test_nominal_list_advanced_modal_and_details_modal(): void
    {
        $this->authenticateUser();

        $c = \App\Models\OralHealth\OralHealthNominalCitizen::create([
            'year' => 2026,
            'quarter' => 3,
            'month' => 9,
            'cidadao_pec_id' => 999993,
            'cns' => '700555555555555',
            'cpf' => '55555555555',
            'name' => 'FERNANDA MODAL DETALHES',
            'birth_date' => '1990-08-20',
            'age_years' => 36,
            'cnes' => '2722569',
            'facility_name' => 'UBS CENTRAL',
            'ine' => '0001749196',
            'team_name' => 'ESB 001',
            'microarea' => '02',
            'mici_updated' => true,
            'b1_count' => 2,
            'b2_count' => 1,
            'b3_count' => 1,
            'b4_count' => 0,
            'b4_eligible' => false,
            'b5_count' => 3,
            'b6_count' => 1,
            'first_consultation_date' => '2026-06-10',
            'treatment_completed_date' => '2026-07-15',
            'last_professional_name' => 'DR DENTISTA SILVA',
            'treatment_status' => 'concluido',
            'calculation_version' => 'test-v1',
        ]);

        Livewire::test(\App\Livewire\OralHealth\NominalList::class)
            ->call('openAdvancedModal')
            ->assertSet('advancedModalOpen', true)
            ->set('advB1', 'SIM')
            ->call('applyAdvancedFilters')
            ->assertSee('FERNANDA MODAL DETALHES')
            ->call('openDetails', $c->id)
            ->assertSet('detailsModalOpen', true)
            ->assertSee('DR DENTISTA SILVA')
            ->assertSee('Linha do Tempo Odontológica')
            ->call('closeDetails')
            ->assertSet('detailsModalOpen', false);
    }

    public function test_authenticated_user_can_access_monthly_dashboard(): void
    {
        $this->authenticateUser();

        // Seed monthly snapshots for 2 teams
        $indicators = ['b1', 'b2', 'b3', 'b4', 'b5', 'b6'];
        foreach ($indicators as $ind) {
            OralHealthMonthlySnapshot::create([
                'year' => 2026,
                'month' => 9,
                'quarter' => 3,
                'month_in_quarter' => 1,
                'ine' => '0001748734',
                'team_name' => 'ESB 001',
                'cnes' => '2719738',
                'facility_name' => '01 CENTRO DE SAUDE MANUEL A DE SANTANA',
                'team_type' => '70',
                'indicator_code' => $ind,
                'numerator' => 15,
                'denominator' => 30,
                'score_percent' => 50.00,
                'performance_level' => 'otimo',
            ]);
        }

        $response = $this->get(route('oral-health.monthly', ['ano' => 2026, 'mes' => 9]));
        $response->assertOk();
        $response->assertSee('Componente de Qualidade / Saúde Bucal - Dashboard Equipes');
        $response->assertSee('Saúde Bucal');
        $response->assertSee('Monitoramento dos Indicadores de Atenção Básica');
        $response->assertSee('Mês:');
        $response->assertSee('2026 / M9');
        $response->assertSee('Detalhamento por Unidade');
        $response->assertSee('0001748734 - ESB 001');
        $response->assertSee('2719738 - 01 CENTRO DE SAUDE MANUEL A DE SANTANA');
        $response->assertSee('B1 - Programada');
        $response->assertSee('B2 - Concluído');
        $response->assertSee('B3 - Exodontias');
        $response->assertSee('B4 - Supervisionada');
        $response->assertSee('B5 - Procedimentos');
        $response->assertSee('B6 - Atraumático');
        $response->assertSee('Total de Registros');

        // Assert single footer policy
        $content = $response->getContent();
        $footerCount = substr_count($content, '<footer');
        $this->assertSame(1, $footerCount, 'There must be exactly one <footer> in the entire page output (Single Footer Policy).');
    }

    public function test_monthly_dashboard_filters_and_modal(): void
    {
        $this->authenticateUser();

        OralHealthMonthlySnapshot::create([
            'year' => 2026,
            'month' => 9,
            'quarter' => 3,
            'month_in_quarter' => 1,
            'ine' => '0001748734',
            'team_name' => 'ESB 001',
            'cnes' => '2719738',
            'facility_name' => '01 CENTRO DE SAUDE MANUEL A DE SANTANA',
            'team_type' => '70',
            'indicator_code' => 'b1',
            'numerator' => 10,
            'denominator' => 20,
            'score_percent' => 50.00,
            'performance_level' => 'otimo',
        ]);

        OralHealthMonthlySnapshot::create([
            'year' => 2026,
            'month' => 9,
            'quarter' => 3,
            'month_in_quarter' => 1,
            'ine' => '0001748815',
            'team_name' => 'ESB 002',
            'cnes' => '2719886',
            'facility_name' => '02 CENTRO DE SAUDE TEOTONIO VILELA',
            'team_type' => '70',
            'indicator_code' => 'b1',
            'numerator' => 5,
            'denominator' => 20,
            'score_percent' => 25.00,
            'performance_level' => 'bom',
        ]);

        Livewire::test(MonthlyDashboard::class)
            ->set('selectedMonth', 9)
            ->assertSee('ESB 001')
            ->assertSee('ESB 002')
            ->set('filterTeam', '0001748734')
            ->assertSee('ESB 001')
            ->assertDontSee('ESB 002')
            ->call('clearFilters')
            ->assertSee('ESB 001')
            ->assertSee('ESB 002')
            ->call('openAdvancedModal')
            ->assertSet('advancedModalOpen', true)
            ->set('advCnes', '2719886')
            ->call('applyAdvancedFilters')
            ->assertSet('filterCnes', '2719886')
            ->assertDontSee('ESB 001')
            ->assertSee('ESB 002');
    }

    public function test_monthly_dashboard_csv_export(): void
    {
        $this->authenticateUser();
        \Illuminate\Support\Carbon::setTestNow('2026-09-25 12:00:00');

        OralHealthMonthlySnapshot::create([
            'year' => 2026,
            'month' => 9,
            'quarter' => 3,
            'month_in_quarter' => 1,
            'ine' => '0001748734',
            'team_name' => 'ESB 001',
            'cnes' => '2719738',
            'facility_name' => '01 CENTRO DE SAUDE MANUEL A DE SANTANA',
            'team_type' => '70',
            'indicator_code' => 'b1',
            'numerator' => 10,
            'denominator' => 20,
            'score_percent' => 50.00,
            'performance_level' => 'otimo',
        ]);

        Livewire::test(MonthlyDashboard::class)
            ->call('exportCsv')
            ->assertFileDownloaded('dashboard_mensal_saude_bucal_2026_M09_20260925_120000.csv');

        \Illuminate\Support\Carbon::setTestNow();
    }
}
