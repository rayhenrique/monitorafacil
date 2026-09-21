<?php

namespace Tests\Feature;

use App\Jobs\SyncCvatNominalJob;
use App\Livewire\Settings\DataProcessing;
use App\Livewire\TerritorialBonding\NominalList;
use App\Models\CvatNominalCitizen;
use App\Models\CvatNominalMetric;
use App\Models\User;
use App\Services\CvatNominalDwService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class CvatNominalListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/vinculo-e-acompanhamento/relacao-nominal')->assertRedirect('/login');
    }

    public function test_page_never_seeds_or_displays_legacy_demo_records(): void
    {
        CvatNominalCitizen::query()->create([
            'cidadao_pec_id' => 1,
            'name' => 'PESSOA DEMONSTRATIVA',
            'year' => 2026,
            'month' => 12,
        ]);
        CvatNominalMetric::query()->create([
            'year' => 2026,
            'month' => 12,
            'mici_total' => 36951,
        ]);

        $response = $this->actingAs(User::factory()->create())->get('/vinculo-e-acompanhamento/relacao-nominal');

        $response->assertOk()->assertSee('Sem extração real do PEC')->assertDontSee('PESSOA DEMONSTRATIVA')->assertDontSee('36.951');
        $this->assertSame(1, CvatNominalCitizen::query()->count());
        $this->assertNull(app(CvatNominalDwService::class)->getMetrics());
    }

    public function test_real_extraction_displays_counts_without_invented_score(): void
    {
        CvatNominalMetric::query()->create([
            'source' => CvatNominalDwService::SOURCE,
            'year' => 2026,
            'month' => 9,
            'reference_date' => '2026-09-21',
            'mici_total' => 1,
            'mici_updated' => 1,
            'mici_and_micdt_updated' => 1,
            'citizens_linked' => 1,
            'benefit_data_available' => false,
            'pbf_import_id' => 2,
            'pbf_vigencia' => '202602',
            'pbf_confirmed_total' => 1,
        ]);
        $citizen = CvatNominalCitizen::query()->create([
            'source' => CvatNominalDwService::SOURCE,
            'cidadao_pec_id' => 2,
            'name' => 'PESSOA REAL TESTE',
            'year' => 2026,
            'month' => 9,
            'registration_eligible' => true,
            'mici_updated' => true,
            'has_micdt' => true,
            'micdt_updated' => true,
            'is_linked' => true,
            'is_accompanied' => true,
            'social_benefit' => 'pbf',
        ]);
        CvatNominalCitizen::query()->create([
            'source' => CvatNominalDwService::SOURCE,
            'cidadao_pec_id' => 3,
            'name' => 'PESSOA EXCLUÍDA TESTE',
            'year' => 2026,
            'month' => 9,
            'registration_eligible' => false,
        ]);

        $user = User::factory()->create();
        $this->actingAs($user)->get('/vinculo-e-acompanhamento/relacao-nominal')
            ->assertOk()
            ->assertSee('PESSOA REAL TESTE')
            ->assertDontSee('PESSOA EXCLUÍDA TESTE')
            ->assertSee('PBF identificado no PEC')
            ->assertSee('202602')
            ->assertSee('índice Y')
            ->assertDontSee('Aferição Oficial');

        Livewire::actingAs($user)->test(NominalList::class)
            ->set('filterName', 'NOME INEXISTENTE')
            ->assertSee('Nenhum cidadão encontrado')
            ->set('filterName', '')
            ->call('openDetails', $citizen->id)
            ->assertSet('detailsModalOpen', true)
            ->call('closeDetails')
            ->assertSet('detailsModalOpen', false);
    }

    public function test_past_month_cannot_be_reconstructed_from_current_pec_view(): void
    {
        $result = app(CvatNominalDwService::class)->syncFromPec(null, 2025, 12);
        $this->assertFalse($result['success']);
        $this->assertNull($result['metrics']);
        $this->assertSame(0, CvatNominalCitizen::query()->count());
    }

    public function test_settings_action_schedules_cli_worker_instead_of_querying_pec_in_web_request(): void
    {
        config()->set('queue.default', 'database');
        Queue::fake();
        Livewire::actingAs(User::factory()->create())
            ->test(DataProcessing::class)
            ->call('processCvat')
            ->assertSet('processStatus', 'success')
            ->assertSee('Extração CVAT agendada');

        Queue::assertPushed(SyncCvatNominalJob::class, 1);
    }

    public function test_cvat_action_rejects_synchronous_queue(): void
    {
        config()->set('queue.default', 'sync');
        Queue::fake();

        Livewire::actingAs(User::factory()->create())
            ->test(DataProcessing::class)
            ->call('processCvat')
            ->assertSet('processStatus', 'error')
            ->assertSee('fila precisa ser assíncrona');

        Queue::assertNothingPushed();
    }

    public function test_generic_process_now_routes_cvat_to_the_queue(): void
    {
        config()->set('queue.default', 'database');
        Queue::fake();

        Livewire::actingAs(User::factory()->create())
            ->test(DataProcessing::class)
            ->call('processNow', 'cvat')
            ->assertSet('processStatus', 'success');

        Queue::assertPushed(SyncCvatNominalJob::class, 1);
    }

    public function test_queue_retry_interval_exceeds_cvat_job_timeout(): void
    {
        $job = new SyncCvatNominalJob(2026, 9);

        $this->assertGreaterThan($job->timeout, config('queue.connections.database.retry_after'));
    }
}
