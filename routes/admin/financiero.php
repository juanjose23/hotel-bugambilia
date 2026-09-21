<?php

declare(strict_types=1);

use App\Http\Controllers\Facturacion\FacturaImpresionController;
use App\Http\Controllers\Financiero\ReporteFinancieroController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reportes Financieros & Facturación (Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('financiero/reportes')->name('financiero.reportes.')->group(function () {
    Route::get('/cuentas-cobrar/pdf', [ReporteFinancieroController::class, 'cuentasCobrarPdf'])->name('cuentas-cobrar.pdf');
    Route::get('/facturacion-ventas/pdf', [ReporteFinancieroController::class, 'facturacionVentasPdf'])->name('facturacion-ventas.pdf');
    Route::get('/resumen-ejecutivo/pdf', [ReporteFinancieroController::class, 'resumenEjecutivoPdf'])->name('resumen-ejecutivo.pdf');
});

Route::prefix('facturacion/facturas')->name('facturacion.facturas.')->group(function () {
    Route::get('/{factura}/imprimir', [FacturaImpresionController::class, 'imprimir'])->name('imprimir');
    Route::get('/{factura}/pdf', [FacturaImpresionController::class, 'pdf'])->name('pdf');
});
