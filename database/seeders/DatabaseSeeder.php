<?php

namespace Database\Seeders;

use App\Models\Abogado;
use App\Models\Caso;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $clientes = Cliente::factory(5)->create();
        $abogados = Abogado::factory(4)->create();

        Caso::factory(10)
            ->recycle($clientes)
            ->create()
            ->each(function (Caso $caso) use ($abogados): void {
                $caso->abogados()->attach(
                    $abogados->random(fake()->numberBetween(1, 3))->pluck('id')
                );
            });
    }
}
