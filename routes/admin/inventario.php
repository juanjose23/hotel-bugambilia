<?php

declare(strict_types=1);

use App\Http\Controllers\Inventario\ReporteInventarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reportes de Inventario & Stock (Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('inventario/reportes')->name('inventario.reportes.')->group(function () {
    Route::get('/stock-producto/pdf', [ReporteInventarioController::class, 'stockPorProductoPdf'])->name('stock-producto.pdf');
    Route::get('/stock-producto/excel', [ReporteInventarioController::class, 'stockPorProductoExcel'])->name('stock-producto.excel');
    Route::get('/movimientos/pdf', [ReporteInventarioController::class, 'movimientosPdf'])->name('movimientos.pdf');
    Route::get('/movimientos/excel', [ReporteInventarioController::class, 'movimientosExcel'])->name('movimientos.excel');
    Route::get('/cuarentena/pdf', [ReporteInventarioController::class, 'cuarentenaPdf'])->name('cuarentena.pdf');
    Route::get('/cuarentena/excel', [ReporteInventarioController::class, 'cuarentenaExcel'])->name('cuarentena.excel');
    Route::get('/proximos-vencer/pdf', [ReporteInventarioController::class, 'proximosVencerPdf'])->name('proximos-vencer.pdf');
    Route::get('/proximos-vencer/excel', [ReporteInventarioController::class, 'proximosVencerExcel'])->name('proximos-vencer.excel');
    Route::get('/vencidos/pdf', [ReporteInventarioController::class, 'vencidosPdf'])->name('vencidos.pdf');
    Route::get('/vencidos/excel', [ReporteInventarioController::class, 'vencidosExcel'])->name('vencidos.excel');
    Route::get('/mermas/pdf', [ReporteInventarioController::class, 'mermasPdf'])->name('mermas.pdf');
    Route::get('/mermas/excel', [ReporteInventarioController::class, 'mermasExcel'])->name('mermas.excel');
    Route::get('/valorizacion/pdf', [ReporteInventarioController::class, 'valorizacionPdf'])->name('valorizacion.pdf');
    Route::get('/valorizacion/excel', [ReporteInventarioController::class, 'valorizacionExcel'])->name('valorizacion.excel');
    Route::get('/rotacion/pdf', [ReporteInventarioController::class, 'rotacionPdf'])->name('rotacion.pdf');
    Route::get('/rotacion/excel', [ReporteInventarioController::class, 'rotacionExcel'])->name('rotacion.excel');
    Route::get('/mermas-totales/excel', [ReporteInventarioController::class, 'mermasTotalesExcel'])->name('mermas-totales.excel');
    Route::get('/trazabilidad/{lote:codigo_lote}/pdf', [ReporteInventarioController::class, 'trazabilidadLotePdf'])->name('trazabilidad.pdf');
    Route::get('/stock-minimo/pdf', [ReporteInventarioController::class, 'stockMinimoPdf'])->name('stock-minimo.pdf');
    Route::get('/stock-minimo/excel', [ReporteInventarioController::class, 'stockMinimoExcel'])->name('stock-minimo.excel');
    Route::get('/ajustes/pdf', [ReporteInventarioController::class, 'ajustesPdf'])->name('ajustes.pdf');
    Route::get('/ajustes/excel', [ReporteInventarioController::class, 'ajustesExcel'])->name('ajustes.excel');
    Route::get('/costo-ventas/pdf', [ReporteInventarioController::class, 'costoVentasPdf'])->name('costo-ventas.pdf');
    Route::get('/costo-ventas/excel', [ReporteInventarioController::class, 'costoVentasExcel'])->name('costo-ventas.excel');
});
