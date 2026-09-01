<?php

declare(strict_types=1);

use App\Http\Controllers\Clientes\CuentaClienteController;
use App\Http\Controllers\Publico\PagoController;
use App\Http\Controllers\Reservas\DetalleReservaPortalController;
use App\Http\Controllers\Reservas\MisReservasController;
use App\Http\Controllers\Reservas\ReservaController;
use App\Http\Controllers\WebServices\Reservas\CancelarReservaWebServiceController;
use App\Http\Controllers\WebServices\Stripe\StripeReservaPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pagos & Pasarela Stripe
|--------------------------------------------------------------------------
*/
Route::get('/pago', PagoController::class)->name('pago');
Route::get('/reservas/{reserva}/pago', [PagoController::class, 'reserva'])
    ->name('reservas.pago');
Route::post('/pagos/stripe/reservas/intento', [StripeReservaPaymentController::class, 'crearIntento'])
    ->name('pagos.stripe.reservas.intento');
Route::post('/pagos/stripe/reservas/confirmar', [StripeReservaPaymentController::class, 'confirmarCliente'])
    ->name('pagos.stripe.reservas.confirmar');
Route::post('/stripe/webhook', [StripeReservaPaymentController::class, 'webhook'])
    ->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| Portal de Huéspedes
|--------------------------------------------------------------------------
*/
Route::get('/mis-reservas', MisReservasController::class)->name('mis-reservas');
Route::get('/reservas/mis-reservas', MisReservasController::class);
Route::get('/portal/reserva/{id}', [DetalleReservaPortalController::class, 'show'])->name('portal.reserva-detalle');
Route::get('/portal/cuenta', [CuentaClienteController::class, 'show'])->middleware('auth')->name('portal.cuenta');
Route::get('/portal/perfil', [CuentaClienteController::class, 'show'])->middleware('auth')->name('portal.perfil');

/*
|--------------------------------------------------------------------------
| Gestión de Reservas Públicas y Cancelaciones
|--------------------------------------------------------------------------
*/
Route::post('/reservas', [ReservaController::class, 'crear'])->name('reservas.crear');
Route::post('/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])
    ->middleware('auth')
    ->name('reservas.cancelar');
Route::post('/web-services/reservas/{reserva}/cancelar', CancelarReservaWebServiceController::class)
    ->middleware('auth')
    ->name('web-services.reservas.cancelar');
Route::get('/reservas/{reserva}/voucher', [ReservaController::class, 'voucher'])
    ->name('reservas.voucher');
