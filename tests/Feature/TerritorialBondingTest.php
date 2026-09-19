<?php

namespace Tests\Feature;

use App\Livewire\TerritorialBonding\TerritorialBondingOverview;
use App\Models\User;
use App\Services\CvatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TerritorialBondingTest extends TestCase
{
    public function test_guest_is_redirected_to_login_when_accessing_territorial_bonding(): void
    {
        $response = $this->get('/vinculo-e-acompanhamento');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_territorial_bonding_overview(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/vinculo-e-acompanhamento');

        $response->assertOk();
        $response->assertSee('Vínculo e Acompanhamento Territorial');
        $response->assertSee('CVAT - Dimensão Cadastro - eSF');
        $response->assertSee('CVAT - Dimensão Acompanhamento - eSF');
    }

    public function test_cvat_service_imports_and_returns_correct_distributions_and_summary(): void
    {
        $service = app(CvatService::class);
        $summary = $service->getMunicipalSummary(2026, 1);
        $charts = $service->getDimensionChartsData();

        $this->assertTrue($summary['has_data']);
        $this->assertEquals(19, $summary['total_teams']);
        $this->assertGreaterThan(8.5, $summary['average_final_score']);
        $this->assertEquals('ÓTIMO', $summary['municipal_classification']);

        $this->assertCount(3, $charts['cadastro']);
        $this->assertCount(3, $charts['acompanhamento']);

        // Verifica os números de Q1/26 na Dimensão Cadastro: Regular 1, Suficiente 0, Bom 3, Ótimo 15
        $q1Cadastro = collect($charts['cadastro'])->firstWhere('period', 'Q1/26');
        $this->assertEquals(1, $q1Cadastro['regular']);
        $this->assertEquals(0, $q1Cadastro['sufficient']);
        $this->assertEquals(3, $q1Cadastro['good']);
        $this->assertEquals(15, $q1Cadastro['optimal']);

        // Verifica os números de Q1/26 na Dimensão Acompanhamento: Regular 1, Suficiente 1, Bom 7, Ótimo 10
        $q1Acomp = collect($charts['acompanhamento'])->firstWhere('period', 'Q1/26');
        $this->assertEquals(1, $q1Acomp['regular']);
        $this->assertEquals(1, $q1Acomp['sufficient']);
        $this->assertEquals(7, $q1Acomp['good']);
        $this->assertEquals(10, $q1Acomp['optimal']);
    }

    public function test_livewire_component_filters_teams_by_classification(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TerritorialBondingOverview::class)
            ->assertSet('selectedYear', 2026)
            ->assertSet('selectedQuarter', 1)
            ->assertSee('01 06 CS MANUEL A DE SANTANA')
            ->call('filterByClassification', 'REGULAR')
            ->assertSet('classificationFilter', 'REGULAR')
            ->assertSee('ESF MATAO DO ROBERTO')
            ->assertDontSee('01 06 CS MANUEL A DE SANTANA');
    }
}
