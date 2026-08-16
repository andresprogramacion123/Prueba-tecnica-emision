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
        return Inertia::render('casos/index', [
            'casos' => CasoListResource::collection(
                $service->listarPaginado($request->integer('per_page', 10))
            ),
        ]);
    }

    public function show(int $id): Response
    {
        return Inertia::render('casos/show', [
            'id' => $id,
        ]);
    }
}
