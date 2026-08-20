<?php

declare(strict_types=1);

use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Interactors\Limpieza\Carrito\AsignarCarritoAEjecucion;
use App\Interactors\Limpieza\Carrito\LiberarCarritoDeEjecucion;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

it('asigna un carrito a una ejecución mediante el caso de uso', function (): void {
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito 1',
        'tipo' => 'carrito_limpieza',
        'estado' => 1,
    ]);
    $ejecucion = LimpiezaEjecucion::factory()->pendiente()->create();

    app(AsignarCarritoAEjecucion::class)->execute($ejecucion->id, $carrito->id);

    expect($ejecucion->refresh()->carrito_id)->toBe($carrito->id);
});

it('no asigna un carrito bloqueado por otra ejecución activa', function (): void {
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito Bloqueado',
        'tipo' => 'carrito_limpieza',
        'estado' => 1,
    ]);

    LimpiezaEjecucion::factory()->pendiente()->create([
        'carrito_id' => $carrito->id,
        'fecha' => now()->toDateString(),
    ]);

    $ejecucion = LimpiezaEjecucion::factory()->pendiente()->create([
        'fecha' => now()->toDateString(),
    ]);

    expect(fn () => app(AsignarCarritoAEjecucion::class)->execute($ejecucion->id, $carrito->id))
        ->toThrow(OperacionLimpiezaNoPermitida::class);

    expect($ejecucion->refresh()->carrito_id)->toBeNull();
});

it('al liberar un carrito reinicia una ejecución en progreso', function (): void {
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito 2',
        'tipo' => 'carrito_limpieza',
        'estado' => 1,
    ]);
    $ejecucion = LimpiezaEjecucion::factory()->enProgreso()->create([
        'carrito_id' => $carrito->id,
        'hora_inicio' => '08:00:00',
    ]);

    app(LiberarCarritoDeEjecucion::class)->execute($ejecucion);

    $ejecucion->refresh();
    expect($ejecucion->carrito_id)->toBeNull()
        ->and($ejecucion->estado)->toBe(EstadoLimpieza::Pendiente)
        ->and($ejecucion->hora_inicio)->toBeNull();
});

it('al liberar una tarea pendiente conserva su estado', function (): void {
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito 3',
        'tipo' => 'carrito_limpieza',
        'estado' => 1,
    ]);
    $ejecucion = LimpiezaEjecucion::factory()->pendiente()->create([
        'carrito_id' => $carrito->id,
    ]);

    app(LiberarCarritoDeEjecucion::class)->execute($ejecucion);

    $ejecucion->refresh();
    expect($ejecucion->carrito_id)->toBeNull()
        ->and($ejecucion->estado)->toBe(EstadoLimpieza::Pendiente);
});
