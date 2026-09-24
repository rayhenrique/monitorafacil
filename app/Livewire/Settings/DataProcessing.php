<?php

namespace App\Livewire\Settings;

use App\Enums\TeamType;
use App\Jobs\SyncCvatNominalJob;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\SyncLog;
use App\Services\EsusDataProcessingService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app')]
class DataProcessing extends Component
{
    public bool $isProcessing = false;

    public ?string $processMessage = null;

    public ?string $processStatus = null; // 'success' | 'error'

    public int $progressPercent = 0;

    public string $currentStep = '';

    /** @var array<string, array{name: string, description: string, status: string, rows: int, message: string}> */
    public array $tablesReport = [];

    public string $selectedScope = 'all'; // 'c1', 'c2', 'c3', 'c4', 'c5', 'cvat' ou 'all' (Geral Completo)

    public ?float $executionTimeMs = null;

    public function setScope(string $scope): void
    {
        $this->selectedScope = in_array($scope, ['c1', 'c2', 'c3', 'c4', 'c5', 'cvat', 'all'], true) ? $scope : 'all';
    }

    public function processC1(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'c1';
        $this->executeProcessing($service, 'c1');
    }

    public function processC2(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'c2';
        $this->executeProcessing($service, 'c2');
    }

    public function processC3(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'c3';
        $this->executeProcessing($service, 'c3');
    }

    public function processC4(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'c4';
        $this->executeProcessing($service, 'c4');
    }

    public function processC5(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'c5';
        $this->executeProcessing($service, 'c5');
    }

    public function processCvat(): void
    {
        $this->selectedScope = 'cvat';
        $this->tablesReport = [];
        $this->progressPercent = 0;
        if (config('queue.default') === 'sync') {
            $this->processStatus = 'error';
            $this->processMessage = 'A fila precisa ser assíncrona para extrair o CVAT fora da requisição web. Configure QUEUE_CONNECTION=database e mantenha o worker ativo.';

            return;
        }

        try {
            SyncCvatNominalJob::dispatch((int) now()->year, (int) now()->month);
            $this->processStatus = 'success';
            $this->processMessage = 'Extração CVAT agendada. Os resultados aparecerão na Relação Nominal após o processamento pelo worker da fila.';
        } catch (Throwable $e) {
            report($e);
            $this->processStatus = 'error';
            $this->processMessage = 'Não foi possível agendar a extração CVAT. Verifique a fila de processamento.';
        }
    }

    public function processAll(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'all';
        $this->executeProcessing($service, 'all');

        if (config('queue.default') !== 'sync') {
            try {
                SyncCvatNominalJob::dispatch((int) now()->year, (int) now()->month);
                $this->processMessage = ($this->processMessage ? $this->processMessage."\n\n" : '')
                    .'Extração nominal do CVAT também foi agendada na fila de processamento.';
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    public function processNow(EsusDataProcessingService $service, ?string $scope = null): void
    {
        $targetScope = $scope ?: $this->selectedScope;
        if ($targetScope === 'cvat') {
            $this->processCvat();

            return;
        }

        $this->executeProcessing($service, $targetScope);
    }

    private function executeProcessing(EsusDataProcessingService $service, string $scope): void
    {
        $this->isProcessing = true;
        $this->processMessage = null;
        $this->processStatus = null;
        $scopeDesc = match ($scope) {
            'c1' => 'Indicador C1 (Mais Acesso)',
            'c2' => 'Indicador C2 (Desenvolvimento Infantil)',
            'c3' => 'Indicador C3 (Gestação e Puerpério)',
            default => 'Geral Completo',
        };
        $this->updateProgress(10, "Iniciando verificação do banco de dados e-SUS PEC [{$scopeDesc}]...");

        try {
            $result = $service->process(function (int $percent, string $step, array $tables): void {
                $this->tablesReport = $tables;
                $this->updateProgress($percent, $step);
            }, (int) now()->year, min(3, (int) ceil(now()->month / 4)), $scope);

            $this->processStatus = $result['success'] ? 'success' : 'error';
            $this->processMessage = $result['message'];
            $this->tablesReport = $result['tables'];
            $this->executionTimeMs = $result['execution_time_ms'];
            $this->updateProgress(
                100,
                $result['success'] ? 'Processamento concluído com sucesso!' : 'Processamento concluído com pendências.',
            );
        } catch (Throwable $e) {
            $this->processStatus = 'error';
            $this->processMessage = 'Exceção ao executar o processamento: '.$e->getMessage();
            $this->updateProgress(100, 'Falha durante o processamento.');
        } finally {
            $this->isProcessing = false;
        }
    }

    private function updateProgress(int $percent, string $step): void
    {
        $this->progressPercent = max(0, min(100, $percent));
        $this->currentStep = $step;
    }

    public function render(): View
    {
        $lastLog = SyncLog::query()->orderByDesc('started_at')->first();

        $latestRegistration = ConsolidationRegistration::query()
            ->orderByDesc('year')
            ->orderByDesc('quarter')
            ->first();

        $teams = [];
        if ($latestRegistration !== null) {
            $teamsRecords = ConsolidationTeam::query()
                ->where('year', $latestRegistration->year)
                ->where('quarter', $latestRegistration->quarter)
                ->get()
                ->keyBy(fn ($item) => $item->type instanceof TeamType ? $item->type->value : (string) $item->type);

            $teams = [
                'esf' => $teamsRecords->get(TeamType::Esf->value)?->total_active ?? 0,
                'saude_bucal' => $teamsRecords->get(TeamType::SaudeBucal->value)?->total_active ?? 0,
                'emulti' => $teamsRecords->get(TeamType::Emulti->value)?->total_active ?? 0,
            ];
        }

        return view('livewire.settings.data-processing', [
            'lastLog' => $lastLog,
            'latestRegistration' => $latestRegistration,
            'teams' => $teams,
        ]);
    }
}
