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
            ->assertSee($firstCitizen->no_cidadao)
            ->assertSet('filterCns', '')
            ->assertSet('filterCpf', '')
            ->assertSet('filterNome', '');
    }

    public function test_nominal_list_filters_by_citizen_name(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        app(CvatNominalDwService::class)->syncFromPec();

        Livewire::test(NominalList::class)
            ->set('filterNome', 'ABEL ANDREZA')
            ->assertSee('ABEL ANDREZA')
            ->set('filterNome', 'NOME_INEXISTENTE_XYZ')
            ->assertSee('Nenhum cidadão encontrado com os filtros aplicados');
    }

    public function test_nominal_list_filters_by_raca_cor(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        app(CvatNominalDwService::class)->syncFromPec();

        Livewire::test(NominalList::class)
            ->set('filterRacaCor', 'Amarela')
            ->assertSee('Amarela');
    }

    public function test_nominal_list_modal_citizen_details(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        app(CvatNominalDwService::class)->syncFromPec();

        $citizen = CvatNominalCitizen::first();

        Livewire::test(NominalList::class)
            ->assertSet('showDetailModal', false)
            ->call('openCitizenModal', $citizen->id)
            ->assertSet('showDetailModal', true)
            ->assertSee($citizen->no_cidadao)
            ->call('closeCitizenModal')
            ->assertSet('showDetailModal', false);
    }

    public function test_nominal_list_modal_advanced_search(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        Livewire::test(NominalList::class)
            ->assertSet('showAdvancedModal', false)
            ->call('openAdvancedModal')
            ->assertSet('showAdvancedModal', true)
            ->assertSee('Busca Avançada de Cidadãos')
            ->call('closeAdvancedModal')
            ->assertSet('showAdvancedModal', false);
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
