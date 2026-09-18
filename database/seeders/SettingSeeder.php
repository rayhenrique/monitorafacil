<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('settings')->insertOrIgnore(
            array_map(
                static fn (string $key): array => [
                    'key' => $key,
                    'value' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                ['municipio_nome', 'municipio_ibge', 'municipio_cnes_sede', 'logo_path'],
            ),
        );
    }
}
