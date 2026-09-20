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
            ->assertSee('47.480')
            ->assertSee('70,44%')
            ->assertSee('67.403')
            ->assertSee('19.923')
            ->assertSee('2026 / M12')
            // Card 3: Total MICI + MICDT
            ->assertSee('Total MICI + MICDT atualizados')
            ->assertSee('18.363')
            ->assertSee('71,36%')
            ->assertSee('25.732')
            ->assertSee('7.369')
            ->assertSee('2026 / M12');

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
}
