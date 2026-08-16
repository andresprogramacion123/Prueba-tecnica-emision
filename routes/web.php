<?php

use App\Http\Controllers\CasoWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CasoWebController::class, 'index'])->name('home');
Route::get('/casos/{id}', [CasoWebController::class, 'show'])
    ->whereNumber('id')
    ->name('casos.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
