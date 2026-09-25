<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\OralHealth\OralHealthSnapshotService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('esus:process-oral-health {--year= : Ano de competência (padrão: atual)} {--quarter= : Quadrimestre de competência (padrão: atual)}')]
#[Description('Processa e consolida os indicadores de Saúde Bucal (B1 a B6) a partir do DW do e-SUS PEC.')]
class ProcessOralHealthCommand extends Command
{
    public function handle(OralHealthSnapshotService $snapshotService): int
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $year = (int) ($this->option('year') ?: now()->year);
        $quarter = (int) ($this->option('quarter') ?: min(3, (int) ceil(now()->month / 4)));

        if ($year < 2020 || $year > 2100 || $quarter < 1 || $quarter > 3) {
            $this->error('Ano ou quadrimestre inválido.');

            return self::FAILURE;
        }

        $this->info(sprintf('Iniciando processamento analítico de Saúde Bucal (B1 a B6) para %d / Q%d...', $year, $quarter));

        try {
            $pec = DB::connection('pgsql_esus');
            $pec->getPdo();
        } catch (\Throwable $e) {
            $this->error('Não foi possível conectar ao banco de dados réplica do e-SUS PEC: ' . $e->getMessage());

            return self::FAILURE;
        }

        $result = $snapshotService->process($pec, $year, $quarter);

        $this->info(sprintf(
            'Processamento concluído com sucesso! Equipes: %d | Indicadores: %d | Registros Nominais: %d',
            $result['teams_count'],
            $result['indicators_processed'],
            $result['nominals_count']
        ));

        return self::SUCCESS;
    }
}
