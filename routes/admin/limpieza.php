<?php

declare(strict_types=1);

use App\Http\Controllers\Limpieza\ReporteLimpiezaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reportes de Limpieza & Lavandería (Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('limpieza/reportes')->name('limpieza.reportes.')->group(function () {
    Route::get('/operacion-hotelera/preview', [ReporteLimpiezaController::class, 'operacionHoteleraPreview'])
        ->name('operacion-hotelera.preview');
    Route::get('/operacion-hotelera/pdf', [ReporteLimpiezaController::class, 'operacionHoteleraPdf'])
        ->name('operacion-hotelera.pdf');
    Route::get('/tiempo-promedio/preview', [ReporteLimpiezaController::class, 'tiempoPromedioPreview'])
        ->name('tiempo-promedio.preview');
    Route::get('/tiempo-promedio/pdf', [ReporteLimpiezaController::class, 'tiempoPromedioPdf'])
        ->name('tiempo-promedio.pdf');
    Route::get('/pendientes-bloqueadas/preview', [ReporteLimpiezaController::class, 'pendientesBloqueadasPreview'])
        ->name('pendientes-bloqueadas.preview');
    Route::get('/pendientes-bloqueadas/pdf', [ReporteLimpiezaController::class, 'pendientesBloqueadasPdf'])
        ->name('pendientes-bloqueadas.pdf');
    Route::get('/amenities-habitacion/preview', [ReporteLimpiezaController::class, 'amenitiesHabitacionPreview'])
        ->name('amenities-habitacion.preview');
    Route::get('/amenities-habitacion/pdf', [ReporteLimpiezaController::class, 'amenitiesHabitacionPdf'])
        ->name('amenities-habitacion.pdf');
    Route::get('/productividad/preview', [ReporteLimpiezaController::class, 'productividadPreview'])
        ->name('productividad.preview');
    Route::get('/productividad/pdf', [ReporteLimpiezaController::class, 'productividadPdf'])
        ->name('productividad.pdf');
});
