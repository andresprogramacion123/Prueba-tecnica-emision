<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AbogadoResource',
    title: 'Abogado',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'cedula', type: 'string', example: '1007654321'),
        new OA\Property(property: 'nombre', type: 'string', example: 'Carlos Andres Ramirez'),
        new OA\Property(property: 'telefono', type: 'string', example: '3009876543', nullable: true),
        new OA\Property(property: 'email', type: 'string', example: 'carlos.ramirez@example.com', nullable: true),
        new OA\Property(property: 'tarjeta_profesional', type: 'string', example: 'T.P. No. 123456', nullable: true),
    ],
    type: 'object'
)]
class AbogadoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cedula' => $this->cedula,
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'tarjeta_profesional' => $this->tarjeta_profesional,
        ];
    }
}
