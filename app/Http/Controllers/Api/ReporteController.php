<?php

namespace App\Http\Controllers\Api;

use App\Exports\ReporteCasosPorAbogadoExport;
use App\Http\Controllers\Controller;
use App\Services\ReporteCasosPorAbogadoService;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteController extends Controller
{
    #[OA\Get(
        path: '/api/reportes/casos-por-abogado',
        summary: 'Descargar reporte Excel de casos por abogado',
        description: 'Genera al vuelo (sin persistir en disco) un archivo Excel con una hoja por cada abogado activo, listando sus casos junto con los datos del cliente asociado.',
        security: [['bearerAuth' => []]],
        tags: ['Reportes'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Archivo Excel (.xlsx) generado correctamente',
                content: new OA\MediaType(
                    mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                )
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
        ]
    )]
    public function casosPorAbogado(ReporteCasosPorAbogadoService $service): BinaryFileResponse
    {
        $abogados = $service->obtenerAbogadosConCasos();

        return Excel::download(
            new ReporteCasosPorAbogadoExport($abogados),
            'reporte-casos-por-abogado.xlsx'
        );
    }
}
