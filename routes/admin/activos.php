<?php

declare(strict_types=1);

use App\Http\Controllers\Activos\ReporteActivoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reportes de Activos Fijos (Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('activos/reportes')->name('activos.reportes.')->group(function () {
    Route::get('/inventario-general/pdf', [ReporteActivoController::class, 'inventarioGeneralPdf'])->middleware('can:Activos:ReporteInventario')->name('inventario-general.pdf');
    Route::get('/inventario-general/excel', [ReporteActivoController::class, 'inventarioGeneralExcel'])->middleware('can:Activos:RouteMiddleware')->name('inventario-general.excel');
    Route::get('/ficha/{activo}/pdf', [ReporteActivoController::class, 'fichaActivoPdf'])->middleware('can:Activos:ReporteFicha')->name('ficha.pdf');
    Route::get('/mantenimiento/{mantenimiento}/pdf', [ReporteActivoController::class, 'fichaMantenimientoPdf'])->middleware('can:Activos:ReporteMantenimiento')->name('mantenimiento.pdf');
    Route::get('/etiquetas/pdf', [ReporteActivoController::class, 'etiquetasPdf'])->middleware('can:Activos:ReporteEtiquetas')->name('etiquetas.pdf');
    Route::get('/por-ubicacion/pdf', [ReporteActivoController::class, 'porUbicacionPdf'])->middleware('can:Activos:ReportePorUbicacion')->name('por-ubicacion.pdf');
    Route::get('/historial-movimientos/pdf', [ReporteActivoController::class, 'historialMovimientosPdf'])->middleware('can:Activos:ReporteHistorial')->name('historial-movimientos.pdf');
    Route::get('/en-mantenimiento/pdf', [ReporteActivoController::class, 'enMantenimientoPdf'])->middleware('can:Activos:ReporteMantenimientoActivos')->name('en-mantenimiento.pdf');
    Route::get('/garantias-proximas/pdf', [ReporteActivoController::class, 'garantiasProximasPdf'])->middleware('can:Activos:ReporteGarantias')->name('garantias-proximas.pdf');
    Route::get('/dados-de-baja/pdf', [ReporteActivoController::class, 'dadosDeBajaPdf'])->middleware('can:Activos:ReporteBajas')->name('dados-de-baja.pdf');
    Route::get('/extraviados/pdf', [ReporteActivoController::class, 'extraviadosPdf'])->middleware('can:Activos:ReporteExtraviados')->name('extraviados.pdf');
    Route::get('/sin-asignacion/pdf', [ReporteActivoController::class, 'sinAsignacionPdf'])->middleware('can:Activos:ReporteSinAsignacion')->name('sin-asignacion.pdf');
    Route::get('/mantenimientos-vencidos/pdf', [ReporteActivoController::class, 'mantenimientosVencidosPdf'])->middleware('can:Activos:ReporteMantenimientosVencidos')->name('mantenimientos-vencidos.pdf');
    Route::get('/hoja-habitacion/{tipo}/{id}/pdf', [ReporteActivoController::class, 'hojaHabitacionPdf'])->middleware('can:Activos:ReporteHojaHabitacion')->name('hoja-habitacion.pdf');
});
