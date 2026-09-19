<?php

namespace App\Console\Commands;

use App\Services\PecSchemaInventoryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;
use Throwable;

#[Signature('esus:inspect-schema {--output= : Caminho do relatório JSON; por padrão usa storage/app/private}')]
#[Description('Inventaria somente os metadados do DW e-SUS PEC em uma transação PostgreSQL somente leitura.')]
class EsusInspectSchema extends Command
{
    public function handle(PecSchemaInventoryService $inventoryService): int
    {
        try {
            $inventory = $inventoryService->inspect(DB::connection('pgsql_esus'));
            $outputPath = $this->resolveOutputPath((string) $this->option('output'));

            File::ensureDirectoryExists(dirname($outputPath));

            $json = json_encode(
                $inventory,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            );

            if (File::put($outputPath, $json.PHP_EOL, true) === false) {
                throw new RuntimeException('Não foi possível gravar o relatório de inventário.');
            }

            $this->info(sprintf(
                'Inventário concluído em modo somente leitura: %d tabelas, %d colunas e %d índices.',
                $inventory['summary']['tables'],
                $inventory['summary']['columns'],
                $inventory['summary']['indexes'],
            ));
            $this->line('Relatório: '.$outputPath);

            if ($inventory['pec_version'] === null) {
                $this->warn('A versão do PEC não foi inferida por nome de tabela ou coluna; valide-a no aplicativo instalado.');
            }

            return self::SUCCESS;
        } catch (JsonException $exception) {
            $this->error('Falha ao serializar o inventário: '.$exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error('Falha no inventário do PEC: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    private function resolveOutputPath(string $configuredPath): string
    {
        if ($configuredPath === '') {
            return storage_path('app/private/pec-schema-inventory-'.now()->format('Ymd-His').'.json');
        }

        if (str_starts_with($configuredPath, '/') || preg_match('/^[a-zA-Z]:[\\\\\/]/', $configuredPath) === 1) {
            return $configuredPath;
        }

        return base_path($configuredPath);
    }
}
