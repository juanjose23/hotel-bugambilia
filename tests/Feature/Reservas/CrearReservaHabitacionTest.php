<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\CrearReservaHabitacionData;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Events\Reservas\ReservaHabitacionCreada;
use App\Interactors\Reservas\Habitaciones\CrearReservaHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use Illuminate\Support\Facades\Event;

test('crear reserva de habitacion crea cabecera, detalles y dispara evento sin cambiar estado fisico de habitacion', function (): void {
    Event::fake();

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Habitación Deluxe 101',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => 1,
        'estado' => 1,
        'capacidad' => 4,
    ]);

    $habitacion = Habitacion::factory()->create([
        'reservable_id' => $recurso->id,
        'estado' => EstadoEspacio::Disponible,
    ]);

    $dto = new CrearReservaHabitacionData(
        nombreCliente: 'Juan Pérez',
        fechaCheckIn: now()->addDays(5),
        fechaCheckOut: now()->addDays(8),
        recursosReservablesIds: [$recurso->id],
        emailCliente: 'juan@example.com',
        adultos: 2,
    );

    /** @var CrearReservaHabitacion $interactor */
    $interactor = app(CrearReservaHabitacion::class);
    $reserva = $interactor->ejecutar($dto);

    expect($reserva->estado)->toBe(EstadoReserva::PENDIENTE);
    expect($reserva->detalles)->toHaveCount(1);
    expect($reserva->detalles->first()->estado)->toBe(EstadoReservaDetalle::PENDIENTE);

    // Habitación física debe permanecer sin cambio
    expect($habitacion->fresh()->estado)->toBe(EstadoEspacio::Disponible);

    Event::assertDispatched(ReservaHabitacionCreada::class);
});
