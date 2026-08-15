<?php

namespace App\Services;

use App\Models\Abogado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ReporteCasosPorAbogadoService
{
    /**
     * Abogados activos con sus casos (y el cliente de cada caso) precargados,
     * ordenados por fecha_inicio ascendente dentro de cada abogado.
     *
     * @return Collection<int, Abogado>
     */
    public function obtenerAbogadosConCasos(): Collection
    {
        return Abogado::query()
            ->with(['casos' => function (BelongsToMany $query): void {
                $query->orderBy('fecha_inicio')->with('cliente');
            }])
            ->orderBy('nombre')
            ->get();
    }
}
