<?php

namespace Database\Factories;

use App\Models\Abogado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Abogado>
 */
class AbogadoFactory extends Factory
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
            'tarjeta_profesional' => fake('es_ES')->unique()->numerify('T.P. No. ######'),
        ];
    }
}
