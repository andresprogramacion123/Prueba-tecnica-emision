<?php

namespace App\Http\Controllers;

use App\Http\Resources\CasoListResource;
use App\Services\CasoListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CasoWebController extends Controller
{
    public function index(Request $request, CasoListingService $service): Response
    {
        [$sortBy, $sortDir] = $service->normalizarOrden(
            $request->string('sort_by')->value() ?: null,
            $request->string('sort_dir')->value() ?: null,
        );
        $search = $request->string('search')->value() ?: null;
        $estado = $request->string('estado')->value() ?: null;

        return Inertia::render('casos/index', [
            'casos' => CasoListResource::collection(
                $service->listarPaginado(
                    porPagina: $request->integer('per_page', 10),
                    sortBy: $sortBy,
                    sortDir: $sortDir,
                    search: $search,
                    estado: $estado,
                )
            ),
            'filters' => [
                'search' => $search,
                'estado' => $estado,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function show(int $id): Response
    {
        return Inertia::render('casos/show', [
            'id' => $id,
        ]);
    }
}
