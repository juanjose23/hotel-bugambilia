<?php

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\UseCases\Compras\OrdenesCompra\Queries\VerificarEstadoOrdenCompra;

it('marca OC como Recibida cuando todas las cantidades están completas', function () {
    $orden = OrdenCompra::factory()->create(['estado' => EstadoOrdenCompra::Emitida]);
    $ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $orden->id,
        'cantidad' => 10,
    ]);
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'estado' => EstadoRecepcion::Completa,
    ]);
    RecepcionItem::factory()->create([
        'recepcion_id' => $recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'cantidad_recibida' => 10,
        'cantidad_rechazada' => 0,
    ]);

    app(VerificarEstadoOrdenCompra::class)->execute($orden);

    expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Recibida);
});

it('no marca OC como Recibida si faltan cantidades por recibir', function () {
    $orden = OrdenCompra::factory()->create(['estado' => EstadoOrdenCompra::Emitida]);
    $ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $orden->id,
        'cantidad' => 10,
    ]);
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'estado' => EstadoRecepcion::Completa,
    ]);
    RecepcionItem::factory()->create([
        'recepcion_id' => $recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'cantidad_recibida' => 5,
        'cantidad_rechazada' => 0,
    ]);

    app(VerificarEstadoOrdenCompra::class)->execute($orden);

    expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Parcial);
});

it('marca OC como Recibida con múltiples recepciones parciales', function () {
    $orden = OrdenCompra::factory()->create(['estado' => EstadoOrdenCompra::Emitida]);
    $ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $orden->id,
        'cantidad' => 10,
    ]);

    $recepcion1 = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'estado' => EstadoRecepcion::Completa,
    ]);
    RecepcionItem::factory()->create([
        'recepcion_id' => $recepcion1->id,
        'orden_item_id' => $ordenItem->id,
        'cantidad_recibida' => 6,
        'cantidad_rechazada' => 0,
    ]);

    $recepcion2 = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
        'estado' => EstadoRecepcion::Completa,
    ]);
    RecepcionItem::factory()->create([
        'recepcion_id' => $recepcion2->id,
        'orden_item_id' => $ordenItem->id,
        'cantidad_recibida' => 4,
        'cantidad_rechazada' => 0,
    ]);

    app(VerificarEstadoOrdenCompra::class)->execute($orden);

    expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Recibida);
});

it('ignora recepciones en estados no-Completa para el total recibido', function () {
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

    app(VerificarEstadoOrdenCompra::class)->execute($orden);

    expect($orden->fresh()->estado)->toBe(EstadoOrdenCompra::Emitida);
});
