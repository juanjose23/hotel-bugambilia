<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticación (Prefijo /auth)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'iniciarSesion'])->middleware('throttle:auth')->name('login.post');
        Route::post('/iniciar-sesion', [AuthController::class, 'iniciarSesion'])->middleware('throttle:auth');
        Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');
        Route::post('/registro', [AuthController::class, 'registrar'])->middleware('throttle:auth')->name('registro.post');

        // Autenticación con Google
        Route::get('/google/redirect', [GoogleAuthController::class, 'redireccionar'])->name('auth.google.redirect');
        Route::get('/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'cerrarSesion'])->middleware(['auth', 'throttle:logout'])->name('logout');
        Route::get('/cambiar-contrasena', [AuthController::class, 'mostrarCambiarContrasena'])->name('cambiar-contrasena');
        Route::post('/cambiar-contrasena', [AuthController::class, 'cambiarContrasena']);
    });
});

// Redirecciones de conveniencia
Route::get('/login', fn () => redirect()->route('login'));
Route::get('/registro', fn () => redirect()->route('registro'));
