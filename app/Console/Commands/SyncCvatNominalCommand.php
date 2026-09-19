<?php

namespace App\Console\Commands;

use App\Services\CvatNominalDwService;
use Illuminate\Console\Command;

class SyncCvatNominalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cvat:sync-nominal {--year=2026} {--month=12}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza as métricas e a relação nominal de Vínculo e Acompanhamento do PEC';

    /**
     * Execute the console command.
     */
    public function handle(CvatNominalDwService $service): int
    {
        $year = (int) $this->option('year');
        $month = (int) $this->option('month');

        $this->info("Inicializando métricas e relação nominal do CVAT para {$year}/M{$month}...");

        $metric = $service->getMetrics($year, $month);
        $this->info("Métricas da Dimensão Cadastro: Total MICI {$metric->mici_total} ({$metric->mici_updated} atualizados)");
        $this->info("Métricas da Dimensão Acompanhamento: Total Sem Critério {$metric->no_criteria_total}, Idosos/Crianças {$metric->elderly_or_child_total}");

        $service->seedInitialCitizens();
        $totalCitizens = \App\Models\CvatNominalCitizen::count();
        $this->info("Base nominal populada: {$totalCitizens} cidadãos cadastrados.");

        $this->info('Concluído com sucesso!');

        return Command::SUCCESS;
    }
}
