<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cedula' => fake('es_ES')->unique()->numerify('#########'),
            'nombre' => fake('es_ES')->name(),
            'telefono' => '3'.fake('es_ES')->numerify('#########'),
            'email' => fake('es_ES')->unique()->safeEmail(),
            'direccion' => sprintf(
                '%s %d # %d-%d',
                fake('es_ES')->randomElement(['Calle', 'Carrera', 'Avenida', 'Diagonal', 'Transversal']),
                fake('es_ES')->numberBetween(1, 130),
                fake('es_ES')->numberBetween(1, 99),
                fake('es_ES')->numberBetween(1, 99)
            ),
        ];
    }
}
