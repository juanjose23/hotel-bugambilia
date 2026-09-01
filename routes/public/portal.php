<?php

declare(strict_types=1);

use App\Http\Controllers\Clientes\PortalAcompanantesController;
use App\Http\Controllers\Clientes\PortalDashboardController;
use App\Http\Controllers\Clientes\PortalPerfilController;
use App\Http\Controllers\Clientes\PortalReservasController;
use App\Http\Controllers\Clientes\PortalServiciosEstanciaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal del Huésped / Portal del Cliente
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('portal')->name('portal.')->group(function (): void {
    Route::get('/', PortalDashboardController::class)->name('dashboard');
    Route::get('/reservas', [PortalReservasController::class, 'index'])->name('reservas.index');
    Route::get('/reservas/{id}', [PortalReservasController::class, 'show'])->name('reservas.show');

    // Servicios adicionales de la estancia
    Route::get('/reservas/{id}/servicios', [PortalServiciosEstanciaController::class, 'create'])->name('reservas.servicios.create');
    Route::post('/reservas/{id}/servicios', [PortalServiciosEstanciaController::class, 'store'])->name('reservas.servicios.store');

    // Gestión de acompañantes para check-in
    Route::get('/reservas/{id}/acompanantes', [PortalAcompanantesController::class, 'create'])->name('reservas.acompanantes.create');
    Route::post('/reservas/{id}/acompanantes', [PortalAcompanantesController::class, 'store'])->name('reservas.acompanantes.store');

    // Perfil y preferencias
    Route::get('/perfil', [PortalPerfilController::class, 'edit'])->name('perfil');
    Route::post('/perfil', [PortalPerfilController::class, 'update'])->name('perfil.update');
});
