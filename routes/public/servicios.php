<?php

declare(strict_types=1);

use App\Http\Controllers\Servicios\ServicioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Servicios & Experiencias
|--------------------------------------------------------------------------
*/
Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
Route::get('/servicios/{slug}', [ServicioController::class, 'show'])->name('servicio-detalle');
