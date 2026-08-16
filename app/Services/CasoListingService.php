<?php

namespace App\Services;

use App\Models\Caso;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CasoListingService
{
    /**
     * Casos activos (no eliminados) paginados, con el cliente precargado,
     * ordenados por fecha_inicio descendente.
     */
    public function listarPaginado(int $porPagina = 10): LengthAwarePaginator
    {
        return Caso::query()
            ->with('cliente')
            ->orderByDesc('fecha_inicio')
            ->paginate($porPagina)
            ->withQueryString();
    }
}
