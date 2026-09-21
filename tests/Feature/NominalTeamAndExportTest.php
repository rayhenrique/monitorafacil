<?php

namespace Tests\Feature;

use App\Livewire\TerritorialBonding\NominalList;
use App\Models\CvatNominalCitizen;
use App\Models\CvatNominalMetric;
use App\Models\User;
use App\Services\CvatNominalDwService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NominalTeamAndExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create base metric
        CvatNominalMetric::create([
            'source' => CvatNominalDwService::SOURCE,
            'year' => 2026,
            'month' => 9,
            'reference_date' => '2026-09-30',
            'mici_total' => 2,
            'mici_updated' => 1,
            'mici_outdated' => 1,
            'mici_without_micdt_total' => 1,
            'mici_with_micdt_total' => 1,
            'mici_updated_micdt_outdated_or_none' => 1,
            'mici_updated_without_micdt' => 1,
            'mici_and_micdt_updated' => 0,
            'mici_and_micdt_outdated' => 1,
            'citizens_linked' => 1,
            'citizens_not_linked' => 1,
            'no_criteria_total' => 1,
            'no_criteria_accompanied' => 1,
            'no_criteria_not_accompanied' => 0,
            'elderly_or_child_total' => 1,
            'elderly_or_child_accompanied' => 0,
            'elderly_or_child_not_accompanied' => 1,
            'bpc_or_pbf_total' => 0,
            'bpc_or_pbf_accompanied' => 0,
            'bpc_or_pbf_not_accompanied' => 0,
            'elderly_child_and_benefit_total' => 0,
            'elderly_child_and_benefit_accompanied' => 0,
            'elderly_child_and_benefit_not_accompanied' => 0,
        ]);

        // Team 1: Alfa (1 citizen, mici_updated = 1)
        CvatNominalCitizen::create([
            'cidadao_pec_id' => 101,
            'source' => CvatNominalDwService::SOURCE,
            'name' => 'MARIA DA SILVA ALFA',
            'cns' => '700000000000001',
            'cpf' => '11111111111',
            'cnes' => '1234567',
            'facility_name' => 'UBS CENTRO',
            'ine' => '0000111111',
            'team_name' => 'ESF ALFA',
            'registration_eligible' => true,
            'mici_updated' => true,
            'mici_date' => '2026-08-15',
            'has_micdt' => true,
            'micdt_updated' => true,
            'micdt_date' => '2026-08-15',
            'is_linked' => true,
            'vulnerability_type' => 'sem_criterio',
            'social_benefit' => 'nenhum',
            'is_accompanied' => true,
            'year' => 2026,
            'month' => 9,
        ]);

        // Team 2: Beta (1 citizen, mici_updated = 0)
        CvatNominalCitizen::create([
            'cidadao_pec_id' => 202,
            'source' => CvatNominalDwService::SOURCE,
            'name' => 'JOAO DA SILVA BETA',
            'cns' => '700000000000002',
            'cpf' => '22222222222',
            'cnes' => '1234567',
            'facility_name' => 'UBS CENTRO',
            'ine' => '0000222222',
            'team_name' => 'ESF BETA',
            'registration_eligible' => true,
            'mici_updated' => false,
            'mici_date' => null,
            'has_micdt' => false,
            'micdt_updated' => false,
            'micdt_date' => null,
            'is_linked' => false,
            'vulnerability_type' => 'idoso',
            'social_benefit' => 'nenhum',
            'is_accompanied' => false,
            'year' => 2026,
            'month' => 9,
        ]);
    }

    public function test_nominal_list_updates_cards_dynamically_when_team_is_selected(): void
    {
        $user = User::factory()->create();

        // Default view: municipality consolidated
        $component = Livewire::actingAs($user)
            ->test(NominalList::class)
            ->assertSee('MARIA DA SILVA ALFA')
            ->assertSee('JOAO DA SILVA BETA');

        // Select Team Alfa: should show only Team Alfa and update metrics
        $component->set('selectedTeam', '0000111111')
            ->assertSee('MARIA DA SILVA ALFA')
            ->assertDontSee('JOAO DA SILVA BETA')
            ->assertSee('Equipe: ESF ALFA');

        // Select Team Beta: should show only Team Beta
        $component->set('selectedTeam', '0000222222')
            ->assertSee('JOAO DA SILVA BETA')
            ->assertDontSee('MARIA DA SILVA ALFA')
            ->assertSee('Equipe: ESF BETA');

        // Clear team filter
        $component->call('clearTeamFilter')
            ->assertSee('MARIA DA SILVA ALFA')
            ->assertSee('JOAO DA SILVA BETA');
    }

    public function test_nominal_list_export_csv_returns_streamed_file(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(NominalList::class);

        $component->call('exportCsv')
            ->assertFileDownloaded();
    }

    public function test_nominal_list_export_pdf_returns_valid_pdf_stream(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(NominalList::class);

        $component->call('exportPdf')
            ->assertFileDownloaded();
    }
}
