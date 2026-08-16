<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CasoListResource;
use App\Http\Resources\CasoResource;
use App\Models\Caso;
use App\Services\CasoListingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class CasoController extends Controller
{
    #[OA\Get(
        path: '/api/casos',
        summary: 'Listar casos paginados',
        description: 'Devuelve un listado paginado de casos activos (no eliminados), con el cliente asociado, ordenados por fecha_inicio descendente. Endpoint publico, no requiere autenticacion.',
        tags: ['Casos'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Numero de pagina',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Casos por pagina (por defecto 10)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10, example: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado paginado de casos',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CasoListResource')),
                        new OA\Property(
                            property: 'links',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'first', type: 'string', nullable: true),
                                new OA\Property(property: 'last', type: 'string', nullable: true),
                                new OA\Property(property: 'prev', type: 'string', nullable: true),
                                new OA\Property(property: 'next', type: 'string', nullable: true),
                            ]
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                                new OA\Property(property: 'total', type: 'integer', example: 42),
                            ]
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(Request $request, CasoListingService $service): AnonymousResourceCollection
    {
        return CasoListResource::collection(
            $service->listarPaginado($request->integer('per_page', 10))
        );
    }

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
