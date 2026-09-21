<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel de Administración y Reportes (Agrupados por Módulos)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reportes/en-proceso/{codigo?}', function (?string $codigo = null) {
        return response()->view('reports.layout.segundo-plano', [
            'codigo' => $codigo,
            'mensaje' => 'El reporte contiene un volumen muy alto de datos y se está procesando en segundo plano para garantizar la estabilidad del sistema. Recibirás una notificación en la campana del panel cuando esté listo para descargar.',
        ], 200);
    })->name('reportes.en-proceso');

    require __DIR__.'/admin/financiero.php';
    require __DIR__.'/admin/reservas.php';
    require __DIR__.'/admin/restaurante.php';
    require __DIR__.'/admin/compras.php';
    require __DIR__.'/admin/inventario.php';
    require __DIR__.'/admin/limpieza.php';
    require __DIR__.'/admin/servicios.php';
    require __DIR__.'/admin/activos.php';
});
