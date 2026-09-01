<?php

declare(strict_types=1);

use App\Http\Controllers\Servicios\ServicioReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reportes de Servicios (Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('servicios/reportes')->name('servicios.reportes.')->group(function () {
    Route::get('/historico-precios/pdf', [ServicioReportController::class, 'historicoPreciosPdf'])->middleware('can:Servicios:ReporteHistoricoPrecios')->name('historico-precios.pdf');
    Route::get('/historico-precios/excel', [ServicioReportController::class, 'historicoPreciosExcel'])->middleware('can:Servicios:ReporteHistoricoPrecios')->name('historico-precios.excel');
});
