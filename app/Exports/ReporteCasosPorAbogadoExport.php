<?php

namespace App\Exports;

use App\Models\Abogado;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteCasosPorAbogadoExport implements Export, WithMultipleSheets
{
    /**
     * @param  Collection<int, Abogado>  $abogados
     */
    public function __construct(private readonly Collection $abogados) {}

    /**
     * @return array<int, CasosPorAbogadoSheetExport>
     */
    public function sheets(): array
    {
        return $this->abogados
            ->map(fn (Abogado $abogado): CasosPorAbogadoSheetExport => new CasosPorAbogadoSheetExport($abogado))
            ->all();
    }
}
