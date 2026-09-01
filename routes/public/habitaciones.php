<?php

declare(strict_types=1);

use App\Http\Controllers\Habitaciones\HabitacionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Catálogo y Disponibilidad de Habitaciones
|--------------------------------------------------------------------------
*/
Route::get('/habitaciones', [HabitacionController::class, 'index'])->name('habitaciones');
Route::get('/habitaciones/{slug}', [HabitacionController::class, 'show'])->name('habitacion-detalle');
Route::get('/habitaciones/{slug}/disponibilidad', [HabitacionController::class, 'disponibilidad'])->name('habitaciones.disponibilidad');
Route::get('/habitaciones/{slug}/dias-agotados', [HabitacionController::class, 'diasAgotados'])->name('habitaciones.dias-agotados');
Route::get('/habitaciones/{slug}/reservar', [HabitacionController::class, 'mostrarReserva'])->name('habitaciones.reservar');
