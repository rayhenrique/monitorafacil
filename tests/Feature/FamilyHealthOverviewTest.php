<?php

namespace Tests\Feature;

use App\Models\FamilyHealthIndicatorSnapshot;
use App\Models\User;
use App\Services\C1DwService;
use App\Services\C2DwService;
use App\Services\C3DwService;
use App\Services\FamilyHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FamilyHealthOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    public function test_overview_page_renders_with_target_structure_and_real_classifications(): void
    {
        $this->authenticateUser();

        // Seed some team snapshots
        FamilyHealthIndicatorSnapshot::create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => '0000171085',
            'team_name' => 'ESF BOA VISTA',
            'team_type' => '70',
            'indicator_code' => 'c1',
            'numerator' => 60,
            'denominator' => 100,
            'score_percent' => 60.00,
            'performance_level' => 'otimo',
            'good_practices_breakdown' => [
                'calculation_version' => C1DwService::VERSION,
            ],
        ]);

        FamilyHealthIndicatorSnapshot::create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => '0000171086',
            'team_name' => 'ESF CENTRO',
            'team_type' => '70',
            'indicator_code' => 'c1',
            'numerator' => 40,
            'denominator' => 100,
            'score_percent' => 40.00,
            'performance_level' => 'bom',
            'good_practices_breakdown' => [
                'calculation_version' => C1DwService::VERSION,
            ],
        ]);

        $response = $this->get(route('family-health.overview', ['ano' => 2026, 'quadrimestre' => 3]));
        $response->assertOk();
        $response->assertSee('Componente de Qualidade / Saúde da Família - Quadrimestre');
        $response->assertSee('Busca Avançada');
        $response->assertSee('Saúde da Família');
        $response->assertSee('Monitoramento dos Indicadores de Atenção Básica');
        $response->assertSee('Quadrimestre: 2026 / Q3');
        $response->assertSee('Mais Acesso');
        $response->assertSee('Ampliação do Acesso à Atenção Básica');
        $response->assertSee('Ótimo');
        $response->assertSee('Bom');
        $response->assertSee('Suficiente');
        $response->assertSee('Regular');
    }

    public function test_advanced_search_modal_toggle_and_filtering(): void
    {
        $this->authenticateUser();

        FamilyHealthIndicatorSnapshot::create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => '0000999111',
            'team_name' => 'ESF RURAL 1',
            'team_type' => '70',
            'indicator_code' => 'c1',
            'numerator' => 50,
            'denominator' => 100,
            'score_percent' => 50.00,
            'performance_level' => 'bom',
            'good_practices_breakdown' => [
                'calculation_version' => C1DwService::VERSION,
            ],
        ]);

        Livewire::test(\App\Livewire\FamilyHealth\FamilyHealthOverview::class, ['ano' => 2026, 'quadrimestre' => 3])
            ->assertSet('advancedSearchOpen', false)
            ->call('toggleAdvancedSearch')
            ->assertSet('advancedSearchOpen', true)
            ->assertSee('Busca Avançada · Saúde da Família')
            ->assertSee('ESF RURAL 1')
            ->set('searchTeamQuery', 'INEXISTENTE')
            ->assertDontSee('ESF RURAL 1')
            ->set('searchTeamQuery', '999111')
            ->assertSee('ESF RURAL 1')
            ->call('selectPeriod', 2026, 1)
            ->assertSet('year', 2026)
            ->assertSet('quarter', 1)
            ->assertSet('advancedSearchOpen', false)
            ->call('toggleAdvancedSearch')
            ->assertSet('advancedSearchOpen', true)
            ->call('closeAdvancedSearch')
            ->assertSet('advancedSearchOpen', false);
    }
}
