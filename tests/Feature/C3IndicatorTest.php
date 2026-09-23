<?php

namespace Tests\Feature;

use App\Livewire\FamilyHealth\IndicatorDetail;
use App\Models\C3NominalPregnancy;
use App\Models\User;
use App\Services\C3ActiveSearchService;
use App\Services\FamilyHealthService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class C3IndicatorTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_c3_metadata_has_weight_two_and_eleven_good_practices(): void
    {
        $this->authenticateUser();

        $meta = FamilyHealthService::getIndicatorMeta('c3');
        $this->assertNotNull($meta);

        // Peso 2.0 oficial conforme NT 08/2026 e Portaria GM/MS nº 3.493/2024
        $this->assertSame(2.0, (float) $meta['weight']);

        // 11 Boas Práticas Oficiais (A a K) somando 100 pontos
        $this->assertCount(11, $meta['good_practices']);
        $totalPoints = 0;
        foreach ($meta['good_practices'] as $practice) {
            $totalPoints += $practice['points'];
        }
        $this->assertSame(100, $totalPoints);

        // Ordem oficial da régua no metadata
        $keys = array_keys($meta['parameters']);
        $this->assertSame(['optimal', 'good', 'sufficient', 'regular'], $keys);

        // Labels respectivos: Ótimo, Bom, Suficiente e Regular
        $this->assertStringContainsString('Regular', $meta['parameters']['regular']['label']);
        $this->assertStringContainsString('Suficiente', $meta['parameters']['sufficient']['label']);
        $this->assertStringContainsString('Bom', $meta['parameters']['good']['label']);
        $this->assertStringContainsString('Ótimo', $meta['parameters']['optimal']['label']);
    }

    public function test_c3_indicator_detail_subtabs_navigation(): void
    {
        $this->authenticateUser();

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c3', 'year' => 2026, 'quarter' => 3])
            ->assertSet('c3SubTab', 'monthly_summary')
            ->assertSee('Resumo Mensal das Equipes')
            ->assertSee('Lista Nominal e Coorte de Gestantes e Puérperas')
            ->call('setC3SubTab', 'nominal')
            ->assertSet('c3SubTab', 'nominal')
            ->call('setC3SubTab', 'monthly_summary')
            ->assertSet('c3SubTab', 'monthly_summary');
    }

    public function test_c3_active_search_renders_real_cohort_and_eleven_practices(): void
    {
        $this->authenticateUser();

        $today = Carbon::today();
        $dum = $today->copy()->subWeeks(19)->subDays(5); // 19s,5d
        $dpp = $dum->copy()->addDays(280);
        $puerperiumEnd = $dpp->copy()->addDays(42);

        C3NominalPregnancy::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'cidadao_pec_id' => 1001,
            'cns' => '706807717016520',
            'cpf' => '059.123.456-78',
            'name' => 'ADENILZA MARIA DA SILVA',
            'birth_date' => '1994-05-10',
            'age_years' => 32,
            'race_color' => 'Parda',
            'cnes' => '7705298',
            'facility_name' => 'USF CENTRO DE SAUDE',
            'district' => 'Distrito 1',
            'ine' => '0001573330',
            'team_name' => 'eSF 01 Centro',
            'professional_cns' => '706807717016520',
            'professional_name' => 'DRA ANA CARLA',
            'microarea' => '01',
            'dum' => $dum->format('Y-m-d'),
            'dpp' => $dpp->format('Y-m-d'),
            'puerperium_end_date' => $puerperiumEnd->format('Y-m-d'),
            'gestational_age_weeks' => 19,
            'current_status' => 'gestante',
            'month_ref' => $puerperiumEnd->format('m/Y'),
            'mici_updated' => true,
            'micdt_updated' => true,
            'is_accompanied' => true,
            'practice_a' => 1,
            'practice_b' => 6,
            'practice_c' => 6,
            'practice_d' => 6,
            'practice_e' => 2,
            'practice_f' => 1,
            'practice_g' => 1,
            'practice_h' => 0,
            'practice_i' => 0,
            'practice_j' => 0,
            'practice_k' => 1,
            'practice_a_met' => true,
            'practice_b_met' => true,
            'practice_c_met' => true,
            'practice_d_met' => true,
            'practice_e_met' => true,
            'practice_f_met' => true,
            'practice_g_met' => true,
            'practice_h_met' => false,
            'practice_i_met' => false,
            'practice_j_met' => false,
            'practice_k_met' => true,
            'score_percent' => 82.00,
            'calculation_version' => '1.0.0',
        ]);

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c3', 'year' => 2026, 'quarter' => 3])
            ->call('setC3SubTab', 'nominal');

        // Hero Card checks
        $component->assertSee('Denominador')
            ->assertSee('1ª Consulta pré-natal até 12 semanas (A)')
            ->assertSee('Consultas (B)')
            ->assertSee('Aferição de Pressão (C)')
            ->assertSee('Peso e Altura (D)')
            ->assertSee('Visitas Domiciliares (E)')
            ->assertSee('dTpa (F)')
            ->assertSee('Testes 1º trimestre (G)')
            ->assertSee('Testes 3º trimestre (H)')
            ->assertSee('Consulta puerpério (I)')
            ->assertSee('Visita Domiciliar puerpério (J)')
            ->assertSee('Avaliação Odontológica (K)');

        // Nominal Table checks matching reference images
        $component->assertSee('ADENILZA MARIA DA SILVA')
            ->assertSee('706807717016520')
            ->assertSee('7705298')
            ->assertSee('0001573330')
            ->assertSee('19s,5d')
            ->assertSee($dpp->format('d/m/Y'))
            ->assertSee('Parda')
            ->assertSee('Sim'); // MCI badge
    }

    public function test_c3_active_search_quick_and_advanced_filters(): void
    {
        $this->authenticateUser();

        C3NominalPregnancy::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'cidadao_pec_id' => 1001,
            'cns' => '701111111111111',
            'cpf' => '111.111.111-11',
            'name' => 'ADILA MARIA',
            'birth_date' => '2006-03-15',
            'age_years' => 20,
            'race_color' => 'Parda',
            'cnes' => '2722585',
            'facility_name' => 'USF SAO JOSE',
            'ine' => '0001711166',
            'team_name' => 'eSF 02',
            'current_status' => 'gestante',
            'mici_updated' => true,
            'practice_a_met' => true,
            'practice_b_met' => false,
            'calculation_version' => '1.0.0',
        ]);

        C3NominalPregnancy::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'cidadao_pec_id' => 1002,
            'cns' => '702222222222222',
            'cpf' => '222.222.222-22',
            'name' => 'BRUNA SANTOS',
            'birth_date' => '1998-07-20',
            'age_years' => 28,
            'race_color' => 'Branca',
            'cnes' => '2722577',
            'facility_name' => 'USF BELA VISTA',
            'ine' => '0001711158',
            'team_name' => 'eSF 03',
            'current_status' => 'gestante',
            'mici_updated' => false,
            'practice_a_met' => false,
            'practice_b_met' => true,
            'calculation_version' => '1.0.0',
        ]);

        $component = Livewire::test(IndicatorDetail::class, ['indicator' => 'c3', 'year' => 2026, 'quarter' => 3])
            ->call('setC3SubTab', 'nominal');

        // Quick filter by name
        $component->set('searchName', 'ADILA')
            ->assertSee('ADILA MARIA')
            ->assertDontSee('BRUNA SANTOS')
            ->set('searchName', '')
            ->assertSee('BRUNA SANTOS');

        // Advanced filter by Practice A (SIM)
        $component->set('showAdvancedModal', true)
            ->call('toggleBooleanFilter', 'advPracticeA', 'sim')
            ->call('applyAdvancedSearch')
            ->assertSee('ADILA MARIA')
            ->assertDontSee('BRUNA SANTOS');

        // Clear filters
        $component->call('clearAdvancedFilters')
            ->assertSee('ADILA MARIA')
            ->assertSee('BRUNA SANTOS');
    }

    public function test_c3_pregnancy_detail_modal_opens_and_closes(): void
    {
        $this->authenticateUser();

        $pregnancy = C3NominalPregnancy::query()->create([
            'year' => 2026,
            'quarter' => 3,
            'cidadao_pec_id' => 1005,
            'cns' => '705001848985458',
            'cpf' => '059.999.888-77',
            'name' => 'ADRIANA DOS SANTOS SILVA',
            'birth_date' => '1982-01-10',
            'age_years' => 44,
            'race_color' => 'Parda',
            'cnes' => '6010989',
            'facility_name' => 'UBS CENTRO',
            'ine' => '000171255',
            'team_name' => 'eSF Centro',
            'current_status' => 'gestante',
            'calculation_version' => '1.0.0',
        ]);

        Livewire::test(IndicatorDetail::class, ['indicator' => 'c3', 'year' => 2026, 'quarter' => 3])
            ->call('setC3SubTab', 'nominal')
            ->call('openPregnancyDetail', $pregnancy->id)
            ->assertSet('showPregnancyDetailModal', true)
            ->assertSee('ADRIANA DOS SANTOS SILVA')
            ->assertSee('Critério Metodológico')
            ->assertSee('1ª Consulta pré-natal até 12 semanas (A)')
            ->assertSee('Avaliação Odontológica (K)')
            ->call('closePregnancyDetail')
            ->assertSet('showPregnancyDetailModal', false);
    }

    public function test_c3_zero_mock_data_when_no_records_exist(): void
    {
        $this->authenticateUser();

        // Ano e quadrimestre sem nenhum registro na base
        Livewire::test(IndicatorDetail::class, ['indicator' => 'c3', 'year' => 2029, 'quarter' => 3])
            ->call('setC3SubTab', 'nominal')
            ->assertSee('Nenhuma gestante ou puérpera encontrada para os filtros aplicados.')
            ->assertDontSee('ADENILZA MARIA DA SILVA');
    }
}
