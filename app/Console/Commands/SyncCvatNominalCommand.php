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

        $this->info("=== Sincronização do Vínculo e Acompanhamento Territorial (CVAT) ===");
        $this->line("Competência de Referência: {$year}/M{$month}");
        $this->line("Iniciando extração direta da base PostgreSQL e-SUS PEC (pgsql_esus)...");

        $startTime = microtime(true);

        $result = $service->syncFromPec(
            null,
            $year,
            $month,
            function (int $percent, string $step): void {
                $this->line(sprintf(' [%3d%%] %s', $percent, $step));
            }
        );

        $duration = round(microtime(true) - $startTime, 2);

        if (! $result['success']) {
            $this->error("Falha na sincronização: {$result['message']}");
            return Command::FAILURE;
        }

        $metric = $result['metrics'];
        $this->newLine();
        $this->info("✓ {$result['message']} (Tempo: {$duration}s)");
        $this->newLine();

        $this->line('--- Métricas da Dimensão Cadastro ---');
        $this->table(
            ['Indicador', 'Valor', 'Percentual'],
            [
                ['Total Geral de Cadastros Individuais (MICI)', number_format($metric->mici_total, 0, ',', '.'), '100%'],
                ['MICI Atualizados (últimos 24 meses)', number_format($metric->mici_updated, 0, ',', '.'), round(($metric->mici_updated / max(1, $metric->mici_total)) * 100, 1) . '%'],
                ['MICI Desatualizados', number_format($metric->mici_outdated, 0, ',', '.'), round(($metric->mici_outdated / max(1, $metric->mici_total)) * 100, 1) . '%'],
                ['MICI Sem Ficha Domiciliar (Sem MICDT)', number_format($metric->mici_without_micdt_total, 0, ',', '.'), round(($metric->mici_without_micdt_total / max(1, $metric->mici_total)) * 100, 1) . '%'],
                ['MICI Com Ficha Domiciliar (Com MICDT)', number_format($metric->mici_with_micdt_total, 0, ',', '.'), round(($metric->mici_with_micdt_total / max(1, $metric->mici_total)) * 100, 1) . '%'],
                ['MICI e MICDT Atualizados', number_format($metric->mici_and_micdt_updated, 0, ',', '.'), round(($metric->mici_and_micdt_updated / max(1, $metric->mici_total)) * 100, 1) . '%'],
                ['Cidadãos Vinculados a Equipes', number_format($metric->citizens_linked, 0, ',', '.'), round(($metric->citizens_linked / max(1, $metric->mici_total)) * 100, 1) . '%'],
                ['Cidadãos Não Vinculados a Equipes', number_format($metric->citizens_not_linked, 0, ',', '.'), round(($metric->citizens_not_linked / max(1, $metric->mici_total)) * 100, 1) . '%'],
            ]
        );

        $this->newLine();
        $this->line('--- Métricas da Dimensão Acompanhamento ---');
        $this->table(
            ['Critério de Vulnerabilidade', 'Total Cidadãos', 'Acompanhados', 'Não Acompanhados', '% Acompanhamento'],
            [
                [
                    'Sem Critérios de Vulnerabilidade',
                    number_format($metric->no_criteria_total, 0, ',', '.'),
                    number_format($metric->no_criteria_accompanied, 0, ',', '.'),
                    number_format($metric->no_criteria_not_accompanied, 0, ',', '.'),
                    round(($metric->no_criteria_accompanied / max(1, $metric->no_criteria_total)) * 100, 1) . '%',
                ],
                [
                    'Idoso (≥60) ou Criança (<6)',
                    number_format($metric->elderly_or_child_total, 0, ',', '.'),
                    number_format($metric->elderly_or_child_accompanied, 0, ',', '.'),
                    number_format($metric->elderly_or_child_not_accompanied, 0, ',', '.'),
                    round(($metric->elderly_or_child_accompanied / max(1, $metric->elderly_or_child_total)) * 100, 1) . '%',
                ],
                [
                    'Benefício Social (BPC ou PBF)',
                    number_format($metric->bpc_or_pbf_total, 0, ',', '.'),
                    number_format($metric->bpc_or_pbf_accompanied, 0, ',', '.'),
                    number_format($metric->bpc_or_pbf_not_accompanied, 0, ',', '.'),
                    round(($metric->bpc_or_pbf_accompanied / max(1, $metric->bpc_or_pbf_total)) * 100, 1) . '%',
                ],
                [
                    'Idoso/Criança E Benefício Social',
                    number_format($metric->elderly_child_and_benefit_total, 0, ',', '.'),
                    number_format($metric->elderly_child_and_benefit_accompanied, 0, ',', '.'),
                    number_format($metric->elderly_child_and_benefit_not_accompanied, 0, ',', '.'),
                    round(($metric->elderly_child_and_benefit_accompanied / max(1, $metric->elderly_child_and_benefit_total)) * 100, 1) . '%',
                ],
            ]
        );

        $this->newLine();
        $this->info("Total de cidadãos registrados na tabela local: {$result['nominal_citizens_count']}");
        $this->info("Data do último registro no PEC: {$metric->last_record_date}");

        return Command::SUCCESS;
    }
}
