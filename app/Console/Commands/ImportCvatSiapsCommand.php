<?php

namespace App\Console\Commands;

use App\Services\CvatService;
use Illuminate\Console\Command;

class ImportCvatSiapsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cvat:import-siaps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa os dados oficiais do Siaps (CVAT - Componente II) dos arquivos CSV';

    /**
     * Execute the console command.
     */
    public function handle(CvatService $cvatService): int
    {
        $this->info('Iniciando importação dos dados oficiais do Siaps (CVAT)...');

        $result = $cvatService->importFromCsvFiles();

        $this->info("Distribuições históricas importadas/atualizadas: {$result['distributions']}");
        $this->info("Avaliações de equipes importadas/atualizadas: {$result['teams']}");

        $this->info('Concluído com sucesso!');

        return Command::SUCCESS;
    }
}
