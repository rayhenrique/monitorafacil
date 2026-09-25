<?php

namespace Tests\Feature;

use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Models\C7NominalWoman;
use App\Models\User;
use App\Services\FamilyHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class C7IndicatorTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_c7_metadata_has_weight_two_and_four_good_practices(): void
    {
        $this->authenticateUser();

        $meta = FamilyHealthService::getIndicatorMeta('c7');
        $this->assertNotNull($meta);

        // Componente III: Peso 2.0 oficial conforme Nota Técnica nº 06/2025-CVAT
        $this->assertSame(2.0, (float) $meta['weight']);

        // 4 Boas Práticas Oficiais (A, B, C, D) somando 100 pontos
        $this->assertCount(4, $meta['good_practices']);
        $totalPoints = 0;
        foreach ($meta['good_practices'] as $practice) {
            $totalPoints += $practice['points'];
        }
        $this->assertSame(100, $totalPoints);

        // Verificação individual dos pesos de cada prática
        $practiceMap = collect($meta['good_practices'])->keyBy('code');
        $this->assertSame(20, $practiceMap['A']['points']); // Colo do Útero (25 a 64 anos)
        $this->assertSame(30, $practiceMap['B']['points']); // Vacina HPV (9 a 14 anos)
        $this->assertSame(30, $practiceMap['C']['points']); // Saúde Sexual e Reprodutiva (14 a 69 anos)
        $this->assertSame(20, $practiceMap['D']['points']); // Câncer de Mama (50 a 69 anos)

        // Ordem oficial da régua no metadata
        $keys = array_keys($meta['parameters']);
        $this->assertSame(['optimal', 'good', 'sufficient', 'regular'], $keys);

        // Labels respectivos: Ótimo, Bom, Suficiente e Regular
        $this->assertStringContainsString('Regular', $meta['parameters']['regular']['label']);
        $this->assertStringContainsString('Suficiente', $meta['parameters']['sufficient']['label']);
        $this->assertStringContainsString('Bom', $meta['parameters']['good']['label']);
        $this->assertStringContainsString('Ótimo', $meta['parameters']['optimal']['label']);
    }

    public function test_c7_indicator_detail_subtabs_navigation(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c7', 'year' => 2026, 'quarter' => 3])
            ->assertSet('c7SubTab', 'monthly_summary')
            ->assertSee('Resumo Mensal por Equipe')
            ->assertSee('Busca Ativa Nominal')
            ->call('switchC7SubTab', 'nominal')
            ->assertSet('c7SubTab', 'nominal')
            ->call('switchC7SubTab', 'monthly_summary')
            ->assertSet('c7SubTab', 'monthly_summary');
    }

    public function test_c7_active_search_renders_real_cohort_and_four_practices(): void
    {
        $this->authenticateUser();

        C7NominalWoman::create([
            'year' => 2026,
            'quarter' => 3,
            'calculation_version' => 'v1.0.0',
            'cidadao_pec_id' => 99201,
            'cns' => '700000000000030',
            'cpf' => '22233344455',
            'name' => 'MARIA TESTE PREVENCAO CANCER',
            'birth_date' => '1990-05-15',
            'age_years' => 36,
            'sex' => 'FEMININO',
            'gender_identity' => 'MULHER_CIS',
            'phone' => '82988880003',
            'race_color' => 'PARDA',
            'cnes' => '2722623',
            'facility_name' => 'USF CENTRO',
            'district' => 'CENTRO',
            'ine' => '0000171284',
            'team_name' => 'EQUIPE 01',
            'team_type' => '70',
            'microarea' => '03',
            'eligible_practice_a' => true,
            'practice_a_met' => true,
            'practice_a_count' => 1,
            'last_cervical_exam_date' => '2026-06-20',
            'last_cervical_exam_code' => '0201020033',
            'last_cervical_exam_desc' => 'EXAME CITOPATOLOGICO CERVICO-VAGINAL',
            'eligible_practice_b' => false,
            'practice_b_met' => false,
            'practice_b_count' => 0,
            'eligible_practice_c' => true,
            'practice_c_met' => true,
            'practice_c_count' => 1,
            'last_sexual_health_date' => '2026-07-10',
            'last_sexual_health_code' => 'W11',
            'last_sexual_health_detail' => 'Planejamento familiar / anticoncepção',
            'eligible_practice_d' => false,
            'practice_d_met' => false,
            'practice_d_count' => 0,
            'score_percent' => 50.0,
        ]);

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c7', 'year' => 2026, 'quarter' => 3])
            ->call('switchC7SubTab', 'nominal');

        $component->assertSee('MARIA TESTE PREVENCAO CANCER')
            ->assertSee('USF CENTRO')
            ->assertSee('Conforme');
    }

    public function test_c7_woman_detail_modal_opens_and_closes(): void
    {
        $this->authenticateUser();

        $woman = C7NominalWoman::create([
            'year' => 2026,
            'quarter' => 3,
            'calculation_version' => 'v1.0.0',
            'cidadao_pec_id' => 99202,
            'cns' => '700000000000040',
            'cpf' => '33344455566',
            'name' => 'ANA CLARA AUDITORIA CLINICA',
            'birth_date' => '1974-08-22',
            'age_years' => 52,
            'sex' => 'FEMININO',
            'gender_identity' => 'MULHER_CIS',
            'phone' => '82988880004',
            'race_color' => 'BRANCA',
            'cnes' => '2722623',
            'facility_name' => 'USF CENTRO',
            'district' => 'CENTRO',
            'ine' => '0000171284',
            'team_name' => 'EQUIPE 01',
            'team_type' => '70',
            'microarea' => '04',
            'eligible_practice_a' => true,
            'practice_a_met' => true,
            'practice_a_count' => 1,
            'last_cervical_exam_date' => '2026-05-18',
            'last_cervical_exam_code' => '0201020033',
            'last_cervical_exam_desc' => 'EXAME CITOPATOLOGICO CERVICO-VAGINAL',
            'eligible_practice_b' => false,
            'practice_b_met' => false,
            'practice_b_count' => 0,
            'eligible_practice_c' => true,
            'practice_c_met' => true,
            'practice_c_count' => 1,
            'last_sexual_health_date' => '2026-04-12',
            'last_sexual_health_code' => 'W12',
            'last_sexual_health_detail' => 'Planejamento familiar',
            'eligible_practice_d' => true,
            'practice_d_met' => true,
            'practice_d_count' => 1,
            'last_mammogram_date' => '2026-03-10',
            'last_mammogram_code' => '0204030188',
            'last_mammogram_desc' => 'MAMOGRAFIA BILATERAL PARA RASTREAMENTO',
            'score_percent' => 70.0,
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c7', 'year' => 2026, 'quarter' => 3])
            ->call('switchC7SubTab', 'nominal')
            ->call('openWomanDetail', $woman->id)
            ->assertSet('showWomanModal', true)
            ->assertSee('ANA CLARA AUDITORIA CLINICA')
            ->assertSee('MAMOGRAFIA BILATERAL PARA RASTREAMENTO')
            ->call('closeWomanDetail')
            ->assertSet('showWomanModal', false);
    }

    public function test_c7_advanced_search_modal_and_filtering_renders_without_error(): void
    {
        $this->authenticateUser();

        C7NominalWoman::create([
            'year' => 2026,
            'quarter' => 3,
            'calculation_version' => 'v1.0.0',
            'cidadao_pec_id' => 99203,
            'cns' => '700000000000050',
            'cpf' => '44455566677',
            'name' => 'BEATRIZ FILTRO AVANCADO',
            'birth_date' => '1995-02-10',
            'age_years' => 31,
            'sex' => 'FEMININO',
            'gender_identity' => 'MULHER_CIS',
            'phone' => '82988880005',
            'race_color' => 'PARDA',
            'cnes' => '2722623',
            'facility_name' => 'USF CENTRO',
            'district' => 'CENTRO',
            'ine' => '0000171284',
            'team_name' => 'EQUIPE 01',
            'team_type' => '70',
            'microarea' => '02',
            'eligible_practice_a' => true,
            'practice_a_met' => true,
            'practice_a_count' => 1,
            'score_percent' => 50.0,
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c7', 'year' => 2026, 'quarter' => 3])
            ->call('switchC7SubTab', 'nominal')
            ->call('openAdvancedSearch')
            ->assertSet('showAdvancedModal', true)
            ->assertSee('CENTRO')
            ->assertSee('USF CENTRO')
            ->set('advDistrict', 'CENTRO')
            ->set('advFacility', '2722623')
            ->set('advTeam', '0000171284')
            ->assertSee('BEATRIZ FILTRO AVANCADO')
            ->call('closeAdvancedSearch')
            ->assertSet('showAdvancedModal', false);
    }

    public function test_c7_zero_mock_data_when_no_records_exist(): void
    {
        $this->authenticateUser();

        // Sem registros cadastrados para a competência avaliada
        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c7', 'year' => 2026, 'quarter' => 3]);

        $component->assertStatus(200);
        $this->assertSame(0, C7NominalWoman::where('year', 2026)->where('quarter', 3)->count());
    }
}
