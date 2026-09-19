<?php

namespace Tests\Feature;

use App\Livewire\TerritorialBonding\NominalList;
use App\Models\CvatNominalCitizen;
use App\Models\CvatNominalMetric;
use App\Models\User;
use App\Services\CvatNominalDwService;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class CvatNominalListTest extends TestCase
{
    private function createUser(): User
    {
        return User::query()->create([
            'name' => 'Gestor Municipal Teste',
            'email' => 'gestor-nominal@monitorafacil.com.br',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/vinculo-e-acompanhamento/relacao-nominal');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_nominal_list_page(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        // Garante seed dos dados
        app(CvatNominalDwService::class)->syncFromPec();

        $response = $this->get('/vinculo-e-acompanhamento/relacao-nominal');

        $response->assertOk();
        $response->assertSee('Monitoramento de Vínculo e Acompanhamento - Relação Nominal');
        $response->assertSee('DIMENSÃO CADASTRO');
        $response->assertSee('DIMENSÃO ACOMPANHAMENTO');
        $response->assertSee('36.951');
        $response->assertSee('35.401');
        $response->assertSee('36.751');
        $response->assertSee('2.047');
    }

    public function test_nominal_list_livewire_component_renders_metrics_and_citizens(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        app(CvatNominalDwService::class)->syncFromPec();

        $firstCitizen = CvatNominalCitizen::first();

        Livewire::test(NominalList::class)
            ->assertSee('36.951')
            ->assertSee('20.550')
            ->assertSee('7.617')
            ->assertSee('7.688')
            ->assertSee('1.096')
            ->assertSee($firstCitizen->name)
            ->assertSet('filterCns', '')
            ->assertSet('filterCpf', '')
            ->assertSet('filterName', '');
    }

    public function test_nominal_list_filters_by_citizen_name(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        app(CvatNominalDwService::class)->syncFromPec();

        Livewire::test(NominalList::class)
            ->set('filterName', 'ABEL ANDREZA')
            ->assertSee('ABEL ANDREZA')
            ->set('filterName', 'NOME_INEXISTENTE_XYZ')
            ->assertSee('Nenhum cidadão encontrado');
    }

    public function test_nominal_list_filters_by_raca_cor(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        app(CvatNominalDwService::class)->syncFromPec();

        Livewire::test(NominalList::class)
            ->set('filterRaceColor', 'Amarela')
            ->assertSee('Amarela');
    }

    public function test_nominal_list_modal_citizen_details(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        app(CvatNominalDwService::class)->syncFromPec();

        $citizen = CvatNominalCitizen::first();

        Livewire::test(NominalList::class)
            ->assertSet('detailsModalOpen', false)
            ->call('openDetails', $citizen->id)
            ->assertSet('detailsModalOpen', true)
            ->assertSee($citizen->name)
            ->call('closeDetails')
            ->assertSet('detailsModalOpen', false);
    }

    public function test_nominal_list_modal_advanced_search(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        Livewire::test(NominalList::class)
            ->assertSet('advancedModalOpen', false)
            ->call('openAdvancedModal')
            ->assertSet('advancedModalOpen', true)
            ->assertSee('Busca Avançada')
            ->call('closeAdvancedModal')
            ->assertSet('advancedModalOpen', false);
    }

    public function test_cvat_nominal_metric_calculates_scores_according_to_nt_30_2025(): void
    {
        $metric = new CvatNominalMetric([
            'year' => 2026,
            'month' => 9,
            'mici_total' => 36951,
            'mici_updated' => 35401,
            'mici_outdated' => 1550,
            'mici_updated_micdt_outdated_or_none' => 14851,
            'mici_and_micdt_updated' => 20550,
            'no_criteria_accompanied' => 2047,
            'elderly_or_child_accompanied' => 3804,
            'bpc_or_pbf_accompanied' => 4565,
            'elderly_child_and_benefit_accompanied' => 1096,
        ]);

        $this->assertEquals(47500, $metric->target_population);

        // Teste de cálculo de X e Y
        $this->assertGreaterThan(50.0, $metric->index_x);
        $this->assertGreaterThan(0.0, $metric->index_y);
        $this->assertContains($metric->classification_x, ['Ótimo', 'Bom', 'Suficiente', 'Regular']);
        $this->assertContains($metric->classification_y, ['Ótimo', 'Bom', 'Suficiente', 'Regular']);
        $this->assertContains($metric->final_classification, ['ÓTIMO', 'BOM', 'SUFICIENTE', 'REGULAR']);
        $this->assertLessThanOrEqual(10.0, $metric->final_score);
    }

    public function test_data_processing_has_exclusive_cvat_action_and_executes(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        Livewire::test(\App\Livewire\Settings\DataProcessing::class)
            ->assertSee('Processar Vínculo e Acompanhamento')
            ->call('processCvat')
            ->assertSet('processStatus', 'success')
            ->assertSet('selectedScope', 'cvat')
            ->assertSee('Processamento do módulo Vínculo e Acompanhamento Territorial concluído com sucesso');
    }
}
