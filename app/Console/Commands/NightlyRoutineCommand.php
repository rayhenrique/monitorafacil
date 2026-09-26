<?php

namespace App\Console\Commands;

use App\Services\SettingsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Throwable;

#[Signature('monitora:nightly-routine {--force : Força a execução mesmo se a rotina estiver desativada nas configurações}')]
#[Description('Executa a rotina noturna completa (20 etapas) de atualização do código, banco, compilação de assets, consolidação analítica do e-SUS PEC e otimização de caches.')]
class NightlyRoutineCommand extends Command
{
    public function handle(SettingsService $settings): int
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $enabled = $settings->get('nightly_routine_enabled', '1') !== '0';
        if (! $enabled && ! $this->option('force')) {
            $this->warn('Rotina noturna está desativada nas configurações. Use --force para ignorar.');

            return self::SUCCESS;
        }

        $startTime = now()->format('Y-m-d H:i:s');
        $settings->set('nightly_routine_last_run', $startTime);
        $settings->set('nightly_routine_last_status', 'running');

        $this->info("==============================================================================");
        $this->info("Iniciando Rotina Noturna Monitora Fácil em: {$startTime}");
        $this->info("==============================================================================");

        $projectDir = $settings->get('nightly_routine_project_path') ?: '/home/kltecnologia-monitorafacil/htdocs/monitorafacil.kltecnologia.com';
        $scriptPath = base_path('scripts/nightly-sync.sh');

        $logPath = storage_path('logs/nightly-sync.log');
        if (! is_dir(dirname($logPath))) {
            @mkdir(dirname($logPath), 0755, true);
        }

        // Se o script bash existir e estivermos em ambiente com bash disponível
        if (file_exists($scriptPath) && DIRECTORY_SEPARATOR === '/') {
            $this->info("Executando via script otimizado scripts/nightly-sync.sh...");
            $process = new Process(['bash', $scriptPath]);
            $process->setTimeout(3600);
            $process->setIdleTimeout(600);

            try {
                $process->run(function ($type, $buffer) use ($logPath): void {
                    $this->output->write($buffer);
                    @file_put_contents($logPath, $buffer, FILE_APPEND);
                });

                $isSuccess = $process->isSuccessful();
            } catch (Throwable $e) {
                $this->error("Erro ao executar script: " . $e->getMessage());
                @file_put_contents($logPath, "\nERRO: " . $e->getMessage() . "\n", FILE_APPEND);
                $isSuccess = false;
            }
        } else {
            // Execução passo a passo via PHP Process (compatível com Windows/Linux)
            $phpBin = PHP_BINARY;
            $commands = [
                ['name' => '1. Git Pull', 'cmd' => ['git', 'pull', 'origin', 'main']],
                ['name' => '2. Composer Install', 'cmd' => ['composer', 'install', '--no-dev', '--optimize-autoloader']],
                ['name' => '3. NPM Run Build', 'cmd' => ['npm', 'run', 'build']],
                ['name' => '4. Migrate', 'cmd' => [$phpBin, 'artisan', 'migrate', '--force']],
                ['name' => '5. CVAT Sync Nominal', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'cvat:sync-nominal']],
                ['name' => '6. C1 Mais Acesso', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'esus:process-data', '--scope=c1']],
                ['name' => '7. C2 Desenv. Infantil', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'esus:process-data', '--scope=c2']],
                ['name' => '8. C3 Gestação e Puerpério', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'esus:process-data', '--scope=c3']],
                ['name' => '9. C4 Diabetes Mellitus', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'esus:process-data', '--scope=c4']],
                ['name' => '10. C5 Hipertensão Arterial', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'esus:process-data', '--scope=c5']],
                ['name' => '11. C6 Pessoa Idosa', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'esus:process-data', '--scope=c6']],
                ['name' => '12. C7 Cuidado da Mulher', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'esus:process-data', '--scope=c7']],
                ['name' => '13. Saúde Bucal (process-data)', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'esus:process-data', '--scope=oral-health']],
                ['name' => '14. Saúde Bucal (process-oral-health)', 'cmd' => [$phpBin, '-d', 'memory_limit=1024M', 'artisan', 'esus:process-oral-health']],
                ['name' => '15. Livewire Publish Assets', 'cmd' => [$phpBin, 'artisan', 'livewire:publish', '--assets']],
                ['name' => '16. Optimize Clear', 'cmd' => [$phpBin, 'artisan', 'optimize:clear']],
                ['name' => '17. Config Cache', 'cmd' => [$phpBin, 'artisan', 'config:cache']],
                ['name' => '18. Queue Restart', 'cmd' => [$phpBin, 'artisan', 'queue:restart']],
                ['name' => '19. Route Cache', 'cmd' => [$phpBin, 'artisan', 'route:cache']],
                ['name' => '20. View Cache', 'cmd' => [$phpBin, 'artisan', 'view:cache']],
            ];

            $isSuccess = true;
            foreach ($commands as $index => $item) {
                $stepNum = $index + 1;
                $this->info("==> [{$stepNum}/20] {$item['name']}...");
                @file_put_contents($logPath, "==> [{$stepNum}/20] {$item['name']}...\n", FILE_APPEND);

                try {
                    $process = new Process($item['cmd'], base_path());
                    $process->setTimeout(1800);
                    $process->run(function ($type, $buffer) use ($logPath): void {
                        $this->output->write($buffer);
                        @file_put_contents($logPath, $buffer, FILE_APPEND);
                    });

                    if (! $process->isSuccessful() && ! in_array($stepNum, [18], true)) {
                        $this->warn("Aviso: Falha na etapa {$stepNum}. Continuando...");
                    }
                } catch (Throwable $e) {
                    $this->warn("Aviso de exceção na etapa {$stepNum}: " . $e->getMessage());
                }
            }
        }

        $endTime = now()->format('Y-m-d H:i:s');
        $status = $isSuccess ? 'success' : 'failed';
        $settings->set('nightly_routine_last_status', $status);
        $settings->set('nightly_routine_finished_at', $endTime);

        $this->newLine();
        $this->info("==============================================================================");
        $this->info("Rotina Noturna finalizada com status [{$status}] em: {$endTime}");
        $this->info("==============================================================================");

        return $isSuccess ? self::SUCCESS : self::FAILURE;
    }
}
