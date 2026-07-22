<?php

use App\Http\Controllers\Activos\ReporteActivoController;
use App\Http\Controllers\AutenticacionController;
use App\Http\Controllers\Compras\ReporteCompraController;
use App\Http\Controllers\Inventario\ReporteInventarioController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\Restaurante\ComandaController;
use App\Http\Controllers\Servicios\ServicioReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::get('/servicios', [PublicPageController::class, 'servicios'])->name('servicios');
Route::get('/servicios/{slug}', [PublicPageController::class, 'servicioDetalle'])->name('servicio-detalle');
Route::get('/acerca-de', [PublicPageController::class, 'acercaDe'])->name('acerca-de');
Route::get('/contacto', [PublicPageController::class, 'contacto'])->name('contacto');
Route::get('/login', [PublicPageController::class, 'login'])->name('login');
Route::post('/login', [AutenticacionController::class, 'iniciarSesion']);
Route::get('/registro', [PublicPageController::class, 'registro'])->name('registro');
Route::post('/registro', [AutenticacionController::class, 'registrar']);
Route::get('/cambiar-contrasena', [AutenticacionController::class, 'cambiarContrasenaForm'])
    ->middleware('auth')
    ->name('cambiar-contrasena');
Route::post('/cambiar-contrasena', [AutenticacionController::class, 'cambiarContrasena'])
    ->middleware('auth');
Route::post('/logout', [AutenticacionController::class, 'cerrarSesion'])->name('logout');
Route::get('/habitaciones', [PublicPageController::class, 'habitaciones'])->name('habitaciones');
Route::get('/habitaciones/{slug}', [PublicPageController::class, 'habitacionDetalle'])->name('habitacion-detalle');
Route::get('/pago', [PublicPageController::class, 'pago'])->name('pago');
Route::get('/mis-reservas', [PublicPageController::class, 'misReservas'])->name('mis-reservas');
Route::post('/reservas', [ReservaController::class, 'crear'])->name('reservas.crear');
Route::post('/reservas/{id}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');
Route::get('/favoritos', [PublicPageController::class, 'favoritos'])->name('favoritos');
Route::get('/restaurante', [PublicPageController::class, 'restaurante'])->name('restaurante');
Route::get('/espacios', [PublicPageController::class, 'espacios'])->name('espacios');
Route::get('/espacios/{id}', [PublicPageController::class, 'espacioDetalle'])->name('espacio-detalle');

Route::middleware(['auth'])->get('/admin/restaurante/pedidos/{pedido}/comanda', [ComandaController::class, 'imprimir'])->name('restaurante.comanda');

