<?php

namespace App\Livewire\Settings;

use App\Enums\TeamType;
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

    public ?float $executionTimeMs = null;

    public function processNow(EsusDataProcessingService $service): void
    {
        $this->isProcessing = true;
        $this->processMessage = null;
        $this->processStatus = null;
        $this->progressPercent = 10;
        $this->currentStep = 'Iniciando verificação do banco de dados e-SUS PEC...';

        try {
            $result = $service->process(function (int $percent, string $step, array $tables): void {
                $this->progressPercent = $percent;
                $this->currentStep = $step;
                $this->tablesReport = $tables;
            });

            $this->progressPercent = 100;
            $this->currentStep = 'Processamento concluído com sucesso!';
            $this->processStatus = 'success';
            $this->processMessage = $result['message'];
            $this->tablesReport = $result['tables'];
            $this->executionTimeMs = $result['execution_time_ms'];
        } catch (Throwable $e) {
            $this->processStatus = 'error';
            $this->processMessage = 'Exceção ao executar o processamento: '.$e->getMessage();
            $this->currentStep = 'Falha durante o processamento.';
        } finally {
            $this->isProcessing = false;
        }
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
