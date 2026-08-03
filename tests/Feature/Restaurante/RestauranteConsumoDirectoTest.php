<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Pedido;

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

test('un cliente walk-in ocupa una mesa libre y realiza un pedido directo', function (): void {
    $mesa = Espacio::query()->create([
        'nombre' => 'Mesa Terraza 04',
        'codigo' => 'M-TER-04',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'estado' => 1,
    ]);

    $pedido = Pedido::query()->create([
        'codigo' => 'PED-TEST-001',
        'mesa_id' => $mesa->id,
        'estado' => EstadoPedido::ABIERTO,
        'subtotal' => 0.00,
        'abierto_en' => now(),
    ]);

    expect($pedido->exists)->toBeTrue()
        ->and($pedido->mesa_id)->toBe($mesa->id)
        ->and($pedido->estado)->toBe(EstadoPedido::ABIERTO);
});
