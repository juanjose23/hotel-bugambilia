<?php

declare(strict_types=1);

use App\Http\Controllers\Restaurante\ConfirmarPagoStripePedidoController;
use App\Http\Controllers\Restaurante\ConsultarMesasDisponiblesController;
use App\Http\Controllers\Restaurante\CrearPedidoPublicoController;
use App\Http\Controllers\Restaurante\CrearReservaMesaPublicaController;
use App\Http\Controllers\Restaurante\RestauranteCheckoutController;
use App\Http\Controllers\Restaurante\RestauranteController;
use App\Http\Controllers\Restaurante\RestauranteReservarController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Restaurante Bugambilias
|--------------------------------------------------------------------------
*/
Route::get('/restaurante', RestauranteController::class)->name('restaurante');
Route::get('/restaurante/reservar', RestauranteReservarController::class)->name('restaurante.reservar');
Route::get('/restaurante/checkout', RestauranteCheckoutController::class)->name('restaurante.checkout');
Route::post('/restaurante/pedido', CrearPedidoPublicoController::class)->name('restaurante.pedido');
Route::post('/restaurante/confirmar-pago-stripe', ConfirmarPagoStripePedidoController::class)->name('restaurante.confirmar-pago-stripe');
Route::get('/restaurante/mesas-disponibles', ConsultarMesasDisponiblesController::class)->name('restaurante.mesas-disponibles');
Route::post('/restaurante/reservar-mesa', CrearReservaMesaPublicaController::class)->name('restaurante.reservar-mesa');
