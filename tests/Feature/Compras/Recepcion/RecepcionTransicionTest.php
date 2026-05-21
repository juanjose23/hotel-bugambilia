<?php

use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\RecepcionCompra;
use App\UseCases\Compras\Recepciones\Mutations\GestionarTransicionRecepcion;

use function Pest\Laravel\assertModelExists;

dataset('transicionesValidas', [
    [EstadoRecepcion::Pendiente, EstadoRecepcion::Completa],
    [EstadoRecepcion::Pendiente, EstadoRecepcion::Parcial],
    [EstadoRecepcion::Pendiente, EstadoRecepcion::Rechazada],
    [EstadoRecepcion::Parcial, EstadoRecepcion::Completa],
]);

dataset('transicionesInvalidas', [
    [EstadoRecepcion::Pendiente, EstadoRecepcion::Pendiente],
    [EstadoRecepcion::Pendiente, EstadoRecepcion::ConDiscrepancia],
    [EstadoRecepcion::Pendiente, EstadoRecepcion::EnCuarentena],

    [EstadoRecepcion::Parcial, EstadoRecepcion::Pendiente],
    [EstadoRecepcion::Parcial, EstadoRecepcion::Parcial],
    [EstadoRecepcion::Parcial, EstadoRecepcion::ConDiscrepancia],
    [EstadoRecepcion::Parcial, EstadoRecepcion::EnCuarentena],
    [EstadoRecepcion::Parcial, EstadoRecepcion::Rechazada],

    [EstadoRecepcion::Completa, EstadoRecepcion::Pendiente],
    [EstadoRecepcion::Completa, EstadoRecepcion::Parcial],
    [EstadoRecepcion::Completa, EstadoRecepcion::Rechazada],

    [EstadoRecepcion::Rechazada, EstadoRecepcion::Pendiente],
    [EstadoRecepcion::Rechazada, EstadoRecepcion::Completa],
    [EstadoRecepcion::Rechazada, EstadoRecepcion::Parcial],

    [EstadoRecepcion::ConDiscrepancia, EstadoRecepcion::Pendiente],
    [EstadoRecepcion::ConDiscrepancia, EstadoRecepcion::Completa],
    [EstadoRecepcion::ConDiscrepancia, EstadoRecepcion::Rechazada],
    [EstadoRecepcion::ConDiscrepancia, EstadoRecepcion::Parcial],
    [EstadoRecepcion::ConDiscrepancia, EstadoRecepcion::EnCuarentena],

    [EstadoRecepcion::EnCuarentena, EstadoRecepcion::Pendiente],
    [EstadoRecepcion::EnCuarentena, EstadoRecepcion::Completa],
    [EstadoRecepcion::EnCuarentena, EstadoRecepcion::Rechazada],
    [EstadoRecepcion::EnCuarentena, EstadoRecepcion::Parcial],
    [EstadoRecepcion::EnCuarentena, EstadoRecepcion::ConDiscrepancia],
    [EstadoRecepcion::EnCuarentena, EstadoRecepcion::EnCuarentena],
]);

it('permite transiciones válidas', function (EstadoRecepcion $origen, EstadoRecepcion $destino) {
    $recepcion = RecepcionCompra::factory()->create(['estado' => $origen]);

    $resultado = app(GestionarTransicionRecepcion::class)->execute($recepcion, $destino);

    expect($resultado->estado)->toBe($destino);
    assertModelExists($resultado);
})->with('transicionesValidas');

it('rechaza transiciones inválidas', function (EstadoRecepcion $origen, EstadoRecepcion $destino) {
    $recepcion = RecepcionCompra::factory()->create(['estado' => $origen]);

    expect(fn () => app(GestionarTransicionRecepcion::class)->execute($recepcion, $destino))
        ->toThrow(InvalidArgumentException::class);
})->with('transicionesInvalidas');

it('registra historial al cambiar estado', function () {
    $recepcion = RecepcionCompra::factory()->create(['estado' => EstadoRecepcion::Pendiente]);

    app(GestionarTransicionRecepcion::class)->execute($recepcion, EstadoRecepcion::Completa);

    expect($recepcion->fresh()->estado)->toBe(EstadoRecepcion::Completa);
});
