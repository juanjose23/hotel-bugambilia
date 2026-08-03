<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;

beforeEach(function (): void {
    if (Moneda::query()->where('codigo', 'NIO')->doesntExist()) {
        Moneda::query()->create([
            'codigo' => 'NIO',
            'nombre' => 'Córdoba Nicaragüense',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]);
    }
});

test('avanza el estado de preparación de platillos en cocina KDS', function (): void {
    $mesa = Espacio::query()->create([
        'nombre' => 'Mesa KDS 01',
        'codigo' => 'M-KDS-01',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);

    $plato = Plato::query()->create([
        'nombre' => 'Sopa de Mariscos Special',
        'codigo' => 'PLT-SOPA-MAR',
        'precio_base' => 380.00,
        'estado' => EstadoGeneral::Activo,
    ]);

    $pedido = Pedido::query()->create([
        'codigo' => 'PED-KDS-100',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::ABIERTO,
        'subtotal' => 380.00,
        'abierto_en' => now(),
    ]);

    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => $plato->id,
        'cantidad' => 1,
        'precio_unitario' => 380.00,
        'subtotal' => 380.00,
        'estado' => EstadoItemPedido::PENDIENTE,
    ]);

    expect($item->estado)->toBe(EstadoItemPedido::PENDIENTE);

    // Marcar en preparación
    $item->update(['estado' => EstadoItemPedido::EN_PREPARACION]);
    $item->refresh();
    expect($item->estado)->toBe(EstadoItemPedido::EN_PREPARACION);

    // Marcar listo
    $item->update(['estado' => EstadoItemPedido::LISTO]);
    $item->refresh();
    expect($item->estado)->toBe(EstadoItemPedido::LISTO);

    // Marcar servido
    $item->update(['estado' => EstadoItemPedido::SERVIDO]);
    $item->refresh();
    expect($item->estado)->toBe(EstadoItemPedido::SERVIDO);
});
