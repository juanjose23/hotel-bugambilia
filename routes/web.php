<?php

use App\Http\Controllers\Activos\ActivoReportController;
use App\Http\Controllers\Compras\CompraReportController;
use App\Http\Controllers\Inventario\InventarioReportController;
use App\Http\Controllers\Servicios\ServicioReportController;
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
        ->middleware('can:Inventario:ReporteStock')
        ->name('reporte.inventario.stock-producto.pdf');
    Route::get('/stock-producto/excel', [InventarioReportController::class, 'stockPorProductoExcel'])
        ->middleware('can:Inventario:ReporteStock')
        ->name('reporte.inventario.stock-producto.excel');

    // HTB-INV-003: Movimientos
    Route::get('/movimientos/pdf', [InventarioReportController::class, 'movimientosPdf'])
        ->middleware('can:Inventario:ReporteMovimientos')
        ->name('reporte.inventario.movimientos.pdf');
    Route::get('/movimientos/excel', [InventarioReportController::class, 'movimientosExcel'])
        ->middleware('can:Inventario:ReporteMovimientos')
        ->name('reporte.inventario.movimientos.excel');

    // HTB-INV-004: Cuarentena
    Route::get('/cuarentena/pdf', [InventarioReportController::class, 'cuarentenaPdf'])
        ->middleware('can:Inventario:ReporteCuarentena')
        ->name('reporte.inventario.cuarentena.pdf');
    Route::get('/cuarentena/excel', [InventarioReportController::class, 'cuarentenaExcel'])
        ->middleware('can:Inventario:ReporteCuarentena')
        ->name('reporte.inventario.cuarentena.excel');

    // HTB-INV-005: Próximos a Vencer
    Route::get('/proximos-vencer/pdf', [InventarioReportController::class, 'proximosVencerPdf'])
        ->middleware('can:Inventario:ReporteProximosVencer')
        ->name('reporte.inventario.proximos-vencer.pdf');
    Route::get('/proximos-vencer/excel', [InventarioReportController::class, 'proximosVencerExcel'])
        ->middleware('can:Inventario:ReporteProximosVencer')
        ->name('reporte.inventario.proximos-vencer.excel');

    // HTB-INV-012: Lotes Vencidos
    Route::get('/vencidos/pdf', [InventarioReportController::class, 'vencidosPdf'])
        ->middleware('can:Inventario:ReporteVencidos')
        ->name('reporte.inventario.vencidos.pdf');
    Route::get('/vencidos/excel', [InventarioReportController::class, 'vencidosExcel'])
        ->middleware('can:Inventario:ReporteVencidos')
        ->name('reporte.inventario.vencidos.excel');

    // HTB-INV-006: Mermas
    Route::get('/mermas/pdf', [InventarioReportController::class, 'mermasPdf'])
        ->middleware('can:Inventario:ReporteMermas')
        ->name('reporte.inventario.mermas.pdf');
    Route::get('/mermas/excel', [InventarioReportController::class, 'mermasExcel'])
        ->middleware('can:Inventario:ReporteMermas')
        ->name('reporte.inventario.mermas.excel');

    // HTB-INV-007: Valorización
    Route::get('/valorizacion/pdf', [InventarioReportController::class, 'valorizacionPdf'])
        ->middleware('can:Inventario:ReporteValorizacion')
        ->name('reporte.inventario.valorizacion.pdf');
    Route::get('/valorizacion/excel', [InventarioReportController::class, 'valorizacionExcel'])
        ->middleware('can:Inventario:ReporteValorizacion')
        ->name('reporte.inventario.valorizacion.excel');

    // HTB-INV-008: Rotación (solo Excel)
    Route::get('/rotacion/excel', [InventarioReportController::class, 'rotacionExcel'])
        ->middleware('can:Inventario:ReporteRotacion')
        ->name('reporte.inventario.rotacion.excel');

    // HTB-INV-009: Mermas Totales (solo Excel)
    Route::get('/mermas-totales/excel', [InventarioReportController::class, 'mermasTotalesExcel'])
        ->middleware('can:Inventario:ReporteMermasTotales')
        ->name('reporte.inventario.mermas-totales.excel');

    // HTB-INV-011: Trazabilidad por Lote
    Route::get('/trazabilidad/{loteId}/pdf', [InventarioReportController::class, 'trazabilidadLotePdf'])
        ->middleware('can:Inventario:ReporteTrazabilidad')
        ->name('reporte.inventario.trazabilidad.pdf');
});

