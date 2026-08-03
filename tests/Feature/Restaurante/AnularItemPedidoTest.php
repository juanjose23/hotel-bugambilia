<?php

declare(strict_types=1);

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Cocina\AnularItemPedido;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;

it('anula un item en preparacion', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-A1',
        'nombre' => 'Mesa A1',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-A1',
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

    $interactor = app(AnularItemPedido::class);
    $result = $interactor->ejecutar($item->id);
    $item->refresh();

    expect($result)->not->toBeNull()
        ->and($result?->estado)->toBe(EstadoItemPedido::ANULADO)
        ->and($item->estado)->toBe(EstadoItemPedido::ANULADO);
});

it('anula un item listo', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-A2',
        'nombre' => 'Mesa A2',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-A2',
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

    $interactor = app(AnularItemPedido::class);
    $result = $interactor->ejecutar($item->id);

    expect($result)->not->toBeNull()
        ->and($result?->estado)->toBe(EstadoItemPedido::ANULADO);
});

it('rechaza anular un item servido', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-A3',
        'nombre' => 'Mesa A3',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-A3',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::SERVIDO,
        'total' => 10,
    ]));
    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => null,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
        'estado' => EstadoItemPedido::SERVIDO,
    ]);

    app(AnularItemPedido::class)->ejecutar($item->id);
})->throws(DomainException::class, 'No se puede anular un plato que ya fue servido');

it('es idempotente al anular', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-A4',
        'nombre' => 'Mesa A4',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-A4',
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

    $interactor = app(AnularItemPedido::class);
    $interactor->ejecutar($item->id);
    $result = $interactor->ejecutar($item->id);

    expect($result)->not->toBeNull()
        ->and($result?->estado)->toBe(EstadoItemPedido::ANULADO);
});
