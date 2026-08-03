<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;

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

test('la mesa cambia de estado y permite trazabilidad de ocupación', function (): void {
    $mesa = Espacio::query()->create([
        'nombre' => 'Mesa Salón 02',
        'codigo' => 'M-SAL-02',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 2,
        'estado' => EstadoEspacio::Disponible,
    ]);

    expect($mesa->estado)->toBe(EstadoEspacio::Disponible);

    // Ocupar mesa
    $mesa->update(['estado' => EstadoEspacio::Mantenimiento]);
    $mesa->refresh();
    expect($mesa->estado)->toBe(EstadoEspacio::Mantenimiento);

    // Liberar mesa a estado por limpiar
    $mesa->update(['estado' => EstadoEspacio::Limpieza]);
    $mesa->refresh();
    expect($mesa->estado)->toBe(EstadoEspacio::Limpieza);
});
