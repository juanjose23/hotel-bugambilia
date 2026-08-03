<?php

declare(strict_types=1);

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Cocina\MarcarItemServido;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;

it('marca un item listo como servido', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-S1',
        'nombre' => 'Mesa S1',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-S1',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::EN_PREPARACION,
        'total' => 10,
    ]));
    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => null,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
        'estado' => EstadoItemPedido::LISTO,
    ]);

    $interactor = app(MarcarItemServido::class);
    $result = $interactor->ejecutar($item->id);
    $item->refresh();

    expect($result)->not->toBeNull()
        ->and($result?->estado)->toBe(EstadoItemPedido::SERVIDO)
        ->and($item->estado)->toBe(EstadoItemPedido::SERVIDO);
});

it('transiciona el pedido a SERVIDO cuando todos los items estan servidos', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-S2',
        'nombre' => 'Mesa S2',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-S2',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::LISTO,
        'total' => 20,
    ]));
    $item1 = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => null,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
        'estado' => EstadoItemPedido::LISTO,
    ]);
    $item2 = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => null,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
        'estado' => EstadoItemPedido::LISTO,
    ]);

    $interactor = app(MarcarItemServido::class);
    $interactor->ejecutar($item1->id);
    $pedido->refresh();

    expect($pedido->estado)->toBe(EstadoPedido::LISTO);

    $interactor->ejecutar($item2->id);
    $pedido->refresh();

    expect($pedido->estado)->toBe(EstadoPedido::SERVIDO);
});

it('es idempotente al marcar como servido', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-S3',
        'nombre' => 'Mesa S3',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-S3',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::LISTO,
        'total' => 10,
    ]));
    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => null,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
        'estado' => EstadoItemPedido::LISTO,
    ]);

    $interactor = app(MarcarItemServido::class);
    $interactor->ejecutar($item->id);
    $result = $interactor->ejecutar($item->id);

    expect($result)->not->toBeNull()
        ->and($result?->estado)->toBe(EstadoItemPedido::SERVIDO);
});

it('rechaza marcar servido un item que no esta listo', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-S4',
        'nombre' => 'Mesa S4',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-S4',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::EN_PREPARACION,
        'total' => 10,
    ]));
    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => null,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
        'estado' => EstadoItemPedido::EN_PREPARACION,
    ]);

    app(MarcarItemServido::class)->ejecutar($item->id);
})->throws(DomainException::class, 'debe estar listo');
