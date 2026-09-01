<?php

declare(strict_types=1);

use App\Http\Controllers\Publico\AcercaDeController;
use App\Http\Controllers\Publico\ContactoController;
use App\Http\Controllers\Publico\HomeController;
use App\Http\Controllers\Restaurante\ComandaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Páginas Generales del Sitio Web
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');
Route::get('/acerca-de', AcercaDeController::class)->name('acerca-de');
Route::get('/contacto', ContactoController::class)->name('contacto');
Route::get('/pantalla-turnos', [ComandaController::class, 'pantallaTurnosPublica'])->name('pantalla-turnos');
