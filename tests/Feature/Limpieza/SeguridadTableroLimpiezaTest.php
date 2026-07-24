<?php

declare(strict_types=1);

use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Interactors\Limpieza\Ejecucion\CompletarEjecucionAsignada;
use App\Interactors\Limpieza\Ejecucion\ReclamarEIniciarLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

it('impide reclamar una tarea preasignada a otro colaborador', function (): void {
    $asignado = Colaborador::factory()->create();
    $intruso = Colaborador::factory()->create();
    $ejecucion = LimpiezaEjecucion::factory()->pendiente()->create([
        'colaborador_id' => $asignado->id,
    ]);

    expect(fn () => app(ReclamarEIniciarLimpieza::class)->execute(
        $ejecucion->id,
        $intruso->id,
        null,
    ))->toThrow(OperacionLimpiezaNoPermitida::class);

    $ejecucion->refresh();
    expect($ejecucion->estado)->toBe(EstadoLimpieza::Pendiente)
        ->and($ejecucion->colaborador_id)->toBe($asignado->id)
        ->and($ejecucion->hora_inicio)->toBeNull();
});

it('impide iniciar por segunda vez una ejecución ya reclamada', function (): void {
    $primero = Colaborador::factory()->create();
    $segundo = Colaborador::factory()->create();
    $ejecucion = LimpiezaEjecucion::factory()->enProgreso()->create([
        'colaborador_id' => $primero->id,
    ]);

    expect(fn () => app(ReclamarEIniciarLimpieza::class)->execute(
        $ejecucion->id,
        $segundo->id,
        null,
    ))->toThrow(OperacionLimpiezaNoPermitida::class);

    expect($ejecucion->refresh()->colaborador_id)->toBe($primero->id);
});

it('impide completar una ejecución asignada a otro colaborador', function (): void {
    $asignado = Colaborador::factory()->create();
    $intruso = Colaborador::factory()->create();
    $ejecucion = LimpiezaEjecucion::factory()->enProgreso()->create([
        'colaborador_id' => $asignado->id,
    ]);

    expect(fn () => app(CompletarEjecucionAsignada::class)->execute(
        $ejecucion->id,
        $intruso->id,
        ['Puerta cerrada' => true],
        '',
        [],
    ))->toThrow(OperacionLimpiezaNoPermitida::class);

    expect($ejecucion->refresh()->estado)->toBe(EstadoLimpieza::EnProgreso);
});

it('impide usar un carrito que no pertenece al turno de la ejecución', function (): void {
    $colaborador = Colaborador::factory()->create();
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito ajeno',
        'tipo' => 'carrito',
        'estado' => 1,
    ]);
    $ejecucion = LimpiezaEjecucion::factory()->pendiente()->create();

    expect(fn () => app(ReclamarEIniciarLimpieza::class)->execute(
        $ejecucion->id,
        $colaborador->id,
        $carrito->id,
    ))->toThrow(OperacionLimpiezaNoPermitida::class);

    expect($ejecucion->refresh()->estado)->toBe(EstadoLimpieza::Pendiente);
});

it('impide usar un carrito ocupado por otra limpieza activa', function (): void {
    $colaborador = Colaborador::factory()->create();
    $carrito = Ubicacion::query()->create([
        'nombre' => 'Carrito ocupado',
        'tipo' => 'carrito',
        'estado' => 1,
    ]);
    $ocupante = LimpiezaEjecucion::factory()->enProgreso()->create([
        'colaborador_id' => Colaborador::factory(),
        'carrito_id' => $carrito->id,
    ]);
    $ejecucion = LimpiezaEjecucion::factory()->pendiente()->create([
        'turno_id' => $ocupante->turno_id,
    ]);
    $ejecucion->turno->carritos()->attach($carrito->id);

    expect(fn () => app(ReclamarEIniciarLimpieza::class)->execute(
        $ejecucion->id,
        $colaborador->id,
        $carrito->id,
    ))->toThrow(OperacionLimpiezaNoPermitida::class);

    $ejecucion->refresh();
    expect($ejecucion->estado)->toBe(EstadoLimpieza::Pendiente)
        ->and($ejecucion->carrito_id)->toBeNull();
});
