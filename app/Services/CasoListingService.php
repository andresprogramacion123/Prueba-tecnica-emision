<?php

namespace App\Services;

use App\Enums\EstadoCaso;
use App\Models\Caso;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CasoListingService
{
    /**
     * @var list<string>
     */
    public const array SORTABLE_COLUMNS = ['fecha_inicio', 'estado'];

    /**
     * Casos activos (no eliminados) paginados, con el cliente precargado.
     * Ordenados por fecha_inicio descendente por defecto; admite ordenar
     * por fecha_inicio o estado, buscar por numero_expediente o nombre del
     * cliente, y filtrar por estado exacto.
     */
    public function listarPaginado(
        int $porPagina = 10,
        ?string $sortBy = null,
        ?string $sortDir = null,
        ?string $search = null,
        ?string $estado = null,
    ): LengthAwarePaginator {
        [$sortBy, $sortDir] = $this->normalizarOrden($sortBy, $sortDir);

        return Caso::query()
            ->with('cliente')
            ->when(filled($search), function ($query) use ($search) {
                $like = '%'.mb_strtolower($search).'%';

                $query->where(function ($query) use ($like) {
                    $query->whereRaw('LOWER(numero_expediente) LIKE ?', [$like])
                        ->orWhereHas('cliente', function ($query) use ($like) {
                            $query->whereRaw('LOWER(nombre) LIKE ?', [$like]);
                        });
                });
            })
            ->when(EstadoCaso::tryFrom((string) $estado), function ($query, EstadoCaso $estado) {
                $query->where('estado', $estado);
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($porPagina)
            ->withQueryString();
    }

    /**
     * Normaliza sort_by/sort_dir a valores validos, con fecha_inicio
     * descendente como default.
     *
     * @return array{0: string, 1: string}
     */
    public function normalizarOrden(?string $sortBy, ?string $sortDir): array
    {
        $sortBy = in_array($sortBy, self::SORTABLE_COLUMNS, true) ? $sortBy : 'fecha_inicio';
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        return [$sortBy, $sortDir];
    }
}
