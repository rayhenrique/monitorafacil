<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

#[Signature('admin:reset-password {email? : E-mail do administrador} {--password= : Nova senha desejada}')]
#[Description('Redefine ou gera uma nova senha para o usuário administrador.')]
class ResetAdminPassword extends Command
{
    public function handle(): int
    {
        $email = (string) ($this->argument('email') ?: config('bootstrap_admin.email', 'admin@monitorafacil.test'));
        $password = (string) $this->option('password');

        if ($password === '') {
            $password = Str::password(16);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Usuário com o e-mail [{$email}] não foi encontrado.");

            return self::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info('Senha do administrador atualizada com sucesso!');
        $this->table(
            ['E-mail', 'Nova Senha'],
            [[$user->email, $password]]
        );

        return self::SUCCESS;
    }
}
