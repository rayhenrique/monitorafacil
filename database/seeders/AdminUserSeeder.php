<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('bootstrap_admin.password');

        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Defina SEED_ADMIN_PASSWORD com pelo menos 12 caracteres antes de executar o seeder.');
        }

        User::firstOrCreate(
            ['email' => config('bootstrap_admin.email')],
            [
                'name' => config('bootstrap_admin.name'),
                'password' => Hash::make($password),
            ],
        );
    }
}
