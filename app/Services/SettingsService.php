<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'municipality:settings';

    private const CACHE_TTL_SECONDS = 300;

    /** @return array<string, string|null> */
    public function all(): array
    {
        try {
            return Cache::remember(
                self::CACHE_KEY,
                self::CACHE_TTL_SECONDS,
                static fn (): array => Setting::query()->pluck('value', 'key')->all(),
            );
        } catch (\Throwable) {
            return [];
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(string $key, ?string $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget(self::CACHE_KEY);
    }

    public const INDICATORS_CACHE_KEY = 'indicators:last_processed_at';

    /**
     * Retorna a data e hora da última atualização/processamento dos indicadores no sistema.
     * Consulta as fontes analíticas (Snapshots de Saúde Bucal, Saúde da Família C1-C7, Coortes, Logs de Sincronização e Rotina Noturna).
     */
    public function getLastIndicatorsProcessedAt(): ?\Illuminate\Support\Carbon
    {
        try {
            $rawDateString = Cache::remember(self::INDICATORS_CACHE_KEY, 60, function (): ?string {
                $candidates = [];

                // 1. Configuração explícita de processamento gravada
                $explicitTimestamp = Setting::query()->where('key', 'indicators_last_processed_at')->value('value');
                if ($explicitTimestamp) {
                    $candidates[] = \Illuminate\Support\Carbon::parse($explicitTimestamp);
                }

                $nightlyFinishedAt = Setting::query()->where('key', 'nightly_routine_finished_at')->value('value');
                if ($nightlyFinishedAt) {
                    $candidates[] = \Illuminate\Support\Carbon::parse($nightlyFinishedAt);
                }

                // 2. Saúde Bucal (Snapshots dos indicadores B1 a B6)
                if (\Illuminate\Support\Facades\Schema::hasTable('oral_health_indicator_snapshots')) {
                    $ohMax = \App\Models\OralHealth\OralHealthIndicatorSnapshot::query()->max('updated_at');
                    if ($ohMax) {
                        $candidates[] = \Illuminate\Support\Carbon::parse($ohMax);
                    }
                }

                // 3. Saúde da Família (Snapshots gerais C1 a C7)
                if (\Illuminate\Support\Facades\Schema::hasTable('family_health_indicator_snapshots')) {
                    $fhMax = \App\Models\FamilyHealthIndicatorSnapshot::query()->max('updated_at');
                    if ($fhMax) {
                        $candidates[] = \Illuminate\Support\Carbon::parse($fhMax);
                    }
                }

                // 4. Coortes específicas de indicadores individuais (C2 a C7)
                $cohortTables = [
                    'c2_cohort_snapshots',
                    'c3_cohort_snapshots',
                    'c4_cohort_snapshots',
                    'c5_cohort_snapshots',
                    'c6_cohort_snapshots',
                    'c7_cohort_snapshots',
                ];
                foreach ($cohortTables as $table) {
                    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                        $cMax = \Illuminate\Support\Facades\DB::table($table)->max('updated_at');
                        if ($cMax) {
                            $candidates[] = \Illuminate\Support\Carbon::parse($cMax);
                        }
                    }
                }

                // 5. Histórico de Sincronização concluído com sucesso
                if (\Illuminate\Support\Facades\Schema::hasTable('sync_logs')) {
                    $syncMax = \App\Models\SyncLog::query()
                        ->where('status', \App\Enums\SyncStatus::Success)
                        ->max('finished_at');
                    if ($syncMax) {
                        $candidates[] = \Illuminate\Support\Carbon::parse($syncMax);
                    }
                }

                // 6. CVAT métricas nominais
                if (\Illuminate\Support\Facades\Schema::hasTable('cvat_nominal_metrics')) {
                    $cvatMax = \App\Models\CvatNominalMetric::query()->max('updated_at');
                    if ($cvatMax) {
                        $candidates[] = \Illuminate\Support\Carbon::parse($cvatMax);
                    }
                }

                if (empty($candidates)) {
                    return null;
                }

                /** @var \Illuminate\Support\Carbon $latest */
                $latest = collect($candidates)->max();

                return $latest->setTimezone(config('esus.schedule_timezone', 'America/Maceio'))->format('Y-m-d H:i:s');
            });

            return $rawDateString ? \Illuminate\Support\Carbon::parse($rawDateString)->setTimezone(config('esus.schedule_timezone', 'America/Maceio')) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Registra o momento atual como a última atualização concluída dos indicadores e invalida o cache.
     */
    public function recordIndicatorsProcessedNow(): void
    {
        $now = now()->setTimezone(config('esus.schedule_timezone', 'America/Maceio'))->format('Y-m-d H:i:s');
        $this->set('indicators_last_processed_at', $now);
        Cache::forget(self::INDICATORS_CACHE_KEY);
    }
}
