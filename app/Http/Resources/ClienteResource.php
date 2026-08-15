<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ClienteResource',
    title: 'Cliente',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'cedula', type: 'string', example: '1001234567'),
        new OA\Property(property: 'nombre', type: 'string', example: 'Maria Fernanda Gomez'),
        new OA\Property(property: 'telefono', type: 'string', example: '3001234567', nullable: true),
        new OA\Property(property: 'email', type: 'string', example: 'maria.gomez@example.com', nullable: true),
        new OA\Property(property: 'direccion', type: 'string', example: 'Calle 45 # 12-34', nullable: true),
    ],
    type: 'object'
)]
class ClienteResource extends JsonResource
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
            'direccion' => $this->direccion,
        ];
    }
}
