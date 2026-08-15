<?php

namespace App\Console\Commands;

use App\Exports\ReporteCasosPorAbogadoExport;
use App\Services\ReporteCasosPorAbogadoService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

#[Signature('reporte:excel-abogados')]
#[Description('Genera un Excel con los casos de cada abogado activo, una hoja por abogado')]
class ReporteExcelAbogados extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ReporteCasosPorAbogadoService $service): int
    {
        $abogados = $service->obtenerAbogadosConCasos();

        $directorio = storage_path('app/reportes');
        File::ensureDirectoryExists($directorio);

        $nombreArchivo = 'reporte-casos-'.now()->format('Y-m-d-His').'.xlsx';
        $ruta = $directorio.'/'.$nombreArchivo;

        File::put($ruta, Excel::raw(new ReporteCasosPorAbogadoExport($abogados), ExcelFormat::XLSX));

        $totalCasos = $abogados->sum(fn ($abogado) => $abogado->casos->count());

        $this->info("Reporte generado en: {$ruta}");
        $this->line("Hojas (abogados): {$abogados->count()} | Casos totales: {$totalCasos}");

        return self::SUCCESS;
    }
}
