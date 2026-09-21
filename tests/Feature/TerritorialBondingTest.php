<?php

namespace Tests\Feature;

use App\Livewire\TerritorialBonding\TerritorialBondingOverview;
use App\Models\CvatNominalCitizen;
use App\Models\User;
use App\Services\CnesXmlParserService;
use App\Services\CvatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TerritorialBondingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $cnesParser = \Mockery::mock(CnesXmlParserService::class);
        $cnesParser->shouldReceive('getEligibleC1Teams')->andReturn([
            ['ine' => '0000000001', 'name' => 'EQUIPE ALFA', 'type' => '70', 'cnes' => '1111111'],
            ['ine' => '0000000002', 'name' => 'EQUIPE BETA', 'type' => '76', 'cnes' => '2222222'],
        ]);
        $this->app->instance(CnesXmlParserService::class, $cnesParser);

        $this->createNominalCitizen(1, '0000000001', 'EQUIPE ALFA', 'UNIDADE ALFA', '1111111', [
            'has_micdt' => true,
            'micdt_updated' => true,
            'vulnerability_type' => 'sem_criterio',
            'last_visit_date' => '2026-09-10',
        ]);
        $this->createNominalCitizen(2, '0000000001', 'EQUIPE ALFA', 'UNIDADE ALFA', '1111111', [
            'has_micdt' => false,
            'micdt_updated' => false,
            'vulnerability_type' => 'idoso',
            'last_visit_date' => '2026-09-12',
        ]);
        $this->createNominalCitizen(3, '0000000002', 'EQUIPE BETA', 'UNIDADE BETA', '2222222', [
            'has_micdt' => true,
            'micdt_updated' => true,
            'is_accompanied' => false,
            'last_visit_date' => null,
        ]);
        $this->createNominalCitizen(4, '0000000003', 'EQUIPE SAÚDE BUCAL', 'UNIDADE ALFA', '1111111');
        $this->createNominalCitizen(5, '0000000003', 'EQUIPE SAÚDE BUCAL', 'UNIDADE ALFA', '1111111', [
            'year' => 2027,
            'month' => 1,
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function createNominalCitizen(int $id, string $ine, string $team, string $facility, string $cnes, array $overrides = []): void
    {
        CvatNominalCitizen::query()->create(array_merge([
            'cidadao_pec_id' => $id,
            'name' => 'CIDADÃO TESTE '.$id,
            'cnes' => $cnes,
            'facility_name' => $facility,
            'ine' => $ine,
            'team_name' => $team,
            'mici_updated' => true,
            'has_micdt' => true,
            'micdt_updated' => true,
            'is_linked' => true,
            'vulnerability_type' => 'sem_criterio',
            'social_benefit' => 'nenhum',
            'is_accompanied' => true,
            'year' => 2026,
            'month' => 9,
        ], $overrides));
    }

    public function test_guest_is_redirected_to_login_when_accessing_territorial_bonding(): void
    {
        $response = $this->get('/vinculo-e-acompanhamento');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_is_redirected_to_nominal_list_when_opening_territorial_bonding(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/vinculo-e-acompanhamento');

        $response->assertRedirect('/vinculo-e-acompanhamento/relacao-nominal');
    }

    public function test_authenticated_user_accessing_cadastro_or_acompanhamento_is_shown_teams_monitoring(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/vinculo-e-acompanhamento?aba=cadastro');

        $response->assertOk();
        $response->assertSee('Monitoramento de Vínculo e Acompanhamento - Equipes (Mensal)');
        $response->assertSee('Sem dados reais de equipes para esta competência');
        $response->assertDontSee('EQUIPE ALFA');
    }

    public function test_cvat_service_does_not_classify_unverified_nominal_data(): void
    {
        $service = app(CvatService::class);
        $summary = $service->getMunicipalSummary(2026, 3);
        $this->assertFalse($summary['has_data']);
        $this->assertSame('NÃO AFERÍVEL', $summary['municipal_classification']);
        $this->assertCount(0, $service->getTeamsList(2026, 3));
        $this->assertSame([], $service->getAvailableQuarters());
    }

    public function test_livewire_component_does_not_list_unverified_teams(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TerritorialBondingOverview::class, ['activeTab' => 'teams'])
            ->assertSet('selectedYear', 2026)
            ->assertSet('selectedQuarter', 1)
            ->assertSee('Sem dados reais de equipes')
            ->assertDontSee('EQUIPE ALFA')
            ->set('filterClassification', 'REGULAR')
            ->assertDontSee('EQUIPE BETA');
    }

    public function test_teams_tab_does_not_fallback_to_demonstration_data(): void
    {
        CvatNominalCitizen::query()->delete();
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TerritorialBondingOverview::class, ['activeTab' => 'teams'])
            ->assertSee('Sem dados reais de equipes para esta competência')
            ->assertSee('Nenhum valor demonstrativo será exibido')
            ->assertDontSee('BENEDITO DE LIRA');
    }

    public function test_sidebar_marks_only_the_current_territorial_child_as_active(): void
    {
        $user = User::factory()->create();

        $nominalResponse = $this->actingAs($user)->get('/vinculo-e-acompanhamento/relacao-nominal');
        $teamsResponse = $this->actingAs($user)->get('/vinculo-e-acompanhamento?aba=teams');

        $this->assertSame(2, $this->activeSidebarLinks($nominalResponse->getContent(), 'relacao-nominal'));
        $this->assertSame(0, $this->activeSidebarLinks($nominalResponse->getContent(), 'aba=teams'));
        $this->assertSame(0, $this->activeSidebarLinks($teamsResponse->getContent(), 'relacao-nominal'));
        $this->assertSame(2, $this->activeSidebarLinks($teamsResponse->getContent(), 'aba=teams'));
    }

    private function activeSidebarLinks(string $html, string $hrefFragment): int
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($document);

        return $xpath->query("//nav[@aria-label='Navegação da aplicação']//a[@aria-current='page' and contains(@href, '{$hrefFragment}')]")?->length ?? 0;
    }

    public function test_methodological_guide_tab_renders_correctly(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TerritorialBondingOverview::class, ['activeTab' => 'guide'])
            ->assertSee('Caderno Metodológico · Componente Vínculo e Acompanhamento Territorial')
            ->assertSee('Nota Técnica nº 30/2025-CGESCO/DESCO/SAPS/MS')
            ->assertSee('Dimensão Cadastro (Índice X')
            ->assertSee('Dimensão Acompanhamento (Índice Y')
            ->assertDontSee('Importar CSV');
    }

    public function test_nominal_list_renders_and_displays_metrics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/vinculo-e-acompanhamento/relacao-nominal');

        $response->assertOk();
        $response->assertSee('Monitoramento de Vínculo e Acompanhamento - Relação Nominal');
        $response->assertSee('Sem extração real do PEC');
        $response->assertSee('Busca Avançada');
    }
}
