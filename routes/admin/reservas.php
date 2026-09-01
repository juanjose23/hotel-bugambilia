<?php

declare(strict_types=1);

use App\Http\Controllers\Reservas\ReporteReservaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reportes de Reservas & Ocupación (Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('reservas/reportes')->name('reservas.reportes.')->group(function () {
    Route::get('/ocupacion/pdf', [ReporteReservaController::class, 'ocupacionPdf'])->name('ocupacion.pdf');
    Route::get('/ventas-ingresos/pdf', [ReporteReservaController::class, 'ventasIngresosPdf'])->name('ventas-ingresos.pdf');
    Route::get('/reservas-estado/pdf', [ReporteReservaController::class, 'reservasEstadoPdf'])->name('reservas-estado.pdf');
    Route::get('/huespedes/pdf', [ReporteReservaController::class, 'huespedesPdf'])->name('huespedes.pdf');
    Route::get('/rendimiento-habitaciones/pdf', [ReporteReservaController::class, 'rendimientoHabitacionesPdf'])->name('rendimiento-habitaciones.pdf');
});
