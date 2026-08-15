<?php

use App\Models\Abogado;
use App\Models\Caso;
use App\Models\Cliente;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;

afterEach(function (): void {
    File::deleteDirectory(storage_path('app/reportes'));
});

test('genera un excel con una hoja por cada abogado activo', function () {
    $abogadoConCasos = Abogado::factory()->create();
    Abogado::factory()->create();
    $abogadoEliminado = Abogado::factory()->create();
    $abogadoEliminado->delete();

    $cliente = Cliente::factory()->create();
    $caso = Caso::factory()->for($cliente)->create();
    $abogadoConCasos->casos()->attach($caso);

    $this->artisan('reporte:excel-abogados')->assertSuccessful();

    $archivos = File::files(storage_path('app/reportes'));
    expect($archivos)->toHaveCount(1);

    $ruta = (string) $archivos[0];
    expect(basename($ruta))->toMatch('/^reporte-casos-\d{4}-\d{2}-\d{2}-\d{6}\.xlsx$/');

    $spreadsheet = IOFactory::load($ruta);
    expect($spreadsheet->getSheetCount())->toBe(2);
});
