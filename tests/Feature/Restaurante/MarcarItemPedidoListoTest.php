<?php

declare(strict_types=1);

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Restaurante\MarcarItemPedidoListo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;

it('marca el item listo y resuelve el estado del pedido de forma idempotente', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-TEST-1',
        'nombre' => 'Mesa Test',
        'tipo' => 'mesa',
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);
    $pedido = Pedido::withoutEvents(fn (): Pedido => Pedido::query()->create([
        'codigo' => 'PED-TEST-1',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::ABIERTO,
        'total' => 10,
    ]));
    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => null,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
        'estado' => EstadoItemPedido::PENDIENTE,
    ]);

    $interactor = app(MarcarItemPedidoListo::class);
    $interactor->ejecutar($item->id);
    $interactor->ejecutar($item->id);

    expect($item->fresh()?->estado)->toBe(EstadoItemPedido::LISTO)
        ->and($pedido->fresh()?->estado)->toBe(EstadoPedido::SERVIDO);
});
