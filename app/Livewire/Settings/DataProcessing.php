<?php

namespace App\Livewire\Settings;

use App\Enums\TeamType;
use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\SyncLog;
use App\Services\CvatNominalDwService;
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

    public string $selectedScope = 'all'; // 'c1', 'c2', 'c3', 'cvat' ou 'all' (Geral Completo)

    public ?float $executionTimeMs = null;

    public function setScope(string $scope): void
    {
        $this->selectedScope = in_array($scope, ['c1', 'c2', 'c3', 'cvat', 'all'], true) ? $scope : 'all';
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

    public function processCvat(CvatNominalDwService $service): void
    {
        $this->selectedScope = 'cvat';
        $this->isProcessing = true;
        $this->processMessage = null;
        $this->processStatus = null;
        $this->updateProgress(15, 'Conectando ao e-SUS PEC e verificando tabelas do Vínculo Territorial...');

        try {
            $startTime = microtime(true);
            $this->updateProgress(35, 'Extraindo cadastros individuais (MICI) e fichas domiciliares (MICDT)...');

            $result = $service->syncFromPec();

            $this->updateProgress(75, 'Consolidando métricas e relações nominais para busca ativa...');

            $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
            $this->executionTimeMs = $executionTimeMs;
            $this->processStatus = 'success';

            $totalMici = number_format($result['metrics']->mici_total, 0, ',', '.');
            $miciAtualizados = number_format($result['metrics']->mici_updated, 0, ',', '.');
            $comMicdt = number_format($result['metrics']->mici_with_micdt_total, 0, ',', '.');
            $vinculados = number_format($result['metrics']->citizens_linked, 0, ',', '.');
            $totalNominal = number_format($result['nominal_citizens_count'], 0, ',', '.');

            $this->processMessage = "Processamento do módulo Vínculo e Acompanhamento Territorial concluído com sucesso!\n"
                . "• Total Geral de MICI: {$totalMici} ({$miciAtualizados} atualizados)\n"
                . "• Total com MICDT: {$comMicdt}\n"
                . "• Cidadãos Vinculados: {$vinculados}\n"
                . "• Relação Nominal da Busca Ativa: {$totalNominal} cidadãos sincronizados ({$executionTimeMs} ms).";

            $this->tablesReport = [
                'tb_fat_cad_individual' => [
                    'name' => 'tb_fat_cad_individual',
                    'description' => 'Fichas de Cadastro Individual (MICI)',
                    'status' => 'Concluído',
                    'rows' => (int) $result['metrics']->mici_total,
                    'message' => "MICI atualizados: {$miciAtualizados}",
                ],
                'tb_fat_cad_domiciliar' => [
                    'name' => 'tb_fat_cad_domiciliar',
                    'description' => 'Fichas de Cadastro Domiciliar e Territorial (MICDT)',
                    'status' => 'Concluído',
                    'rows' => (int) $result['metrics']->mici_with_micdt_total,
                    'message' => "Cadastros com domicílio: {$comMicdt}",
                ],
                'cvat_nominal_citizens' => [
                    'name' => 'cvat_nominal_citizens',
                    'description' => 'Relação Nominal e Busca Ativa (Monitora Fácil)',
                    'status' => 'Atualizado',
                    'rows' => (int) $result['nominal_citizens_count'],
                    'message' => 'Base local sincronizada para busca e acompanhamento',
                ],
            ];

            $this->updateProgress(100, 'Processamento do Vínculo Territorial concluído com sucesso!');
        } catch (Throwable $e) {
            $this->processStatus = 'error';
            $this->processMessage = 'Exceção ao processar Vínculo e Acompanhamento: '.$e->getMessage();
            $this->updateProgress(100, 'Falha durante o processamento do Vínculo e Acompanhamento.');
        } finally {
            $this->isProcessing = false;
        }
    }

    public function processAll(EsusDataProcessingService $service): void
    {
        $this->selectedScope = 'all';
        $this->executeProcessing($service, 'all');
    }

    public function processNow(EsusDataProcessingService $service, ?string $scope = null): void
    {
        $targetScope = $scope ?: $this->selectedScope;
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
