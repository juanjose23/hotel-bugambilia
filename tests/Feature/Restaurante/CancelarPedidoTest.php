<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\Pedidos\CancelarPedido;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;

it('cancela un pedido en preparacion y anula sus items', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-C1',
        'nombre' => 'Mesa C1',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => EstadoEspacio::Ocupado,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-C1',
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

    $interactor = app(CancelarPedido::class);
    $result = $interactor->ejecutar($pedido);
    $item->refresh();
    $mesa->refresh();

    expect($result->estado)->toBe(EstadoPedido::CANCELADO)
        ->and($result->cerrado_en)->not->toBeNull()
        ->and($item->estado)->toBe(EstadoItemPedido::ANULADO)
        ->and($mesa->estado)->toBe(EstadoEspacio::Sucio);
});

it('rechaza cancelar un pedido pagado', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-C2',
        'nombre' => 'Mesa C2',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-C2',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::PAGADO,
        'total' => 10,
    ]));

    app(CancelarPedido::class)->ejecutar($pedido);
})->throws(DomainException::class, 'no puede ser cancelado');

it('rechaza cancelar un pedido ya cancelado', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-C3',
        'nombre' => 'Mesa C3',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-C3',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::CANCELADO,
        'total' => 0,
    ]));

    app(CancelarPedido::class)->ejecutar($pedido);
})->throws(DomainException::class, 'no puede ser cancelado');

it('no toca items servidos al cancelar', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-C4',
        'nombre' => 'Mesa C4',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => EstadoEspacio::Ocupado,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-C4',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::EN_PREPARACION,
        'total' => 20,
    ]));
    $itemServido = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => null,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
        'estado' => EstadoItemPedido::SERVIDO,
    ]);
    $itemActivo = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => null,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
        'estado' => EstadoItemPedido::EN_PREPARACION,
    ]);

    app(CancelarPedido::class)->ejecutar($pedido);
    $itemServido->refresh();
    $itemActivo->refresh();

    expect($itemServido->estado)->toBe(EstadoItemPedido::SERVIDO)
        ->and($itemActivo->estado)->toBe(EstadoItemPedido::ANULADO);
});
