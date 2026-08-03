<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\CrearReserva;
use App\Repository\Models\Espacios\Espacio;

test('valida y bloquea reservaciones con conflicto de fecha y hora en la misma mesa', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-DISP-01',
        'nombre' => 'Mesa 01',
        'tipo' => 'mesa',
        'capacidad_personas' => 6,
        'estado' => EstadoEspacio::Disponible,
    ]);

    $crearReserva = app(CrearReserva::class);
    $fechaFutura = now()->addDay()->toDateString();

    // Crear primera reserva para la mesa a las 13:00 mañana
    $crearReserva->ejecutar([
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'nombre_cliente' => 'Cliente A',
        'espacio_id' => $mesa->id,
        'fecha_check_in' => $fechaFutura,
        'hora_reserva' => '13:00',
        'adultos' => 2,
    ]);

    // Intentar crear segunda reserva para la misma mesa la misma fecha y hora debe fallar por conflicto
    expect(fn () => $crearReserva->ejecutar([
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'nombre_cliente' => 'Cliente B',
        'espacio_id' => $mesa->id,
        'fecha_check_in' => $fechaFutura,
        'hora_reserva' => '13:00',
        'adultos' => 4,
    ]))->toThrow(InvalidArgumentException::class, 'no se encuentra disponible');
});

test('permite reservaciones en la misma mesa si es en diferente hora o fecha', function (): void {
    $mesa = Espacio::query()->create([
        'codigo' => 'MESA-DISP-02',
        'nombre' => 'Mesa 02',
        'tipo' => 'mesa',
        'capacidad_personas' => 6,
        'estado' => EstadoEspacio::Disponible,
    ]);

    $crearReserva = app(CrearReserva::class);
    $fechaFutura = now()->addDay()->toDateString();

    $reserva1 = $crearReserva->ejecutar([
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'nombre_cliente' => 'Cliente Almuerzo',
        'espacio_id' => $mesa->id,
        'fecha_check_in' => $fechaFutura,
        'hora_reserva' => '12:30',
        'adultos' => 2,
    ]);

    $reserva2 = $crearReserva->ejecutar([
        'tipo_reserva' => TipoReserva::RESTAURANTE->value,
        'nombre_cliente' => 'Cliente Cena',
        'espacio_id' => $mesa->id,
        'fecha_check_in' => $fechaFutura,
        'hora_reserva' => '20:00',
        'adultos' => 4,
    ]);

    expect($reserva1->id)->not->toBeNull()
        ->and($reserva2->id)->not->toBeNull();
});
