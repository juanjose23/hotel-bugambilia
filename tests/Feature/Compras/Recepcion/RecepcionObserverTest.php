<?php

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\UseCases\Compras\Recepciones\Mutations\GestionarTransicionRecepcion;

it('no marca OC como Recibida al crear recepcion en Pendiente', function () {
    $orden = OrdenCompra::factory()->create(['estado' => EstadoOrdenCompra::Emitida]);
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'estado' => EstadoRecepcion::Pendiente,
    ]);

    expect($orden->fresh()->estado)->not->toBe(EstadoOrdenCompra::Recibida);
    expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Emitida);
});

it('marca OC como Recibida cuando recepcion pasa a Completa', function () {
    $orden = OrdenCompra::factory()->create(['estado' => EstadoOrdenCompra::Emitida]);
    $ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $orden->id,
        'cantidad' => 10,
    ]);
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'estado' => EstadoRecepcion::Pendiente,
    ]);
    RecepcionItem::factory()->create([
        'recepcion_id' => $recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'cantidad_recibida' => 10,
        'cantidad_rechazada' => 0,
    ]);

    app(GestionarTransicionRecepcion::class)->execute($recepcion, EstadoRecepcion::Completa);

    expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Recibida);
});

it('regresa OC a Emitida cuando recepcion pasa a Rechazada', function () {
    $orden = OrdenCompra::factory()->create(['estado' => EstadoOrdenCompra::EnTransito]);
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'estado' => EstadoRecepcion::Pendiente,
    ]);

    app(GestionarTransicionRecepcion::class)->execute($recepcion, EstadoRecepcion::Rechazada);

    expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Emitida);
});

it('no cambia estado de OC cuando recepcion pasa a Parcial', function () {
    $orden = OrdenCompra::factory()->create(['estado' => EstadoOrdenCompra::Emitida]);
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'estado' => EstadoRecepcion::Pendiente,
    ]);

    app(GestionarTransicionRecepcion::class)->execute($recepcion, EstadoRecepcion::Parcial);

    expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Emitida);
});
