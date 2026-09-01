<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel de Administración y Reportes (Agrupados por Módulos)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    require __DIR__.'/admin/financiero.php';
    require __DIR__.'/admin/reservas.php';
    require __DIR__.'/admin/restaurante.php';
    require __DIR__.'/admin/compras.php';
    require __DIR__.'/admin/inventario.php';
    require __DIR__.'/admin/limpieza.php';
    require __DIR__.'/admin/servicios.php';
    require __DIR__.'/admin/activos.php';
});
