<?php

use App\Http\Controllers\Api\CasoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/casos/{caso}', [CasoController::class, 'show'])->name('api.casos.show');
});
