<?php

declare(strict_types=1);

use App\Http\Controllers\Espacios\EspacioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Espacios & Salones de Eventos
|--------------------------------------------------------------------------
*/
Route::get('/espacios', [EspacioController::class, 'index'])->name('espacios');
Route::get('/espacios/{slug}', [EspacioController::class, 'show'])->name('espacio-detalle');
Route::get('/espacios/{slug}/reservar', [EspacioController::class, 'mostrarReserva'])->name('espacios.reservar');
