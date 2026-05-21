<?php

use App\Http\Controllers\Compras\CompraReportController;
use App\Http\Controllers\Inventario\InventarioReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->prefix('admin/compras/reportes')->group(function () {
    Route::get('/solicitud/{solicitud}', [CompraReportController::class, 'imprimirSolicitud'])
        ->middleware('can:Compras:ImprimirSolicitud')
        ->name('reporte.solicitud');
    Route::get('/orden-compra/{orden}', [CompraReportController::class, 'imprimirOrdenCompra'])
        ->middleware('can:Compras:ImprimirOrdenCompra')
        ->name('reporte.orden-compra');
    Route::get('/recepcion/{recepcion}', [CompraReportController::class, 'imprimirRecepcion'])
        ->middleware('can:Compras:ImprimirRecepcion')
        ->name('reporte.recepcion');
    Route::get('/cotizacion/{cotizacion}', [CompraReportController::class, 'imprimirCotizacion'])
        ->middleware('can:Compras:ImprimirCotizacion')
        ->name('reporte.cotizacion');
    Route::get('/comparativa/{solicitud}', [CompraReportController::class, 'imprimirComparativa'])
        ->middleware('can:Compras:ImprimirComparativa')
        ->name('reporte.comparativa');
    Route::get('/resumen-departamentos', [CompraReportController::class, 'imprimirResumenDepartamentos'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.departamentos');
});

Route::middleware(['auth'])->prefix('admin/inventario/reportes')->group(function () {
    // HTB-INV-001: Stock por Producto
    Route::get('/stock-producto/pdf', [InventarioReportController::class, 'stockPorProductoPdf'])
        ->name('reporte.inventario.stock-producto.pdf');
    Route::get('/stock-producto/excel', [InventarioReportController::class, 'stockPorProductoExcel'])
        ->name('reporte.inventario.stock-producto.excel');

    // HTB-INV-003: Movimientos
    Route::get('/movimientos/pdf', [InventarioReportController::class, 'movimientosPdf'])
        ->name('reporte.inventario.movimientos.pdf');
    Route::get('/movimientos/excel', [InventarioReportController::class, 'movimientosExcel'])
        ->name('reporte.inventario.movimientos.excel');

    // HTB-INV-004: Cuarentena
    Route::get('/cuarentena/pdf', [InventarioReportController::class, 'cuarentenaPdf'])
        ->name('reporte.inventario.cuarentena.pdf');
    Route::get('/cuarentena/excel', [InventarioReportController::class, 'cuarentenaExcel'])
        ->name('reporte.inventario.cuarentena.excel');

    // HTB-INV-005: Próximos a Vencer
    Route::get('/proximos-vencer/pdf', [InventarioReportController::class, 'proximosVencerPdf'])
        ->name('reporte.inventario.proximos-vencer.pdf');
    Route::get('/proximos-vencer/excel', [InventarioReportController::class, 'proximosVencerExcel'])
        ->name('reporte.inventario.proximos-vencer.excel');

    // HTB-INV-012: Lotes Vencidos
    Route::get('/vencidos/pdf', [InventarioReportController::class, 'vencidosPdf'])
        ->name('reporte.inventario.vencidos.pdf');
    Route::get('/vencidos/excel', [InventarioReportController::class, 'vencidosExcel'])
        ->name('reporte.inventario.vencidos.excel');

    // HTB-INV-006: Mermas
    Route::get('/mermas/pdf', [InventarioReportController::class, 'mermasPdf'])
        ->name('reporte.inventario.mermas.pdf');
    Route::get('/mermas/excel', [InventarioReportController::class, 'mermasExcel'])
        ->name('reporte.inventario.mermas.excel');

    // HTB-INV-007: Valorización
    Route::get('/valorizacion/pdf', [InventarioReportController::class, 'valorizacionPdf'])
        ->name('reporte.inventario.valorizacion.pdf');
    Route::get('/valorizacion/excel', [InventarioReportController::class, 'valorizacionExcel'])
        ->name('reporte.inventario.valorizacion.excel');

    // HTB-INV-008: Rotación (solo Excel)
    Route::get('/rotacion/excel', [InventarioReportController::class, 'rotacionExcel'])
        ->name('reporte.inventario.rotacion.excel');

    // HTB-INV-009: Mermas Totales (solo Excel)
    Route::get('/mermas-totales/excel', [InventarioReportController::class, 'mermasTotalesExcel'])
        ->name('reporte.inventario.mermas-totales.excel');

    // HTB-INV-011: Trazabilidad por Lote
    Route::get('/trazabilidad/{loteId}/pdf', [InventarioReportController::class, 'trazabilidadLotePdf'])
        ->name('reporte.inventario.trazabilidad.pdf');
});
