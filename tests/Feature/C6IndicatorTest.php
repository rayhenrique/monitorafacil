<?php

namespace Tests\Feature;

use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Models\C6NominalElderly;
use App\Models\User;
use App\Services\C6ActiveSearchService;
use App\Services\FamilyHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class C6IndicatorTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_c6_metadata_has_weight_one_and_four_good_practices(): void
    {
        $this->authenticateUser();

        $meta = FamilyHealthService::getIndicatorMeta('c6');
        $this->assertNotNull($meta);

        // Componente III: Peso 1.0 oficial conforme Nota Metodológica C6
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
        $this->assertSame(25, $practiceMap['A']['points']); // Consulta médica/enfermagem no ano
        $this->assertSame(25, $practiceMap['B']['points']); // Antropometria (Peso e Altura) no ano
        $this->assertSame(25, $practiceMap['C']['points']); // 2 Visitas Domiciliares ACS no ano
        $this->assertSame(25, $practiceMap['D']['points']); // Vacina Influenza no ano

        // Ordem oficial da régua no metadata
        $keys = array_keys($meta['parameters']);
        $this->assertSame(['optimal', 'good', 'sufficient', 'regular'], $keys);

        // Labels respectivos: Ótimo, Bom, Suficiente e Regular
        $this->assertStringContainsString('Regular', $meta['parameters']['regular']['label']);
        $this->assertStringContainsString('Suficiente', $meta['parameters']['sufficient']['label']);
        $this->assertStringContainsString('Bom', $meta['parameters']['good']['label']);
        $this->assertStringContainsString('Ótimo', $meta['parameters']['optimal']['label']);
    }

    public function test_c6_indicator_detail_subtabs_navigation(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c6', 'year' => 2026, 'quarter' => 3])
            ->assertSet('c6SubTab', 'monthly_summary')
            ->assertSee('Resumo Mensal das Equipes')
            ->assertSee('Lista Nominal e Coorte de Idosos')
            ->call('setC6SubTab', 'nominal')
            ->assertSet('c6SubTab', 'nominal')
            ->call('setC6SubTab', 'monthly_summary')
            ->assertSet('c6SubTab', 'monthly_summary');
    }

    public function test_c6_active_search_renders_real_cohort_and_four_practices(): void
    {
        $this->authenticateUser();

        C6NominalElderly::create([
            'year' => 2026,
            'quarter' => 3,
            'calculation_version' => 'v1.0.0',
            'cidadao_pec_id' => 99101,
            'cns' => '700000000000010',
            'cpf' => '11122233344',
            'name' => 'SEBASTIAO TESTE IDOSO',
            'birth_date' => '1954-03-20',
            'age_years' => 72,
            'phone' => '82988880001',
            'race_color' => 'PARDA',
            'cnes' => '2722623',
            'facility_name' => 'USF CENTRO',
            'district' => 'CENTRO',
            'ine' => '0000171284',
            'team_name' => 'EQUIPE 01',
            'team_type' => '70',
            'microarea' => '01',
            'practice_a' => 1,
            'practice_a_met' => true,
            'last_consultation_date' => '2026-08-10',
            'practice_b' => 1,
            'practice_b_met' => true,
            'last_anthropometry_date' => '2026-05-20',
            'last_weight' => 72.5,
            'last_height' => 165.0,
            'practice_c' => 2,
            'practice_c_met' => true,
            'last_visit_date' => '2026-07-15',
            'practice_d' => 1,
            'practice_d_met' => true,
            'last_vaccine_date' => '2026-04-18',
            'last_vaccine_name' => 'Vacina influenza trivalente',
            'score_percent' => 100.0,
        ]);

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c6', 'year' => 2026, 'quarter' => 3])
            ->call('setC6SubTab', 'nominal');

        $component->assertSee('SEBASTIAO TESTE IDOSO')
            ->assertSee('100,0%')
            ->assertSee('USF CENTRO');
    }

    public function test_c6_elderly_detail_modal_opens_and_closes(): void
    {
        $this->authenticateUser();

        $elderly = C6NominalElderly::create([
            'year' => 2026,
            'quarter' => 3,
            'calculation_version' => 'v1.0.0',
            'cidadao_pec_id' => 99102,
            'cns' => '700000000000020',
            'cpf' => '99988877766',
            'name' => 'DONA FRANCISCA IDOSA',
            'birth_date' => '1948-11-10',
            'age_years' => 77,
            'phone' => '82988880002',
            'race_color' => 'BRANCA',
            'cnes' => '2722623',
            'facility_name' => 'USF CENTRO',
            'district' => 'CENTRO',
            'ine' => '0000171284',
            'team_name' => 'EQUIPE 01',
            'team_type' => '70',
            'microarea' => '02',
            'practice_a' => 1,
            'practice_a_met' => true,
            'last_consultation_date' => '2026-07-20',
            'practice_b' => 1,
            'practice_b_met' => true,
            'last_anthropometry_date' => '2026-06-11',
            'last_weight' => 60.0,
            'last_height' => 155.0,
            'practice_c' => 0,
            'practice_c_met' => false,
            'last_visit_date' => null,
            'practice_d' => 1,
            'practice_d_met' => true,
            'last_vaccine_date' => '2026-05-02',
            'last_vaccine_name' => 'Vacina influenza trivalente',
            'score_percent' => 75.0,
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c6', 'year' => 2026, 'quarter' => 3])
            ->call('setC6SubTab', 'nominal')
            ->assertSet('showElderlyModal', false)
            ->call('openElderlyDetail', $elderly->id)
            ->assertSet('showElderlyModal', true)
            ->assertSee('DONA FRANCISCA IDOSA')
            ->assertSee('Pessoa Idosa (≥ 60 anos)')
            ->assertSee('Auditoria das 4 Boas Práticas Oficiais')
            ->call('closeElderlyDetail')
            ->assertSet('showElderlyModal', false)
            ->assertSet('selectedElderly', null);
    }

    public function test_c6_eap_type_76_exempts_practice_c(): void
    {
        $this->authenticateUser();

        // Equipe eAP tipo 76: C é dispensada, pontuação normalizada: (A + B + D) * 100 / 75
        // Se A=25, B=25, D=25 -> 75 * 100 / 75 = 100%
        $elderlyEap = C6NominalElderly::create([
            'year' => 2026,
            'quarter' => 3,
            'calculation_version' => 'v1.0.0',
            'cidadao_pec_id' => 99103,
            'cns' => '700000000000030',
            'cpf' => '55566677788',
            'name' => 'ANTONIO EAP IDOSO',
            'birth_date' => '1950-01-01',
            'age_years' => 76,
            'cnes' => '2722623',
            'facility_name' => 'USF CENTRO',
            'ine' => '0000171299',
            'team_name' => 'EAP CENTRO',
            'team_type' => '76', // eAP
            'practice_a' => 1,
            'practice_a_met' => true,
            'practice_b' => 1,
            'practice_b_met' => true,
            'practice_c' => 0,
            'practice_c_met' => false,
            'practice_d' => 1,
            'practice_d_met' => true,
            'score_percent' => 100.0, // Normalizado sem C
        ]);

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c6', 'year' => 2026, 'quarter' => 3])
            ->call('setC6SubTab', 'nominal');

        $component->assertSee('ANTONIO EAP IDOSO')
            ->assertSee('Isento (eAP)');
    }

    public function test_c6_zero_mock_data_when_no_records_exist(): void
    {
        $this->authenticateUser();

        // Nenhuma linha inserida no banco
        $this->assertFalse(C6ActiveSearchService::isRealDataAvailable(2025, 1));
        $this->assertSame(0, C6ActiveSearchService::getRealElderlyCount(2025, 1));

        $c6Service = app(C6ActiveSearchService::class);
        $cohort = $c6Service->getBaseCohort(2025, 1);
        $this->assertCount(0, $cohort);
    }
}