Route::middleware(['auth'])->prefix('admin/compras/reportes')->group(function () {
    Route::get('/solicitud/{solicitud}', [ReporteCompraController::class, 'imprimirSolicitud'])
        ->middleware('can:Compras:ImprimirSolicitud')
        ->name('reporte.solicitud');
    Route::get('/orden-compra/{orden}', [ReporteCompraController::class, 'imprimirOrdenCompra'])
        ->middleware('can:Compras:ImprimirOrdenCompra')
        ->name('reporte.orden-compra');
    Route::get('/recepcion/{recepcion}', [ReporteCompraController::class, 'imprimirRecepcion'])
        ->middleware('can:Compras:ImprimirRecepcion')
        ->name('reporte.recepcion');
    Route::get('/cotizacion/{cotizacion}', [ReporteCompraController::class, 'imprimirCotizacion'])
        ->middleware('can:Compras:ImprimirCotizacion')
        ->name('reporte.cotizacion');
    Route::get('/comparativa/{solicitud}', [ReporteCompraController::class, 'imprimirComparativa'])
        ->middleware('can:Compras:ImprimirComparativa')
        ->name('reporte.comparativa');
    Route::get('/devolucion/{devolucion}', [ReporteCompraController::class, 'devolucion'])
        ->middleware('can:Compras:ImprimirDevolucion')
        ->name('reporte.devolucion');
    Route::get('/resumen-departamentos', [ReporteCompraController::class, 'imprimirResumenDepartamentos'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.departamentos');

    Route::get('/solicitudes-estado', [ReporteCompraController::class, 'solicitudesEstado'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.solicitudes-estado');

    Route::get('/seguimiento-oc', [ReporteCompraController::class, 'seguimientoOc'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.seguimiento-oc');

    Route::get('/recepciones-proveedor', [ReporteCompraController::class, 'recepcionesPorProveedor'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.recepciones-proveedor');

    Route::get('/analisis-precio', [ReporteCompraController::class, 'analisisPrecio'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.analisis-precio');

    Route::get('/valorizacion', [ReporteCompraController::class, 'valorizacion'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.valorizacion');

    Route::get('/rotacion', [ReporteCompraController::class, 'rotacion'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.rotacion');

    Route::get('/tiempos-entrega', [ReporteCompraController::class, 'tiemposEntrega'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.tiempos-entrega');

    Route::get('/ranking-proveedores', [ReporteCompraController::class, 'rankingProveedores'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.ranking-proveedores');

    Route::get('/devoluciones', [ReporteCompraController::class, 'devoluciones'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.devoluciones');

    Route::get('/trazabilidad-completa/{solicitud}', [ReporteCompraController::class, 'trazabilidadCompleta'])
        ->middleware('can:Compras:ImprimirReportesCompras')
        ->name('reporte.compras.trazabilidad-completa');
});

Route::middleware(['auth'])->prefix('admin/inventario/reportes')->group(function () {
    // HTB-INV-001: Stock por Producto
    Route::get('/stock-producto/pdf', [ReporteInventarioController::class, 'stockPorProductoPdf'])
        ->middleware('can:Inventario:ReporteStock')
        ->name('reporte.inventario.stock-producto.pdf');
    Route::get('/stock-producto/excel', [ReporteInventarioController::class, 'stockPorProductoExcel'])
        ->middleware('can:Inventario:ReporteStock')
        ->name('reporte.inventario.stock-producto.excel');

    // HTB-INV-003: Movimientos
    Route::get('/movimientos/pdf', [ReporteInventarioController::class, 'movimientosPdf'])
        ->middleware('can:Inventario:ReporteMovimientos')
        ->name('reporte.inventario.movimientos.pdf');
    Route::get('/movimientos/excel', [ReporteInventarioController::class, 'movimientosExcel'])
        ->middleware('can:Inventario:ReporteMovimientos')
        ->name('reporte.inventario.movimientos.excel');

    // HTB-INV-004: Cuarentena
    Route::get('/cuarentena/pdf', [ReporteInventarioController::class, 'cuarentenaPdf'])
        ->middleware('can:Inventario:ReporteCuarentena')
        ->name('reporte.inventario.cuarentena.pdf');
    Route::get('/cuarentena/excel', [ReporteInventarioController::class, 'cuarentenaExcel'])
        ->middleware('can:Inventario:ReporteCuarentena')
        ->name('reporte.inventario.cuarentena.excel');

    // HTB-INV-005: Próximos a Vencer
    Route::get('/proximos-vencer/pdf', [ReporteInventarioController::class, 'proximosVencerPdf'])
        ->middleware('can:Inventario:ReporteProximosVencer')
        ->name('reporte.inventario.proximos-vencer.pdf');
    Route::get('/proximos-vencer/excel', [ReporteInventarioController::class, 'proximosVencerExcel'])
        ->middleware('can:Inventario:ReporteProximosVencer')
        ->name('reporte.inventario.proximos-vencer.excel');

    // HTB-INV-012: Lotes Vencidos
    Route::get('/vencidos/pdf', [ReporteInventarioController::class, 'vencidosPdf'])
        ->middleware('can:Inventario:ReporteVencidos')
        ->name('reporte.inventario.vencidos.pdf');
    Route::get('/vencidos/excel', [ReporteInventarioController::class, 'vencidosExcel'])
        ->middleware('can:Inventario:ReporteVencidos')
        ->name('reporte.inventario.vencidos.excel');

    // HTB-INV-006: Mermas
    Route::get('/mermas/pdf', [ReporteInventarioController::class, 'mermasPdf'])
        ->middleware('can:Inventario:ReporteMermas')
        ->name('reporte.inventario.mermas.pdf');
    Route::get('/mermas/excel', [ReporteInventarioController::class, 'mermasExcel'])
        ->middleware('can:Inventario:ReporteMermas')
        ->name('reporte.inventario.mermas.excel');

    // HTB-INV-007: Valorización
    Route::get('/valorizacion/pdf', [ReporteInventarioController::class, 'valorizacionPdf'])
        ->middleware('can:Inventario:ReporteValorizacion')
        ->name('reporte.inventario.valorizacion.pdf');
    Route::get('/valorizacion/excel', [ReporteInventarioController::class, 'valorizacionExcel'])
        ->middleware('can:Inventario:ReporteValorizacion')
        ->name('reporte.inventario.valorizacion.excel');

    // HTB-INV-008: Rotación (solo Excel)
    Route::get('/rotacion/excel', [ReporteInventarioController::class, 'rotacionExcel'])
        ->middleware('can:Inventario:ReporteRotacion')
        ->name('reporte.inventario.rotacion.excel');

    // HTB-INV-009: Mermas Totales (solo Excel)
    Route::get('/mermas-totales/excel', [ReporteInventarioController::class, 'mermasTotalesExcel'])
        ->middleware('can:Inventario:ReporteMermasTotales')
        ->name('reporte.inventario.mermas-totales.excel');

    // HTB-INV-011: Trazabilidad por Lote
    Route::get('/trazabilidad/{loteId}/pdf', [ReporteInventarioController::class, 'trazabilidadLotePdf'])
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
    Route::get('/inventario-general/pdf', [ReporteActivoController::class, 'inventarioGeneralPdf'])
        ->middleware('can:Activos:ReporteInventario')
        ->name('reporte.activos.inventario-general.pdf');
    Route::get('/inventario-general/excel', [ReporteActivoController::class, 'inventarioGeneralExcel'])
        ->middleware('can:Activos:ReporteInventario')
        ->name('reporte.activos.inventario-general.excel');
    Route::get('/ficha/{activo}/pdf', [ReporteActivoController::class, 'fichaActivoPdf'])
        ->middleware('can:Activos:ReporteFicha')
        ->name('reporte.activos.ficha.pdf');
    Route::get('/mantenimiento/{mantenimiento}/pdf', [ReporteActivoController::class, 'fichaMantenimientoPdf'])
        ->middleware('can:Activos:ReporteMantenimiento')
        ->name('reporte.activos.mantenimiento.pdf');
    Route::get('/etiquetas/pdf', [ReporteActivoController::class, 'etiquetasPdf'])
        ->middleware('can:Activos:ReporteEtiquetas')
        ->name('reporte.activos.etiquetas.pdf');

    // HTB-ACT-005: Activos por Ubicación
    Route::get('/por-ubicacion/pdf', [ReporteActivoController::class, 'porUbicacionPdf'])
        ->middleware('can:Activos:ReportePorUbicacion')
        ->name('reporte.activos.por-ubicacion.pdf');

    // HTB-ACT-006: Historial de Movimientos
    Route::get('/historial-movimientos/pdf', [ReporteActivoController::class, 'historialMovimientosPdf'])
        ->middleware('can:Activos:ReporteHistorial')
        ->name('reporte.activos.historial-movimientos.pdf');

    // HTB-ACT-007: Activos en Mantenimiento
    Route::get('/en-mantenimiento/pdf', [ReporteActivoController::class, 'enMantenimientoPdf'])
        ->middleware('can:Activos:ReporteMantenimientoActivos')
        ->name('reporte.activos.en-mantenimiento.pdf');

    // HTB-ACT-008: Garantías Próximas a Vencer
    Route::get('/garantias-proximas/pdf', [ReporteActivoController::class, 'garantiasProximasPdf'])
        ->middleware('can:Activos:ReporteGarantias')
        ->name('reporte.activos.garantias-proximas.pdf');

    // HTB-ACT-009: Activos Dados de Baja
    Route::get('/dados-de-baja/pdf', [ReporteActivoController::class, 'dadosDeBajaPdf'])
        ->middleware('can:Activos:ReporteBajas')
        ->name('reporte.activos.dados-de-baja.pdf');

    // HTB-ACT-010: Activos Extraviados
    Route::get('/extraviados/pdf', [ReporteActivoController::class, 'extraviadosPdf'])
        ->middleware('can:Activos:ReporteExtraviados')
        ->name('reporte.activos.extraviados.pdf');

    // HTB-ACT-011: Activos Sin Asignación
    Route::get('/sin-asignacion/pdf', [ReporteActivoController::class, 'sinAsignacionPdf'])
        ->middleware('can:Activos:ReporteSinAsignacion')
        ->name('reporte.activos.sin-asignacion.pdf');

    // HTB-ACT-012: Mantenimientos Vencidos
    Route::get('/mantenimientos-vencidos/pdf', [ReporteActivoController::class, 'mantenimientosVencidosPdf'])
        ->middleware('can:Activos:ReporteMantenimientosVencidos')
        ->name('reporte.activos.mantenimientos-vencidos.pdf');

    // HTB-ACT-013: Hoja de Habitación / Espacio
    Route::get('/hoja-habitacion/{tipo}/{id}/pdf', [ReporteActivoController::class, 'hojaHabitacionPdf'])
        ->middleware('can:Activos:ReporteHojaHabitacion')
        ->name('reporte.activos.hoja-habitacion.pdf');
});
