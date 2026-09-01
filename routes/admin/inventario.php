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
    Route::get('/stock-producto/pdf', [ReporteInventarioController::class, 'stockPorProductoPdf'])->middleware('can:Inventario:ReporteStock')->name('stock-producto.pdf');
    Route::get('/stock-producto/excel', [ReporteInventarioController::class, 'stockPorProductoExcel'])->middleware('can:Inventario:ReporteStock')->name('stock-producto.excel');
    Route::get('/movimientos/pdf', [ReporteInventarioController::class, 'movimientosPdf'])->middleware('can:Inventario:ReporteMovimientos')->name('movimientos.pdf');
    Route::get('/movimientos/excel', [ReporteInventarioController::class, 'movimientosExcel'])->middleware('can:Inventario:ReporteMovimientos')->name('movimientos.excel');
    Route::get('/cuarentena/pdf', [ReporteInventarioController::class, 'cuarentenaPdf'])->middleware('can:Inventario:ReporteCuarentena')->name('cuarentena.pdf');
    Route::get('/cuarentena/excel', [ReporteInventarioController::class, 'cuarentenaExcel'])->middleware('can:Inventario:ReporteCuarentena')->name('cuarentena.excel');
    Route::get('/proximos-vencer/pdf', [ReporteInventarioController::class, 'proximosVencerPdf'])->middleware('can:Inventario:ReporteProximosVencer')->name('proximos-vencer.pdf');
    Route::get('/proximos-vencer/excel', [ReporteInventarioController::class, 'proximosVencerExcel'])->middleware('can:Inventario:ReporteProximosVencer')->name('proximos-vencer.excel');
    Route::get('/vencidos/pdf', [ReporteInventarioController::class, 'vencidosPdf'])->middleware('can:Inventario:ReporteVencidos')->name('vencidos.pdf');
    Route::get('/vencidos/excel', [ReporteInventarioController::class, 'vencidosExcel'])->middleware('can:Inventario:ReporteVencidos')->name('vencidos.excel');
    Route::get('/mermas/pdf', [ReporteInventarioController::class, 'mermasPdf'])->middleware('can:Inventario:ReporteMermas')->name('mermas.pdf');
    Route::get('/mermas/excel', [ReporteInventarioController::class, 'mermasExcel'])->middleware('can:Inventario:ReporteMermas')->name('mermas.excel');
    Route::get('/valorizacion/pdf', [ReporteInventarioController::class, 'valorizacionPdf'])->middleware('can:Inventario:ReporteValorizacion')->name('valorizacion.pdf');
    Route::get('/valorizacion/excel', [ReporteInventarioController::class, 'valorizacionExcel'])->middleware('can:Inventario:ReporteValorizacion')->name('valorizacion.excel');
    Route::get('/rotacion/pdf', [ReporteInventarioController::class, 'rotacionPdf'])->middleware('can:Inventario:ReporteRotacion')->name('rotacion.pdf');
    Route::get('/rotacion/excel', [ReporteInventarioController::class, 'rotacionExcel'])->middleware('can:Inventario:ReporteRotacion')->name('rotacion.excel');
    Route::get('/mermas-totales/excel', [ReporteInventarioController::class, 'mermasTotalesExcel'])->middleware('can:Inventario:ReporteMermasTotales')->name('mermas-totales.excel');
    Route::get('/trazabilidad/{loteId}/pdf', [ReporteInventarioController::class, 'trazabilidadLotePdf'])->middleware('can:Inventario:ReporteTrazabilidad')->name('trazabilidad.pdf');
    Route::get('/stock-minimo/pdf', [ReporteInventarioController::class, 'stockMinimoPdf'])->middleware('can:Inventario:ReporteStockMinimo')->name('stock-minimo.pdf');
    Route::get('/stock-minimo/excel', [ReporteInventarioController::class, 'stockMinimoExcel'])->middleware('can:Inventario:ReporteStockMinimo')->name('stock-minimo.excel');
    Route::get('/ajustes/pdf', [ReporteInventarioController::class, 'ajustesPdf'])->middleware('can:Inventario:ReporteAjustes')->name('ajustes.pdf');
    Route::get('/ajustes/excel', [ReporteInventarioController::class, 'ajustesExcel'])->middleware('can:Inventario:ReporteAjustes')->name('ajustes.excel');
    Route::get('/costo-ventas/pdf', [ReporteInventarioController::class, 'costoVentasPdf'])->middleware('can:Inventario:ReporteCostoVentas')->name('costo-ventas.pdf');
    Route::get('/costo-ventas/excel', [ReporteInventarioController::class, 'costoVentasExcel'])->middleware('can:Inventario:ReporteCostoVentas')->name('costo-ventas.excel');
});
