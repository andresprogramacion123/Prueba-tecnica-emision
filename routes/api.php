<?php

use App\Http\Controllers\Api\CasoController;
use App\Http\Controllers\Api\ReporteController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/casos/{caso}', [CasoController::class, 'show'])->name('api.casos.show');
    Route::get('/reportes/casos-por-abogado', [ReporteController::class, 'casosPorAbogado'])->name('api.reportes.casos-por-abogado');
});
