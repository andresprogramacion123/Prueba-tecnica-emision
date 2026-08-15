<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('token:generate {email : Email del usuario para el cual generar el token}')]
#[Description('Genera un Bearer Token de Sanctum para un usuario existente (o lo crea si no existe)')]
class GenerateApiToken extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Usuario de Prueba',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $token = $user->createToken('cli-token')->plainTextToken;

        $this->info("Bearer Token para {$user->email}:");
        $this->line($token);

        return self::SUCCESS;
    }
}
