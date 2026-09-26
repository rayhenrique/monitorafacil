<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\QualityOverview;
use App\Livewire\Dashboard\RegistrationsOverview;
use App\Livewire\Dashboard\TeamsOverview;
use Database\Seeders\RealDashboardDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardRealDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_exact_real_official_data_for_2026_q3(): void
    {
        // Executa o seeder com os dados oficiais reais
        $this->seed(RealDashboardDataSeeder::class);

        // 1. Vínculo e Acompanhamento (RegistrationsOverview)
        Livewire::test(RegistrationsOverview::class, ['year' => 2026, 'quarter' => 3])
            ->assertOk()
            // Card 1: Classificação do Quadrimestre
            ->assertSee('Classificação do Quadrimestre')
            ->assertSee('Desempenho das equipes de eSF')
            ->assertSee('Distribuição oficial das equipes no modelo de cofinanciamento.')
            ->assertSee('7')
            ->assertSee('ÓTIMO')
            ->assertSee('8')
            ->assertSee('BOM')
            ->assertSee('3')
            ->assertSee('SUFICIENTE')
            ->assertSee('1')
            ->assertSee('REGULAR')
            // Card 2: Total MICI
            ->assertSee('Total MICI atualizados')
            ->assertSee('36.705')
            ->assertSee('99,20%')
            ->assertSee('37.002')
            ->assertSee('297')
            ->assertSee('2026 / M09')
            // Card 3: Total MICI + MICDT
            ->assertSee('Total MICI + MICDT atualizados')
            ->assertSee('35.939')
            ->assertSee('99,04%')
            ->assertSee('36.289')
            ->assertSee('116')
            ->assertSee('2026 / M09');

        // 2. Equipes Homologadas (TeamsOverview)
        Livewire::test(TeamsOverview::class, ['year' => 2026, 'quarter' => 3])
            ->assertOk()
            ->assertSee('19')
            ->assertSee('8')
            ->assertSee('2')
            ->assertSee('29');

        // 3. Componente de Qualidade (QualityOverview)
        // Quando não houver extração clínica real para C1..C7, B1..B6 e M1..M2,
        // não exibe zeros simulados (0 Ótimo, 0 Bom...), e sim o estado vazio limpo.
        Livewire::test(QualityOverview::class, ['year' => 2026, 'quarter' => 3])
            ->assertOk()
            ->assertSee('Sem consolidação disponível neste período')
            ->assertDontSee('0 Ótimo');
    }

    public function test_dashboard_renders_empty_cleanly_when_quarter_has_no_data(): void
    {
        // 2025 Q1 não possui registros nem consolidações
        Livewire::test(RegistrationsOverview::class, ['year' => 2025, 'quarter' => 1])
            ->assertOk()
            ->assertSee('Sem consolidação disponível neste período');

        Livewire::test(TeamsOverview::class, ['year' => 2025, 'quarter' => 1])
            ->assertOk()
            ->assertSee('—');

        Livewire::test(QualityOverview::class, ['year' => 2025, 'quarter' => 1])
            ->assertOk()
            ->assertSee('Sem consolidação disponível neste período');
    }

    public function test_quality_overview_displays_oral_health_real_indicators_when_available(): void
    {
        \App\Models\OralHealth\OralHealthIndicatorSnapshot::create([
            'year' => 2026,
            'quarter' => 3,
            'ine' => '0001746014',
            'team_name' => 'ESB 013',
            'cnes' => '2722607',
            'facility_name' => 'Usf 13 Jose Belarmino Soares',
            'team_type' => '88',
            'indicator_code' => 'b1',
            'numerator' => 59,
            'denominator' => 1749,
            'score_percent' => 3.37,
            'performance_level' => 'regular',
            'good_practices_breakdown' => ['points' => 0.5, 'weight' => 2],
            'active_search_count' => 0,
        ]);

        Livewire::test(QualityOverview::class, ['year' => 2026, 'quarter' => 3])
            ->assertOk()
            ->assertSee('Primeira Consulta Programada')
            ->assertSee('1 equipe eSB avaliada com consolidação válida')
            ->assertSeeHtml('<p class="text-lg font-semibold tabular-nums text-rose-700">1</p>');
    }
}
