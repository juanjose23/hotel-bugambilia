<?php

declare(strict_types=1);

use App\Http\Controllers\Publico\PromocionPublicoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Promociones & Ofertas Especiales
|--------------------------------------------------------------------------
*/
Route::get('/promociones', [PromocionPublicoController::class, 'index'])->name('promociones');
