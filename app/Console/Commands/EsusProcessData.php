<?php

namespace App\Console\Commands;

use App\Services\EsusDataProcessingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('esus:process-data {--scope=all : Escopo de processamento: all (geral completo) ou c1 (indicador C1)} {--year=2026 : Ano de competência} {--quarter=3 : Quadrimestre de competência}')]
#[Description('Executa o processamento e consolidação das tabelas do e-SUS PEC para os Indicadores da APS.')]
class EsusProcessData extends Command
{
    public function handle(EsusDataProcessingService $service): int
    {
        $scope = strtolower((string) ($this->option('scope') ?: 'all'));
        if (! in_array($scope, ['all', 'c1'], true)) {
            $scope = 'all';
        }

        $year = (int) ($this->option('year') ?: 2026);
        $quarter = (int) ($this->option('quarter') ?: 3);

        $this->info(sprintf('Iniciando processamento analítico e-SUS PEC [Escopo: %s] para %d/Q%d...', strtoupper($scope), $year, $quarter));

        $result = $service->process(function (int $percent, string $step): void {
            $this->output->write(sprintf("\r[%3d%%] %s", $percent, $step));
        }, $year, $quarter, $scope);

        $this->newLine();

        if ($result['success']) {
            $this->info($result['message']);

            return self::SUCCESS;
        }

        $this->error($result['message']);

        return self::FAILURE;
    }
}
