<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\RecalcularEstadoReservaHabitacion;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;

test('recalcular estado devuelve CANCELADA si todos los detalles estan cancelados', function (): void {
    $recurso = RecursoReservable::query()->create([
        'tipo' => TipoRecursoReservable::HABITACION,
        'nombre' => 'Habitación 101',
        'control_disponibilidad' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-003',
        'nombre_cliente' => 'Cliente Test',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now(),
        'fecha_check_out' => now()->addDays(2),
        'estado' => EstadoReserva::PENDIENTE,
    ]);

    ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now(),
        'fecha_fin' => now()->addDays(2),
        'estado' => EstadoReservaDetalle::CANCELADO,
    ]);

    $service = new RecalcularEstadoReservaHabitacion;
    $nuevoEstado = $service->calcularNuevoEstado($reserva);

    expect($nuevoEstado)->toBe(EstadoReserva::CANCELADA);
});

test('recalcular estado devuelve CHECKED_IN si todos los detalles estan en uso', function (): void {
    $recurso = RecursoReservable::query()->create([
        'tipo' => TipoRecursoReservable::HABITACION,
        'nombre' => 'Habitación 102',
        'control_disponibilidad' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-004',
        'nombre_cliente' => 'Cliente Test 2',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now(),
        'fecha_check_out' => now()->addDays(2),
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now(),
        'fecha_fin' => now()->addDays(2),
        'estado' => EstadoReservaDetalle::EN_USO,
    ]);

    $service = new RecalcularEstadoReservaHabitacion;
    $nuevoEstado = $service->calcularNuevoEstado($reserva);

    expect($nuevoEstado)->toBe(EstadoReserva::CHECKED_IN);
});
