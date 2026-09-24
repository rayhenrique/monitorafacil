<?php

namespace Tests\Feature;

use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Models\C5NominalHypertensive;
use App\Models\User;
use App\Services\C5ActiveSearchService;
use App\Services\FamilyHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class C5IndicatorTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_c5_metadata_has_weight_one_and_four_good_practices(): void
    {
        $this->authenticateUser();

        $meta = FamilyHealthService::getIndicatorMeta('c5');
        $this->assertNotNull($meta);

        // Componente III: Peso 1.0 oficial conforme Nota Metodológica C5
        $this->assertSame(1.0, (float) $meta['weight']);

        // 4 Boas Práticas Oficiais (A, B, C, D) somando 100 pontos (25 pts cada)
        $this->assertCount(4, $meta['good_practices']);
        $totalPoints = 0;
        foreach ($meta['good_practices'] as $practice) {
            $totalPoints += $practice['points'];
        }
        $this->assertSame(100, $totalPoints);

        // Verificação individual dos pesos de cada prática
        $practiceMap = collect($meta['good_practices'])->keyBy('code');
        $this->assertSame(25, $practiceMap['A']['points']); // Consulta médica/enfermagem no semestre
        $this->assertSame(25, $practiceMap['B']['points']); // Aferição de Pressão Arterial no semestre
        $this->assertSame(25, $practiceMap['C']['points']); // Antropometria (Peso e Altura) no ano
        $this->assertSame(25, $practiceMap['D']['points']); // 2 Visitas Domiciliares ACS no ano

        // Ordem oficial da régua no metadata
        $keys = array_keys($meta['parameters']);
        $this->assertSame(['optimal', 'good', 'sufficient', 'regular'], $keys);

        // Labels respectivos: Ótimo, Bom, Suficiente e Regular
        $this->assertStringContainsString('Regular', $meta['parameters']['regular']['label']);
        $this->assertStringContainsString('Suficiente', $meta['parameters']['sufficient']['label']);
        $this->assertStringContainsString('Bom', $meta['parameters']['good']['label']);
        $this->assertStringContainsString('Ótimo', $meta['parameters']['optimal']['label']);
    }

    public function test_c5_indicator_detail_subtabs_navigation(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c5', 'year' => 2026, 'quarter' => 3])
            ->assertSet('c5SubTab', 'monthly_summary')
            ->assertSee('Resumo Mensal das Equipes')
            ->assertSee('Lista Nominal e Coorte de Hipertensos')
            ->call('setC5SubTab', 'nominal')
            ->assertSet('c5SubTab', 'nominal')
            ->call('setC5SubTab', 'monthly_summary')
            ->assertSet('c5SubTab', 'monthly_summary');
    }

    public function test_c5_active_search_renders_real_cohort_and_four_practices(): void
    {
        $this->authenticateUser();

        C5NominalHypertensive::create([
            'year' => 2026,
            'quarter' => 3,
            'calculation_version' => 'v1.0.0',
            'cidadao_pec_id' => 99001,
            'cns' => '700000000000001',
            'cpf' => '12345678901',
            'name' => 'JOAO TESTE HIPERTENSO',
            'birth_date' => '1970-05-15',
            'age_years' => 56,
            'phone' => '84999990001',
            'race_color' => 'PARDA',
            'cnes' => '2722623',
            'facility_name' => 'USF CENTRO',
            'district' => 'CENTRO',
            'ine' => '0000171284',
            'team_name' => 'EQUIPE 01',
            'microarea' => '01',
            'condition_status' => 'Ativo',
            'ciap_codes' => 'K86',
            'cid_codes' => 'I10',
            'first_diagnosis_date' => '2024-01-10',
            'last_diagnosis_date' => '2026-02-15',
            'practice_a' => 1,
            'practice_a_met' => true,
            'last_consultation_date' => '2026-08-10',
            'practice_b' => 1,
            'practice_b_met' => true,
            'last_pa_date' => '2026-08-10',
            'last_pa_value' => '120/80',
            'practice_c' => 1,
            'practice_c_met' => true,
            'last_anthropometry_date' => '2026-05-20',
            'last_weight' => 78.5,
            'last_height' => 172.0,
            'practice_d' => 2,
            'practice_d_met' => true,
            'last_visit_date' => '2026-07-15',
            'score_percent' => 100.0,
        ]);

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c5', 'year' => 2026, 'quarter' => 3])
            ->call('setC5SubTab', 'nominal');

        $component->assertSee('JOAO TESTE HIPERTENSO')
            ->assertSee('100,0%')
            ->assertSee('USF CENTRO');
    }

    public function test_c5_hypertensive_detail_modal_opens_and_closes(): void
    {
        $this->authenticateUser();

        $hypertensive = C5NominalHypertensive::create([
            'year' => 2026,
            'quarter' => 3,
            'calculation_version' => 'v1.0.0',
            'cidadao_pec_id' => 99002,
            'cns' => '700000000000002',
            'cpf' => '98765432100',
            'name' => 'MARIA MODAL TESTE',
            'birth_date' => '1965-08-20',
            'age_years' => 61,
            'phone' => '84999990002',
            'race_color' => 'BRANCA',
            'cnes' => '2722623',
            'facility_name' => 'USF CENTRO',
            'district' => 'CENTRO',
            'ine' => '0000171284',
            'team_name' => 'EQUIPE 01',
            'microarea' => '02',
            'condition_status' => 'Ativo',
            'ciap_codes' => 'K86',
            'cid_codes' => 'I10',
            'first_diagnosis_date' => '2023-04-12',
            'last_diagnosis_date' => '2026-03-01',
            'practice_a' => 1,
            'practice_a_met' => true,
            'last_consultation_date' => '2026-07-20',
            'practice_b' => 1,
            'practice_b_met' => true,
            'last_pa_date' => '2026-07-20',
            'last_pa_value' => '130/85',
            'practice_c' => 1,
            'practice_c_met' => true,
            'last_anthropometry_date' => '2026-06-11',
            'last_weight' => 65.0,
            'last_height' => 160.0,
            'practice_d' => 0,
            'practice_d_met' => false,
            'last_visit_date' => null,
            'score_percent' => 75.0,
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c5', 'year' => 2026, 'quarter' => 3])
            ->call('setC5SubTab', 'nominal')
            ->assertSet('showHypertensiveModal', false)
            ->call('openHypertensiveDetail', $hypertensive->id)
            ->assertSet('showHypertensiveModal', true)
            ->assertSee('MARIA MODAL TESTE')
            ->assertSee('Pessoa com Hipertensão')
            ->assertSee('Auditoria das 4 Boas Práticas Oficiais')
            ->call('closeHypertensiveDetail')
            ->assertSet('showHypertensiveModal', false)
            ->assertSet('selectedHypertensive', null);
    }

    public function test_c5_zero_mock_data_when_no_records_exist(): void
    {
        $this->authenticateUser();

        // Nenhuma linha inserida no banco
        $this->assertFalse(C5ActiveSearchService::isRealDataAvailable(2025, 1));
        $this->assertSame(0, C5ActiveSearchService::getRealHypertensivesCount(2025, 1));

        $c5Service = app(C5ActiveSearchService::class);
        $cohort = $c5Service->getBaseCohort(2025, 1);
        $this->assertCount(0, $cohort);
    }
}
