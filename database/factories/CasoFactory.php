<?php

namespace Database\Factories;

use App\Enums\EstadoCaso;
use App\Models\Caso;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caso>
 */
class CasoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $estado = fake('es_ES')->randomElement(EstadoCaso::cases());
        $fechaInicio = fake('es_ES')->dateTimeBetween('-3 years', '-1 month');

        return [
            'numero_expediente' => fake('es_ES')->unique()->numerify(sprintf(
                '20##-%s-######',
                fake('es_ES')->randomElement(['CIV', 'PEN', 'LAB', 'COM', 'FAM'])
            )),
            'cliente_id' => Cliente::factory(),
            'fecha_inicio' => $fechaInicio,
            'fecha_archivo' => in_array($estado, [EstadoCaso::Archivado, EstadoCaso::Finalizado], true)
                ? fake('es_ES')->dateTimeBetween($fechaInicio, 'now')
                : null,
            'estado' => $estado,
        ];
    }
}
