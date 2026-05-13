<?php

use App\Http\Controllers\Compras\CompraReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->prefix('admin/compras/reportes')->group(function () {
    Route::get('/solicitud/{solicitud}', [CompraReportController::class, 'imprimirSolicitud'])->name('reporte.solicitud');
    Route::get('/orden-compra/{orden}', [CompraReportController::class, 'imprimirOrdenCompra'])->name('reporte.orden-compra');
    Route::get('/recepcion/{recepcion}', [CompraReportController::class, 'imprimirRecepcion'])->name('reporte.recepcion');
    Route::get('/cotizacion/{cotizacion}', [CompraReportController::class, 'imprimirCotizacion'])->name('reporte.cotizacion');
    Route::get('/resumen-departamentos', [CompraReportController::class, 'imprimirResumenDepartamentos'])->name('reporte.compras.departamentos');
});
