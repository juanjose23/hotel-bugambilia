<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaCreada;
use App\Interactors\Reservas\Gestion\CrearReserva;
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

    $datos = [
        'nombre_cliente' => 'Juan Pérez',
        'email_cliente' => 'juan@example.com',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(8)->toDateString(),
        'adultos' => 2,
    ];

    /** @var CrearReserva $interactor */
    $interactor = app(CrearReserva::class);
    $reserva = $interactor->ejecutar($datos);

    expect($reserva->estado)->toBe(EstadoReserva::PENDIENTE);
    expect($reserva->detalles)->toHaveCount(1);
    expect($reserva->detalles->first()->estado)->toBe(EstadoReservaDetalle::CONFIRMADO);

    // Habitación física debe permanecer sin cambio
    expect($habitacion->fresh()->estado)->toBe(EstadoEspacio::Disponible);

    Event::assertDispatched(ReservaCreada::class);
});
