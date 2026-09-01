<?php

declare(strict_types=1);

use App\Http\Controllers\Compras\ReporteCompraController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reportes de Compras & Proveedores (Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('compras/reportes')->name('compras.reportes.')->group(function () {
    Route::get('/solicitud/{solicitud}', [ReporteCompraController::class, 'imprimirSolicitud'])->middleware('can:Compras:ImprimirSolicitud')->name('solicitud');
    Route::get('/orden-compra/{orden}', [ReporteCompraController::class, 'imprimirOrdenCompra'])->middleware('can:Compras:ImprimirOrdenCompra')->name('orden-compra');
    Route::get('/recepcion/{recepcion}', [ReporteCompraController::class, 'imprimirRecepcion'])->middleware('can:Compras:ImprimirRecepcion')->name('recepcion');
    Route::get('/cotizacion/{cotizacion}', [ReporteCompraController::class, 'imprimirCotizacion'])->middleware('can:Compras:ImprimirCotizacion')->name('cotizacion');
    Route::get('/comparativa/{solicitud}', [ReporteCompraController::class, 'imprimirComparativa'])->middleware('can:Compras:ImprimirComparativa')->name('comparativa');
    Route::get('/devolucion/{devolucion}', [ReporteCompraController::class, 'devolucion'])->middleware('can:Compras:ImprimirDevolucion')->name('devolucion');

    // Reportes Generales de Compras
    Route::middleware('can:Compras:ImprimirReportesCompras')->group(function () {
        Route::get('/resumen-departamentos', [ReporteCompraController::class, 'imprimirResumenDepartamentos'])->name('resumen-departamentos');
        Route::get('/solicitudes-estado', [ReporteCompraController::class, 'solicitudesEstado'])->name('solicitudes-estado');
        Route::get('/seguimiento-oc', [ReporteCompraController::class, 'seguimientoOc'])->name('seguimiento-oc');
        Route::get('/recepciones-proveedor', [ReporteCompraController::class, 'recepcionesPorProveedor'])->name('recepciones-proveedor');
        Route::get('/analisis-precio', [ReporteCompraController::class, 'analisisPrecio'])->name('analisis-precio');
        Route::get('/valorizacion', [ReporteCompraController::class, 'valorizacion'])->name('valorizacion');
        Route::get('/rotacion', [ReporteCompraController::class, 'rotacion'])->name('rotacion');
        Route::get('/tiempos-entrega', [ReporteCompraController::class, 'tiemposEntrega'])->name('tiempos-entrega');
        Route::get('/ranking-proveedores', [ReporteCompraController::class, 'rankingProveedores'])->name('ranking-proveedores');
        Route::get('/devoluciones', [ReporteCompraController::class, 'devoluciones'])->name('devoluciones');
        Route::get('/trazabilidad-completa/{solicitud}', [ReporteCompraController::class, 'trazabilidadCompleta'])->name('trazabilidad-completa');
    });
});