// HTB-SER-001: Histórico de Servicios por Precio por Moneda
Route::middleware(['auth'])->prefix('admin/servicios/reportes')->group(function () {
    Route::get('/historico-precios/pdf', [ServicioReportController::class, 'historicoPreciosPdf'])
        ->middleware('can:Servicios:ReporteHistoricoPrecios')
        ->name('reporte.servicios.historico-precios.pdf');
    Route::get('/historico-precios/excel', [ServicioReportController::class, 'historicoPreciosExcel'])
        ->middleware('can:Servicios:ReporteHistoricoPrecios')
        ->name('reporte.servicios.historico-precios.excel');
});

// HTB-ACT-001, HTB-ACT-002 & HTB-ACT-003: Activos Fijos
Route::middleware(['auth'])->prefix('admin/activos/reportes')->group(function () {
    Route::get('/inventario-general/pdf', [ActivoReportController::class, 'inventarioGeneralPdf'])
        ->middleware('can:Activos:ReporteInventario')
        ->name('reporte.activos.inventario-general.pdf');
    Route::get('/inventario-general/excel', [ActivoReportController::class, 'inventarioGeneralExcel'])
        ->middleware('can:Activos:ReporteInventario')
        ->name('reporte.activos.inventario-general.excel');
    Route::get('/ficha/{activo}/pdf', [ActivoReportController::class, 'fichaActivoPdf'])
        ->middleware('can:Activos:ReporteFicha')
        ->name('reporte.activos.ficha.pdf');
    Route::get('/mantenimiento/{mantenimiento}/pdf', [ActivoReportController::class, 'fichaMantenimientoPdf'])
        ->middleware('can:Activos:ReporteMantenimiento')
        ->name('reporte.activos.mantenimiento.pdf');
    Route::get('/etiquetas/pdf', [ActivoReportController::class, 'etiquetasPdf'])
        ->middleware('can:Activos:ReporteEtiquetas')
        ->name('reporte.activos.etiquetas.pdf');

    // HTB-ACT-005: Activos por Ubicación
    Route::get('/por-ubicacion/pdf', [ActivoReportController::class, 'porUbicacionPdf'])
        ->middleware('can:Activos:ReportePorUbicacion')
        ->name('reporte.activos.por-ubicacion.pdf');

    // HTB-ACT-006: Historial de Movimientos
    Route::get('/historial-movimientos/pdf', [ActivoReportController::class, 'historialMovimientosPdf'])
        ->middleware('can:Activos:ReporteHistorial')
        ->name('reporte.activos.historial-movimientos.pdf');

    // HTB-ACT-007: Activos en Mantenimiento
    Route::get('/en-mantenimiento/pdf', [ActivoReportController::class, 'enMantenimientoPdf'])
        ->middleware('can:Activos:ReporteMantenimientoActivos')
        ->name('reporte.activos.en-mantenimiento.pdf');

    // HTB-ACT-008: Garantías Próximas a Vencer
    Route::get('/garantias-proximas/pdf', [ActivoReportController::class, 'garantiasProximasPdf'])
        ->middleware('can:Activos:ReporteGarantias')
        ->name('reporte.activos.garantias-proximas.pdf');

    // HTB-ACT-009: Activos Dados de Baja
    Route::get('/dados-de-baja/pdf', [ActivoReportController::class, 'dadosDeBajaPdf'])
        ->middleware('can:Activos:ReporteBajas')
        ->name('reporte.activos.dados-de-baja.pdf');

    // HTB-ACT-010: Activos Extraviados
    Route::get('/extraviados/pdf', [ActivoReportController::class, 'extraviadosPdf'])
        ->middleware('can:Activos:ReporteExtraviados')
        ->name('reporte.activos.extraviados.pdf');

    // HTB-ACT-011: Activos Sin Asignación
    Route::get('/sin-asignacion/pdf', [ActivoReportController::class, 'sinAsignacionPdf'])
        ->middleware('can:Activos:ReporteSinAsignacion')
        ->name('reporte.activos.sin-asignacion.pdf');

    // HTB-ACT-012: Mantenimientos Vencidos
    Route::get('/mantenimientos-vencidos/pdf', [ActivoReportController::class, 'mantenimientosVencidosPdf'])
        ->middleware('can:Activos:ReporteMantenimientosVencidos')
        ->name('reporte.activos.mantenimientos-vencidos.pdf');

    // HTB-ACT-013: Hoja de Habitación / Espacio
    Route::get('/hoja-habitacion/{tipo}/{id}/pdf', [ActivoReportController::class, 'hojaHabitacionPdf'])
        ->middleware('can:Activos:ReporteHojaHabitacion')
        ->name('reporte.activos.hoja-habitacion.pdf');
});
