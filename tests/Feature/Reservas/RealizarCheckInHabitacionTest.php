<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\RealizarCheckInData;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\CheckInRegistrado;
use App\Interactors\Reservas\Habitaciones\RealizarCheckInHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Support\Facades\Event;

test('realizar check-in por detalle crea estancia activa, cambia habitacion a ocupada y actualiza estado de reserva', function (): void {
    Event::fake();

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Habitación 101',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => 1,
    ]);
    $habitacion = Habitacion::factory()->create([
        'reservable_id' => $recurso->id,
        'estado' => EstadoEspacio::Disponible,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-001',
        'nombre_cliente' => 'Cliente Test',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now(),
        'fecha_check_out' => now()->addDays(2),
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    $detalle = ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now(),
        'fecha_fin' => now()->addDays(2),
        'estado' => EstadoReservaDetalle::CONFIRMADO,
    ]);

    $dto = new RealizarCheckInData(
        reservaDetalleId: $detalle->id,
        cantidadLlaves: 2,
        observaciones: 'Sin novedades',
    );

    /** @var RealizarCheckInHabitacion $interactor */
    $interactor = app(RealizarCheckInHabitacion::class);
    $estancia = $interactor->ejecutar($dto);

    expect($estancia->estado)->toBe(EstadoEstancia::ACTIVA);
    expect($estancia->habitacion_id)->toBe($habitacion->id);

    expect($detalle->fresh()->estado)->toBe(EstadoReservaDetalle::EN_USO);
    expect($habitacion->fresh()->estado)->toBe(EstadoEspacio::Ocupado);
    expect($reserva->fresh()->estado)->toBe(EstadoReserva::CHECKED_IN);

    Event::assertDispatched(CheckInRegistrado::class);
});
