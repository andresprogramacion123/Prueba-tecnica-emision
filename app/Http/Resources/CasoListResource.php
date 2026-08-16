<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CasoListResource',
    title: 'Caso (listado)',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'numero_expediente', type: 'string', example: '2024-CIV-000123'),
        new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date', example: '2024-03-10'),
        new OA\Property(property: 'estado', type: 'string', example: 'en_tramite', enum: ['en_tramite', 'archivado', 'suspendido', 'finalizado']),
        new OA\Property(
            property: 'cliente',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'nombre', type: 'string', example: 'Maria Fernanda Gomez'),
            ]
        ),
    ],
    type: 'object'
)]
class CasoListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_expediente' => $this->numero_expediente,
            'fecha_inicio' => $this->fecha_inicio?->toDateString(),
            'estado' => $this->estado->value,
            'cliente' => [
                'id' => $this->cliente->id,
                'nombre' => $this->cliente->nombre,
            ],
        ];
    }
}
