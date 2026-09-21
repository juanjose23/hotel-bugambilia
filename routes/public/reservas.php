<?php

declare(strict_types=1);

use App\Http\Controllers\Publico\PagoController;
use App\Http\Controllers\Reservas\ReservaController;
use App\Http\Controllers\WebServices\Reservas\CancelarReservaWebServiceController;
use App\Http\Controllers\WebServices\Stripe\StripeReservaPaymentController;
use Illuminate\Http\Request;
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
| Redirecciones de conveniencia hacia el Portal Unificado
|--------------------------------------------------------------------------
*/
Route::get('/mis-reservas', function (Request $request) {
    return redirect()->route('portal.reservas.index', $request->query());
})->name('mis-reservas');

Route::get('/reservas/mis-reservas', function (Request $request) {
    return redirect()->route('portal.reservas.index', $request->query());
});

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
