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
    Route::get('/inventario-general/pdf', [ReporteActivoController::class, 'inventarioGeneralPdf'])->name('inventario-general.pdf');
    Route::get('/inventario-general/excel', [ReporteActivoController::class, 'inventarioGeneralExcel'])->name('inventario-general.excel');
    Route::get('/ficha/{activo:codigo_inventario}/pdf', [ReporteActivoController::class, 'fichaActivoPdf'])->name('ficha.pdf');
    Route::get('/mantenimiento/{mantenimiento}/pdf', [ReporteActivoController::class, 'fichaMantenimientoPdf'])->name('mantenimiento.pdf');
    Route::get('/etiquetas/pdf', [ReporteActivoController::class, 'etiquetasPdf'])->name('etiquetas.pdf');
    Route::get('/por-ubicacion/pdf', [ReporteActivoController::class, 'porUbicacionPdf'])->name('por-ubicacion.pdf');
    Route::get('/historial-movimientos/pdf', [ReporteActivoController::class, 'historialMovimientosPdf'])->name('historial-movimientos.pdf');
    Route::get('/en-mantenimiento/pdf', [ReporteActivoController::class, 'enMantenimientoPdf'])->name('en-mantenimiento.pdf');
    Route::get('/garantias-proximas/pdf', [ReporteActivoController::class, 'garantiasProximasPdf'])->name('garantias-proximas.pdf');
    Route::get('/dados-de-baja/pdf', [ReporteActivoController::class, 'dadosDeBajaPdf'])->name('dados-de-baja.pdf');
    Route::get('/extraviados/pdf', [ReporteActivoController::class, 'extraviadosPdf'])->name('extraviados.pdf');
    Route::get('/sin-asignacion/pdf', [ReporteActivoController::class, 'sinAsignacionPdf'])->name('sin-asignacion.pdf');
    Route::get('/mantenimientos-vencidos/pdf', [ReporteActivoController::class, 'mantenimientosVencidosPdf'])->name('mantenimientos-vencidos.pdf');
    Route::get('/hoja-habitacion/{tipo}/{id}/pdf', [ReporteActivoController::class, 'hojaHabitacionPdf'])->name('hoja-habitacion.pdf');
});
