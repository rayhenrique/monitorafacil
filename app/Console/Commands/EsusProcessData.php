<?php

namespace App\Console\Commands;

use App\Services\EsusDataProcessingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('esus:process-data {--scope=all : Escopo de processamento: all (geral completo), c1 (indicador C1), c2 (indicador C2), c3 (indicador C3), c4 (indicador C4), c5 (indicador C5), c6 (indicador C6) ou c7 (indicador C7)} {--year= : Ano de competência (padrão: atual)} {--quarter= : Quadrimestre de competência (padrão: atual)}')]
#[Description('Executa o processamento e consolidação das tabelas do e-SUS PEC para os Indicadores da APS.')]
class EsusProcessData extends Command
{
    public function handle(EsusDataProcessingService $service): int
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $scope = strtolower((string) ($this->option('scope') ?: 'all'));
        if (! in_array($scope, ['all', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7'], true)) {
            $scope = 'all';
        }

        $year = (int) ($this->option('year') ?: now()->year);
        $quarter = (int) ($this->option('quarter') ?: min(3, (int) ceil(now()->month / 4)));
        if ($year < 2020 || $year > 2100 || $quarter < 1 || $quarter > 3) {
            $this->error('Ano ou quadrimestre inválido.');

            return self::FAILURE;
        }

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
