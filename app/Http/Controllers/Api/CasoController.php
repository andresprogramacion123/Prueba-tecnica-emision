<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CasoResource;
use App\Models\Caso;
use OpenApi\Attributes as OA;

class CasoController extends Controller
{
    #[OA\Get(
        path: '/api/casos/{id}',
        summary: 'Obtener un caso por su id',
        description: 'Devuelve la informacion completa de un caso: sus campos propios, el cliente asociado y todos los abogados asignados.',
        security: [['bearerAuth' => []]],
        tags: ['Casos'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id del caso',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Caso encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/CasoResource')
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Caso no encontrado (o eliminado logicamente)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Caso] 999'),
                    ]
                )
            ),
        ]
    )]
    public function show(Caso $caso): CasoResource
    {
        $caso->load(['cliente', 'abogados']);

        return new CasoResource($caso);
    }
}
