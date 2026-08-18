<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaCancelada;
use App\Interactors\Reservas\Habitaciones\CancelarReservaHabitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Support\Facades\Event;

test('cancela reserva de habitacion y despacha evento ReservaCancelada', function (): void {
    Event::fake([ReservaCancelada::class]);

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Habitacion Cancelar Test',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CAN-HAB-'.Str::random(4),
        'nombre_cliente' => 'Cliente Cancelar',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(8)->toDateString(),
        'estado' => EstadoReserva::CONFIRMADA,
        'subtotal' => 300.00,
        'total' => 300.00,
    ]);

    ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'estado' => 2,
        'fecha_inicio' => now()->addDays(5),
        'fecha_fin' => now()->addDays(8),
        'precio_unitario' => 100.00,
        'subtotal' => 300.00,
    ]);

    $data = new CancelarReservaHabitacionData(
        reservaId: $reserva->id,
        motivo: 'Cliente cancelo',
        usuarioId: null,
        montoPenalizacion: 50.00,
    );

    $resultado = app(CancelarReservaHabitacion::class)->ejecutar($data);

    expect($resultado->estado)->toBe(EstadoReserva::CANCELADA);
    expect((float) $resultado->total)->toBe(50.00);
    Event::assertDispatched(ReservaCancelada::class);
});

test('lanza excepcion al cancelar reserva ya cancelada', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CAN-REPETIDA-'.Str::random(4),
        'nombre_cliente' => 'Cliente Ya Cancelado',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(8)->toDateString(),
        'estado' => EstadoReserva::CANCELADA,
    ]);

    $data = new CancelarReservaHabitacionData(
        reservaId: $reserva->id,
        motivo: 'Intento 2',
    );

    app(CancelarReservaHabitacion::class)->ejecutar($data);
})->throws(DomainException::class, 'no se puede cancelar desde su estado actual');
