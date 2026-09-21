<?php

namespace Database\Seeders;

use App\Models\ConsolidationRegistration;
use App\Models\ConsolidationTeam;
use App\Models\FamilyHealthIndicatorSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RealDashboardDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Consolidação de Cadastros (Q3/2026) - Dados Reais Oficiais
        // Total MICI: 47.480 atualizados + 19.923 desatualizados = 67.403 (70,44%)
        // Total MICDT: 18.363 atualizados + 7.369 desatualizados = 25.732 (71,36%)
        ConsolidationRegistration::query()->updateOrCreate(
            ['year' => 2026, 'quarter' => 3],
            [
                'mici_updated_count' => 47480,
                'mici_outdated_count' => 19923,
                'micdt_updated_count' => 18363,
                'micdt_outdated_count' => 7369,
            ]
        );

        // 2. Consolidação de Equipes Homologadas (Q3/2026) - 19 eSF, 8 eSB, 2 eMulti
        ConsolidationTeam::query()->updateOrCreate(
            ['year' => 2026, 'quarter' => 3, 'type' => 'esf'],
            ['total_active' => 19]
        );
        ConsolidationTeam::query()->updateOrCreate(
            ['year' => 2026, 'quarter' => 3, 'type' => 'esaude_bucal'],
            ['total_active' => 8]
        );
        ConsolidationTeam::query()->updateOrCreate(
            ['year' => 2026, 'quarter' => 3, 'type' => 'emulti'],
            ['total_active' => 2]
        );

        // Limpa snapshots fictícios de demonstração de C4..C7 se gerados anteriormente por baseline
        if (Schema::hasTable('family_health_indicator_snapshots')) {
            FamilyHealthIndicatorSnapshot::query()
                ->whereIn('indicator_code', ['c4', 'c5', 'c6', 'c7'])
                ->where('team_name', 'like', '%Centro de Saúde Central%')
                ->orWhere(function ($query): void {
                    $query->whereIn('indicator_code', ['c4', 'c5', 'c6', 'c7'])
                        ->where('team_name', 'Consolidado Municipal');
                })
                ->delete();
        }
    }
}
