<?php

namespace Tests\Feature;

use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Models\C4CohortSnapshot;
use App\Models\C4NominalDiabetic;
use App\Models\User;
use App\Services\C4ActiveSearchService;
use App\Services\FamilyHealthService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class C4IndicatorTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_c4_metadata_has_weight_one_and_six_good_practices(): void
    {
        $this->authenticateUser();

        $meta = FamilyHealthService::getIndicatorMeta('c4');
        $this->assertNotNull($meta);

        // Componente III: Peso 1.0 oficial conforme Nota Metodológica C4 e NT 08/2026
        $this->assertSame(1.0, (float) $meta['weight']);

        // 6 Boas Práticas Oficiais (A a F - Quadro 01) somando 100 pontos
        $this->assertCount(6, $meta['good_practices']);
        $totalPoints = 0;
        foreach ($meta['good_practices'] as $practice) {
            $totalPoints += $practice['points'];
        }
        $this->assertSame(100, $totalPoints);

        // Verificação individual dos pesos de cada prática
        $practiceMap = collect($meta['good_practices'])->keyBy('code');
        $this->assertSame(20, $practiceMap['A']['points']); // Consulta médica/enfermagem no semestre
        $this->assertSame(15, $practiceMap['B']['points']); // Aferição de Pressão Arterial no semestre
        $this->assertSame(15, $practiceMap['C']['points']); // Peso e Altura no ano
        $this->assertSame(20, $practiceMap['D']['points']); // 2 Visitas Domiciliares ACS no ano
        $this->assertSame(15, $practiceMap['E']['points']); // Hemoglobina Glicada no ano
        $this->assertSame(15, $practiceMap['F']['points']); // Avaliação dos Pés no ano

        // Ordem oficial da régua no metadata
        $keys = array_keys($meta['parameters']);
        $this->assertSame(['optimal', 'good', 'sufficient', 'regular'], $keys);

        // Labels respectivos: Ótimo, Bom, Suficiente e Regular
        $this->assertStringContainsString('Regular', $meta['parameters']['regular']['label']);
        $this->assertStringContainsString('Suficiente', $meta['parameters']['sufficient']['label']);
        $this->assertStringContainsString('Bom', $meta['parameters']['good']['label']);
        $this->assertStringContainsString('Ótimo', $meta['parameters']['optimal']['label']);
    }

    public function test_c4_indicator_detail_subtabs_navigation(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c4', 'year' => 2026, 'quarter' => 3])
            ->assertSet('c4SubTab', 'monthly_summary')
            ->assertSee('Resumo Mensal das Equipes')
            ->assertSee('Lista Nominal e Coorte de Diabéticos')
            ->call('setC4SubTab', 'nominal')
            ->assertSet('c4SubTab', 'nominal')
            ->call('setC4SubTab', 'monthly_summary')
            ->assertSet('c4SubTab', 'monthly_summary');
    }

    public function test_c4_active_search_renders_real_cohort_and_six_practices(): void
    {
        $this->authenticateUser();

        C4NominalDiabetic::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'cidadao_pec_id' => 2001,
            'cns' => '700100200300400',
            'cpf' => '059.888.777-66',
            'name' => 'SEBASTIAO FERREIRA LIMA',
            'birth_date' => '1965-08-15',
            'age_years' => 61,
            'race_color' => 'Parda',
            'cnes' => '7705298',
            'facility_name' => 'USF CENTRO DE SAUDE',
            'district' => 'Distrito Central',
            'ine' => '0001573330',
            'team_name' => 'eSF 01 Centro',
            'microarea' => '02',
            'condition_status' => 'ativo',
            'ciap_codes' => 'T90',
            'cid_codes' => 'E11.9',
            'first_diagnosis_date' => '2018-03-10',
            'last_diagnosis_date' => '2026-06-15',
            'month_ref' => '09/2026',
            'mici_updated' => true,
            'is_accompanied' => true,
            'practice_a' => 1,
            'practice_a_met' => true,
            'last_consultation_date' => '2026-07-12',
            'practice_b' => 1,
            'practice_b_met' => true,
            'last_pa_date' => '2026-07-12',
            'last_pa_value' => '130/80',
            'practice_c' => 1,
            'practice_c_met' => true,
            'last_anthropometry_date' => '2026-05-20',
            'last_weight' => 78.5,
            'last_height' => 172.0,
            'practice_d' => 2,
            'practice_d_met' => true,
            'last_visit_date' => '2026-08-05',
            'practice_e' => 1,
            'practice_e_met' => true,
            'last_hba1c_date' => '2026-04-10',
            'last_hba1c_type' => '02.02.01.050-3',
            'practice_f' => 1,
            'practice_f_met' => true,
            'last_foot_exam_date' => '2026-06-22',
            'score_percent' => 100.0,
            'calculation_version' => '1.0.0',
        ]);

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c4', 'year' => 2026, 'quarter' => 3])
            ->call('setC4SubTab', 'nominal');

        // Banner superior checks
        $component->assertSee('Denominador')
            ->assertSee('Consulta Méd/Enf Semestre (A)')
            ->assertSee('Aferição de Pressão (B)')
            ->assertSee('Peso e Altura no Ano (C)')
            ->assertSee('2 Visitas ACS no Ano (D)')
            ->assertSee('Hemoglobina Glicada (E)')
            ->assertSee('Avaliação dos Pés (F)');

        // Nominal Table checks
        $component->assertSee('SEBASTIAO FERREIRA LIMA')
            ->assertSee('700100200300400')
            ->assertSee('059.888.777-66')
            ->assertSee('7705298')
            ->assertSee('0001573330')
            ->assertSee('Parda')
            ->assertSee('100,0%')
            ->assertSee('Sim'); // MCI badge
    }

    public function test_c4_active_search_quick_and_advanced_filters(): void
    {
        $this->authenticateUser();

        C4NominalDiabetic::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'cidadao_pec_id' => 2001,
            'cns' => '701111111111111',
            'cpf' => '111.111.111-11',
            'name' => 'ANTONIO ALVES',
            'birth_date' => '1958-04-12',
            'age_years' => 68,
            'race_color' => 'Parda',
            'cnes' => '2722585',
            'facility_name' => 'USF SAO JOSE',
            'ine' => '0001711166',
            'team_name' => 'eSF 02',
            'microarea' => '01',
            'mici_updated' => true,
            'practice_a_met' => true,
            'practice_b_met' => false,
            'score_percent' => 50.0,
            'calculation_version' => '1.0.0',
        ]);

        C4NominalDiabetic::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'cidadao_pec_id' => 2002,
            'cns' => '702222222222222',
            'cpf' => '222.222.222-22',
            'name' => 'BENEDITA SOUZA',
            'birth_date' => '1952-11-20',
            'age_years' => 73,
            'race_color' => 'Branca',
            'cnes' => '2722577',
            'facility_name' => 'USF BELA VISTA',
            'ine' => '0001711158',
            'team_name' => 'eSF 03',
            'microarea' => '02',
            'mici_updated' => false,
            'practice_a_met' => false,
            'practice_b_met' => true,
            'score_percent' => 30.0,
            'calculation_version' => '1.0.0',
        ]);

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c4', 'year' => 2026, 'quarter' => 3])
            ->call('setC4SubTab', 'nominal');

        // Quick filter by name
        $component->set('searchName', 'ANTONIO')
            ->assertSee('ANTONIO ALVES')
            ->assertDontSee('BENEDITA SOUZA')
            ->set('searchName', '')
            ->assertSee('BENEDITA SOUZA');

        // Advanced filter by Practice A (SIM)
        $component->set('showAdvancedModal', true)
            ->call('toggleBooleanFilter', 'advPracticeA', 'sim')
            ->call('applyAdvancedSearch')
            ->assertSee('ANTONIO ALVES')
            ->assertDontSee('BENEDITA SOUZA');

        // Advanced filter by Practice B (SIM) via applyAdvancedFilters
        $component->call('clearAdvancedFilters')
            ->set('showAdvancedModal', true)
            ->call('toggleBooleanFilter', 'advPracticeB', 'sim')
            ->call('applyAdvancedFilters')
            ->assertSee('BENEDITA SOUZA')
            ->assertDontSee('ANTONIO ALVES');

        // Clear filters
        $component->call('clearAdvancedFilters')
            ->assertSee('ANTONIO ALVES')
            ->assertSee('BENEDITA SOUZA');
    }

    public function test_c4_diabetic_detail_modal_opens_and_closes(): void
    {
        $this->authenticateUser();

        $diabetic = C4NominalDiabetic::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'cidadao_pec_id' => 2005,
            'cns' => '705001848985458',
            'cpf' => '059.999.888-77',
            'name' => 'CARLOS EDUARDO PEREIRA',
            'birth_date' => '1960-03-25',
            'age_years' => 66,
            'race_color' => 'Parda',
            'cnes' => '6010989',
            'facility_name' => 'UBS CENTRO',
            'ine' => '000171255',
            'team_name' => 'eSF Centro',
            'microarea' => '03',
            'ciap_codes' => 'T90',
            'cid_codes' => 'E11',
            'first_diagnosis_date' => '2015-05-10',
            'last_diagnosis_date' => '2026-01-20',
            'practice_a_met' => true,
            'last_consultation_date' => '2026-06-10',
            'practice_b_met' => true,
            'last_pa_date' => '2026-06-10',
            'last_pa_value' => '125/80',
            'practice_c_met' => true,
            'last_anthropometry_date' => '2026-04-18',
            'last_weight' => 74.0,
            'last_height' => 168.0,
            'practice_d' => 2,
            'practice_d_met' => true,
            'last_visit_date' => '2026-05-30',
            'practice_e_met' => true,
            'last_hba1c_date' => '2026-02-14',
            'practice_f_met' => true,
            'last_foot_exam_date' => '2026-06-10',
            'score_percent' => 100.0,
            'calculation_version' => '1.0.0',
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c4', 'year' => 2026, 'quarter' => 3])
            ->call('setC4SubTab', 'nominal')
            ->call('openDiabeticDetail', $diabetic->id)
            ->assertSet('showDiabeticDetailModal', true)
            ->assertSee('CARLOS EDUARDO PEREIRA')
            ->assertSee('Critério Metodológico Oficial')
            ->assertSee('Consulta Médica / Enfermagem no Semestre (A)')
            ->assertSee('Aferição de Pressão Arterial no Semestre (B)')
            ->assertSee('Antropometria: Peso e Altura no Ano (C)')
            ->assertSee('Visitas Domiciliares de ACS no Ano (D)')
            ->assertSee('Exame de Hemoglobina Glicada no Ano (E)')
            ->assertSee('Avaliação Clínica dos Pés no Ano (F)')
            ->call('closeDiabeticDetail')
            ->assertSet('showDiabeticDetailModal', false);
    }

    public function test_c4_zero_mock_data_when_no_records_exist(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c4', 'year' => 2030, 'quarter' => 3])
            ->call('setC4SubTab', 'nominal')
            ->assertSee('Nenhuma pessoa com diabetes encontrada para os filtros aplicados.')
            ->assertDontSee('SEBASTIAO FERREIRA LIMA');
    }
}
