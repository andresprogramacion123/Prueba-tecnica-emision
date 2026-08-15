<?php

namespace App\Exports;

use App\Models\Abogado;
use App\Models\Caso;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class CasosPorAbogadoSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(private readonly Abogado $abogado) {}

    /**
     * @return Collection<int, Caso>
     */
    public function collection(): Collection
    {
        return $this->abogado->casos;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'numero_expediente',
            'cliente_cedula',
            'cliente_nombre',
            'fecha_inicio',
            'fecha_archivo',
            'estado',
        ];
    }

    /**
     * @param  Caso  $caso
     * @return array<int, string|null>
     */
    public function map(mixed $caso): array
    {
        return [
            $caso->numero_expediente,
            $caso->cliente->cedula,
            $caso->cliente->nombre,
            $caso->fecha_inicio?->toDateString(),
            $caso->fecha_archivo?->toDateString(),
            $caso->estado->value,
        ];
    }

    public function title(): string
    {
        $titulo = preg_replace('/[\\\\\/\?\*\[\]:]/', '', $this->abogado->nombre) ?? $this->abogado->nombre;

        return mb_substr(trim($titulo), 0, 31);
    }
}
