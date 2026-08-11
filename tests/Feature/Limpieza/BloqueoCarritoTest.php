<?php

declare(strict_types=1);

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Queries\Limpieza\Carrito\ObtenerCarritosDisponibles;
use App\Repository\Queries\Limpieza\Carrito\ObtenerEstadisticasCarrito;
use Carbon\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2026-08-04 09:00:00'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('no muestra un carrito reservado por otra tarea pendiente de hoy', function (): void {
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito Reservado',
        'tipo' => 'carrito',
        'estado' => 1,
    ]);

    LimpiezaEjecucion::factory()->pendiente()->create([
        'carrito_id' => $carrito->id,
        'fecha' => now()->toDateString(),
    ]);

    $otra = LimpiezaEjecucion::factory()->pendiente()->create();

    $disponibles = app(ObtenerCarritosDisponibles::class)->execute($otra->id);

    expect($disponibles)->not->toHaveKey((string) $carrito->id);
});

it('sí muestra un carrito reservado por una tarea pendiente de otra fecha', function (): void {
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito Libre',
        'tipo' => 'carrito',
        'estado' => 1,
    ]);

    LimpiezaEjecucion::factory()->pendiente()->create([
        'carrito_id' => $carrito->id,
        'fecha' => now()->subDay()->toDateString(),
    ]);

    $otra = LimpiezaEjecucion::factory()->pendiente()->create();

    $disponibles = app(ObtenerCarritosDisponibles::class)->execute($otra->id);

    expect($disponibles)->toHaveKey((string) $carrito->id);
});

it('reporta el carrito como bloqueado cuando tiene una tarea pendiente de hoy asignada', function (): void {
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito En Uso',
        'tipo' => 'carrito',
        'estado' => 1,
    ]);

    LimpiezaEjecucion::factory()->pendiente()->create([
        'carrito_id' => $carrito->id,
        'fecha' => now()->toDateString(),
    ]);

    $stats = app(ObtenerEstadisticasCarrito::class)->execute($carrito->id);

    expect($stats->bloqueado)->toBeTrue();
});

it('reporta el carrito como disponible cuando no tiene tareas activas asignadas', function (): void {
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito Disponible',
        'tipo' => 'carrito',
        'estado' => 1,
    ]);

    $stats = app(ObtenerEstadisticasCarrito::class)->execute($carrito->id);

    expect($stats->bloqueado)->toBeFalse();
});
