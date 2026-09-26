<?php

namespace Tests\Feature;

use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Livewire\OralHealth\NominalList as OralHealthNominalList;
use App\Livewire\OralHealth\Overview as OralHealthOverview;
use App\Livewire\TerritorialBonding\NominalList as TerritorialNominalList;
use App\Livewire\TerritorialBonding\TerritorialBondingOverview;
use App\Models\CvatNominalCitizen;
use App\Models\CvatNominalMetric;
use App\Models\User;
use App\Services\CnesXmlParserService;
use App\Services\CvatNominalDwService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperatorScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $cnesParser = \Mockery::mock(CnesXmlParserService::class);
        $cnesParser->shouldReceive('getEligibleC1Teams')->andReturn([
            ['ine' => '0000000001', 'name' => 'EQUIPE UNIDADE A', 'type' => '70', 'cnes' => '1111111'],
            ['ine' => '0000000002', 'name' => 'EQUIPE UNIDADE B', 'type' => '70', 'cnes' => '2222222'],
        ]);
        $this->app->instance(CnesXmlParserService::class, $cnesParser);

        $curYear = (int) date('Y');
        $curMonth = (int) date('n');

        CvatNominalMetric::query()->create([
            'source' => CvatNominalDwService::SOURCE,
            'year' => $curYear,
            'month' => $curMonth,
            'reference_date' => date('Y-m-d'),
            'mici_total' => 2,
            'mici_updated' => 2,
            'mici_and_micdt_updated' => 2,
            'citizens_linked' => 2,
            'benefit_data_available' => false,
        ]);

        CvatNominalCitizen::query()->create([
            'cidadao_pec_id' => 101,
            'name' => 'PACIENTE UNIDADE A',
            'cnes' => '1111111',
            'facility_name' => 'UBS UNIDADE A',
            'ine' => '0000000001',
            'team_name' => 'EQUIPE UNIDADE A',
            'source' => CvatNominalDwService::SOURCE,
            'registration_eligible' => true,
            'year' => $curYear,
            'month' => $curMonth,
            'quarter' => (int) ceil($curMonth / 4),
            'has_micdt' => true,
            'micdt_updated' => true,
            'is_accompanied' => true,
            'is_linked' => true,
            'vulnerability_type' => 'sem_criterio',
            'social_benefit' => 'nenhum',
            'evaluated_at' => now(),
        ]);

        CvatNominalCitizen::query()->create([
            'cidadao_pec_id' => 102,
            'name' => 'PACIENTE UNIDADE B',
            'cnes' => '2222222',
            'facility_name' => 'UBS UNIDADE B',
            'ine' => '0000000002',
            'team_name' => 'EQUIPE UNIDADE B',
            'source' => CvatNominalDwService::SOURCE,
            'registration_eligible' => true,
            'year' => $curYear,
            'month' => $curMonth,
            'quarter' => (int) ceil($curMonth / 4),
            'has_micdt' => true,
            'micdt_updated' => true,
            'is_accompanied' => true,
            'is_linked' => true,
            'vulnerability_type' => 'sem_criterio',
            'social_benefit' => 'nenhum',
            'evaluated_at' => now(),
        ]);
    }

    public function test_operator_in_territorial_bonding_nominal_list_is_locked_to_assigned_cnes(): void
    {
        $operator = User::factory()->operator('1111111', 'UBS UNIDADE A')->create();
        $this->actingAs($operator);

        Livewire::test(TerritorialNominalList::class)
            ->assertSet('filterCnes', '1111111')
            ->assertSee('PACIENTE UNIDADE A')
            ->assertDontSee('PACIENTE UNIDADE B')
            ->set('filterCnes', '')
            ->assertSet('filterCnes', '1111111')
            ->set('filterCnes', '2222222')
            ->assertSet('filterCnes', '1111111');
    }

    public function test_operator_in_territorial_bonding_overview_initializes_with_assigned_cnes(): void
    {
        $operator = User::factory()->operator('1111111', 'UBS UNIDADE A')->create();
        $this->actingAs($operator);

        Livewire::test(TerritorialBondingOverview::class)
            ->assertSet('filterCnes', '1111111');
    }

    public function test_operator_in_family_health_indicator_detail_is_locked_to_assigned_cnes(): void
    {
        $operator = User::factory()->operator('1111111', 'UBS UNIDADE A')->create();
        $this->actingAs($operator);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c1'])
            ->assertSet('selectedCnes', '1111111')
            ->set('selectedCnes', '2222222')
            ->assertSet('selectedCnes', '1111111')
            ->set('selectedCnes', '')
            ->assertSet('selectedCnes', '1111111');
    }

    public function test_operator_in_oral_health_nominal_list_is_locked_to_assigned_cnes(): void
    {
        $operator = User::factory()->operator('1111111', 'UBS UNIDADE A')->create();
        $this->actingAs($operator);

        Livewire::test(OralHealthNominalList::class)
            ->assertSet('filterCnes', '1111111')
            ->set('filterCnes', '2222222')
            ->assertSet('filterCnes', '1111111');
    }

    public function test_admin_has_unrestricted_filters(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(TerritorialNominalList::class)
            ->assertSet('filterCnes', '')
            ->set('filterCnes', '2222222')
            ->assertSet('filterCnes', '2222222');
    }
}
