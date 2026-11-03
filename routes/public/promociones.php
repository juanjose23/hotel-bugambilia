<?php

declare(strict_types=1);

use App\Http\Controllers\Publico\PromocionPublicoController;
use App\Http\Controllers\Publico\ReservarPromocionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Promociones & Ofertas Especiales
|--------------------------------------------------------------------------
*/
Route::get('/promociones', [PromocionPublicoController::class, 'index'])->name('promociones');

Route::get('/promociones/{promocion}/reservar', [ReservarPromocionController::class, 'show'])
    ->whereNumber('promocion')->name('promociones.reservar');
Route::post('/promociones/{promocion}/reservar', [ReservarPromocionController::class, 'store'])
    ->whereNumber('promocion')->middleware('throttle:12,1')->name('promociones.reservar.store');
