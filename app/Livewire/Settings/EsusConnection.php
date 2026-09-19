<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app')]
class EsusConnection extends Component
{
    public bool $isTesting = false;

    public ?bool $connectionSuccess = null;

    public ?string $testMessage = null;

    public ?float $latencyMs = null;

    /** @var array<string, bool> */
    public array $tablesStatus = [];

    public function testConnection(): void
    {
        $this->isTesting = true;
        $this->connectionSuccess = null;
        $this->testMessage = null;
        $this->latencyMs = null;
        $this->tablesStatus = [];

        $startTime = microtime(true);

        try {
            $connection = DB::connection('pgsql_esus');
            // Force connection and test query
            $connection->statement("SET statement_timeout TO '5s'");
            $connection->select('SELECT 1');

            $this->latencyMs = round((microtime(true) - $startTime) * 1000, 2);
            $this->connectionSuccess = true;
            $this->testMessage = 'Conexão com a base de dados PostgreSQL do e-SUS PEC estabelecida com sucesso!';

            // Check key PEC tables
            $keyTables = [
                'tb_equipe',
                'tb_cidadao',
                'tb_fat_cad_individual',
                'tb_fat_atendimento_individual',
            ];

            foreach ($keyTables as $table) {
                try {
                    $exists = $connection->selectOne(
                        'SELECT to_regclass(?) IS NOT NULL AS exists',
                        [$table]
                    );
                    $this->tablesStatus[$table] = (bool) ($exists->exists ?? false);
                } catch (Throwable) {
                    $this->tablesStatus[$table] = false;
                }
            }
        } catch (Throwable $e) {
            $this->latencyMs = round((microtime(true) - $startTime) * 1000, 2);
            $this->connectionSuccess = false;
            $this->testMessage = 'Falha ao conectar na base do e-SUS PEC: '.$e->getMessage();
        } finally {
            $this->isTesting = false;
        }
    }

    public function render(): View
    {
        $config = config('database.connections.pgsql_esus', []);

        $connectionInfo = [
            'host' => $config['host'] ?? '127.0.0.1',
            'port' => $config['port'] ?? '5432',
            'database' => $config['database'] ?? 'esus',
            'username' => $config['username'] ?? 'esus_readonly',
            'sslmode' => $config['sslmode'] ?? 'prefer',
            'driver' => 'PostgreSQL',
        ];

        return view('livewire.settings.esus-connection', [
            'connectionInfo' => $connectionInfo,
        ]);
    }
}
