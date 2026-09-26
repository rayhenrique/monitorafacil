<?php

namespace App\Livewire\Settings;

use App\Enums\TeamType;
use App\Jobs\SyncCvatNominalJob;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\SyncLog;
use App\Services\EsusDataProcessingService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Artisan;
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

    public string $selectedScope = 'all'; // 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'cvat' ou 'all' (Geral Completo)

    public ?float $executionTimeMs = null;

    // Configurações da Rotina Noturna Automática (03:00 da manhã)
    public bool $nightlyRoutineEnabled = true;

    public string $nightlyRoutineTime = '03:00';

    public string $nightlyRoutineProjectPath = '/home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com';

    public string $nightlyRoutinePhpBin = 'php8.5';

    public string $nightlyRoutineMemoryLimit = '1024M';

    public ?string $nightlySuccessMessage = null;

    public ?string $nightlyErrorMessage = null;

    public ?string $nightlyLastRun = null;

    public ?string $nightlyLastStatus = null;

    public ?string $nightlyFinishedAt = null;

    public bool $showNightlyLogModal = false;

    public string $nightlyLogContent = '';

    public bool $isExecutingNightlyNow = false;

    public function mount(SettingsService $settings): void
    {
        $this->nightlyRoutineEnabled = $settings->get('nightly_routine_enabled', '1') !== '0';
        $this->nightlyRoutineTime = $settings->get('nightly_routine_time', '03:00') ?: '03:00';
        $this->nightlyRoutineProjectPath = $settings->get('nightly_routine_project_path') ?: '/home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com';
        $this->nightlyRoutinePhpBin = $settings->get('nightly_routine_php_bin', 'php8.5') ?: 'php8.5';
        $this->nightlyRoutineMemoryLimit = $settings->get('nightly_routine_memory_limit', '1024M') ?: '1024M';
        $this->nightlyLastRun = $settings->get('nightly_routine_last_run');
        $this->nightlyLastStatus = $settings->get('nightly_routine_last_status');
        $this->nightlyFinishedAt = $settings->get('nightly_routine_finished_at');
    }

    public function setScope(string $scope): void
    {
        $this->selectedScope = in_array($scope, ['c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'oral-health', 'b', 'cvat', 'all'], true) ? $scope : 'all';
    }

    public function processOralHealth(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'oral-health';
        $this->executeProcessing($service, 'oral-health');
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

    public function processC6(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'c6';
        $this->executeProcessing($service, 'c6');
    }

    public function processC7(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'c7';
        $this->executeProcessing($service, 'c7');
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
            'c4' => 'Indicador C4 (Diabetes Mellitus)',
            'c5' => 'Indicador C5 (Hipertensão Arterial)',
            'c6' => 'Indicador C6 (Cuidado da Pessoa Idosa)',
            'c7' => 'Indicador C7 (Prevenção do Câncer / Mulheres)',
            'oral-health', 'b' => 'Saúde Bucal (Indicadores B1 a B6)',
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

    public function saveNightlySettings(SettingsService $settings): void
    {
        $this->nightlySuccessMessage = null;
        $this->nightlyErrorMessage = null;

        $time = trim($this->nightlyRoutineTime);
        if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
            $this->nightlyErrorMessage = 'O horário informado deve estar no formato HH:MM (ex: 03:00).';

            return;
        }

        $settings->set('nightly_routine_enabled', $this->nightlyRoutineEnabled ? '1' : '0');
        $settings->set('nightly_routine_time', $time);
        $settings->set('nightly_routine_project_path', trim($this->nightlyRoutineProjectPath));
        $settings->set('nightly_routine_php_bin', trim($this->nightlyRoutinePhpBin));
        $settings->set('nightly_routine_memory_limit', trim($this->nightlyRoutineMemoryLimit));

        $this->nightlySuccessMessage = "Configurações da rotina noturna salvas com sucesso! Horário agendado: {$time}.";
    }

    public function runNightlyRoutineNow(SettingsService $settings): void
    {
        $this->nightlySuccessMessage = null;
        $this->nightlyErrorMessage = null;
        $this->isExecutingNightlyNow = true;

        try {
            $exitCode = Artisan::call('monitora:nightly-routine', ['--force' => true]);
            $this->nightlyLastRun = $settings->get('nightly_routine_last_run');
            $this->nightlyLastStatus = $settings->get('nightly_routine_last_status');
            $this->nightlyFinishedAt = $settings->get('nightly_routine_finished_at');

            if ($exitCode === 0) {
                $this->nightlySuccessMessage = 'Rotina noturna completa executada com sucesso!';
            } else {
                $this->nightlyErrorMessage = 'A rotina noturna foi concluída com pendências ou avisos. Verifique o log de execução.';
            }
        } catch (Throwable $e) {
            $this->nightlyErrorMessage = 'Erro ao disparar rotina noturna: '.$e->getMessage();
        } finally {
            $this->isExecutingNightlyNow = false;
        }
    }

    public function viewNightlyLog(): void
    {
        $logPath = storage_path('logs/nightly-sync.log');
        if (file_exists($logPath)) {
            $lines = @file($logPath);
            $lastLines = array_slice($lines ?: [], -150);
            $this->nightlyLogContent = implode('', $lastLines);
        } else {
            $this->nightlyLogContent = "Nenhum registro de log encontrado em storage/logs/nightly-sync.log ainda.\nExecute a rotina noturna manualmente ou aguarde o próximo agendamento.";
        }

        $this->showNightlyLogModal = true;
    }

    public function closeNightlyLogModal(): void
    {
        $this->showNightlyLogModal = false;
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

        $timeParts = explode(':', $this->nightlyRoutineTime ?: '03:00');
        $cronMinute = (int) ($timeParts[1] ?? 0);
        $cronHour = (int) ($timeParts[0] ?? 3);
        $projectPath = rtrim($this->nightlyRoutineProjectPath ?: '/home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com', '/');
        $crontabCommand = "{$cronMinute} {$cronHour} * * * /bin/bash {$projectPath}/scripts/nightly-sync.sh >> {$projectPath}/storage/logs/nightly-sync.log 2>&1";
        $schedulerCron = "* * * * * cd {$projectPath} && {$this->nightlyRoutinePhpBin} artisan schedule:run >> /dev/null 2>&1";

        return view('livewire.settings.data-processing', [
            'lastLog' => $lastLog,
            'latestRegistration' => $latestRegistration,
            'teams' => $teams,
            'crontabCommand' => $crontabCommand,
            'schedulerCron' => $schedulerCron,
        ]);
    }
}
