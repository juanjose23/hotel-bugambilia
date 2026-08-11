<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\RealizarCheckInData;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\OrigenReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Estancias\SolicitarServicioEstancia;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Interactors\Reservas\Habitaciones\RealizarCheckInHabitacion;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Shared\Precio;

test('registra un servicio agendado con horario en la estancia creando un ReservaDetalle vinculado', function (): void {
    $moneda = Moneda::query()->where('es_predeterminada', true)->first()
        ?? Moneda::factory()->create(['es_predeterminada' => true]);

    $recHab = RecursoReservable::query()->create([
        'nombre' => 'Hab 401',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
    ]);
    $hab = Habitacion::factory()->create(['reservable_id' => $recHab->id, 'estado' => EstadoEspacio::Disponible]);

    $reserva = app(CrearReserva::class)->ejecutar([
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'nombre_cliente' => 'Huésped Sauna',
        'fecha_check_in' => now()->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(2)->format('Y-m-d'),
        'habitacion_id' => $hab->id,
    ]);
    $reserva->update(['estado' => EstadoReserva::CONFIRMADA]);

    $detalleHab = $reserva->detalles()->firstOrFail();

    /** @var Estancia $estancia */
    $estancia = app(RealizarCheckInHabitacion::class)->ejecutar(
        new RealizarCheckInData(reservaDetalleId: $detalleHab->id)
    );

    $recSauna = RecursoReservable::query()->create([
        'nombre' => 'Sauna Privado',
        'tipo' => TipoRecursoReservable::SERVICIO,
        'control_disponibilidad' => ControlDisponibilidad::HORARIO,
        'duracion_minutos' => 60,
    ]);
    Precio::query()->create([
        'moneda_id' => $moneda->id,
        'priceable_type' => RecursoReservable::class,
        'priceable_id' => $recSauna->id,
        'tipo_precio' => 'base',
        'precio' => 50.0,
        'fecha_inicio' => today(),
        'estado' => EstadoGeneral::Activo,
    ]);

    /** @var SolicitarServicioEstancia $interactor */
    $interactor = app(SolicitarServicioEstancia::class);
    $resultado = $interactor->ejecutar([
        'estancia_id' => $estancia->id,
        'reservable_id' => $recSauna->id,
        'fecha_inicio' => now()->addHours(2)->format('Y-m-d H:i:s'),
        'origen' => OrigenReservaDetalle::HUESPED,
    ]);

    expect($resultado)->toBeInstanceOf(ReservaDetalle::class);
    expect($resultado->estancia_id)->toBe($estancia->id);
    expect($resultado->origen)->toBe(OrigenReservaDetalle::HUESPED);
});

test('registra un consumo sin bloqueo directo a la cuenta de la estancia', function (): void {
    $moneda = Moneda::query()->where('es_predeterminada', true)->first()
        ?? Moneda::factory()->create(['es_predeterminada' => true]);

    $recHab = RecursoReservable::query()->create([
        'nombre' => 'Hab 402',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
    ]);
    $hab = Habitacion::factory()->create(['reservable_id' => $recHab->id, 'estado' => EstadoEspacio::Disponible]);

    $reserva = app(CrearReserva::class)->ejecutar([
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'nombre_cliente' => 'Huésped Minibar',
        'fecha_check_in' => now()->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(2)->format('Y-m-d'),
        'habitacion_id' => $hab->id,
    ]);
    $reserva->update(['estado' => EstadoReserva::CONFIRMADA]);

    $detalleHab = $reserva->detalles()->firstOrFail();

    /** @var Estancia $estancia */
    $estancia = app(RealizarCheckInHabitacion::class)->ejecutar(
        new RealizarCheckInData(reservaDetalleId: $detalleHab->id)
    );

    $recMinibar = RecursoReservable::query()->create([
        'nombre' => 'Bebida Minibar',
        'tipo' => TipoRecursoReservable::SERVICIO,
        'control_disponibilidad' => ControlDisponibilidad::SIN_BLOQUEO,
    ]);
    Precio::query()->create([
        'moneda_id' => $moneda->id,
        'priceable_type' => RecursoReservable::class,
        'priceable_id' => $recMinibar->id,
        'tipo_precio' => 'base',
        'precio' => 15.0,
        'fecha_inicio' => today(),
        'estado' => EstadoGeneral::Activo,
    ]);

    /** @var SolicitarServicioEstancia $interactor */
    $interactor = app(SolicitarServicioEstancia::class);
    $resultado = $interactor->ejecutar([
        'estancia_id' => $estancia->id,
        'reservable_id' => $recMinibar->id,
        'cantidad' => 2,
    ]);

    expect(is_array($resultado))->toBeTrue();
    expect($resultado['tipo'])->toBe('consumo_cuenta');
    expect((float) $resultado['detalle']->subtotal)->toBe(30.0);
});
