<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\RealizarCheckOutData;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\CheckOutHabitacionRealizado;
use App\Events\Reservas\HabitacionPendienteDeLimpieza;
use App\Interactors\Reservas\Habitaciones\RealizarCheckOutHabitacion;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Support\Facades\Event;

test('realizar check-out completa estancia y detalle, marca habitacion sucia y emite eventos', function (): void {
    Event::fake();

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Habitación 102',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => 1,
    ]);
    $habitacion = Habitacion::factory()->create([
        'reservable_id' => $recurso->id,
        'estado' => EstadoEspacio::Ocupado,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-002',
        'nombre_cliente' => 'Cliente Out',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDays(2),
        'fecha_check_out' => now(),
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $detalle = ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now()->subDays(2),
        'fecha_fin' => now(),
        'estado' => EstadoReservaDetalle::EN_USO,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'habitacion_id' => $habitacion->id,
        'check_in_at' => now()->subDays(2),
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    $dto = new RealizarCheckOutData(
        estanciaId: $estancia->id,
        autorizarSaldoPendiente: true,
    );

    /** @var RealizarCheckOutHabitacion $interactor */
    $interactor = app(RealizarCheckOutHabitacion::class);
    $estanciaFinal = $interactor->ejecutar($dto);

    expect($estanciaFinal->estado)->toBe(EstadoEstancia::FINALIZADA);
    expect($detalle->fresh()->estado)->toBe(EstadoReservaDetalle::COMPLETADO);
    expect($habitacion->fresh()->estado)->toBe(EstadoEspacio::Sucio);
    expect($reserva->fresh()->estado)->toBe(EstadoReserva::CHECKED_OUT);

    Event::assertDispatched(CheckOutHabitacionRealizado::class);
    Event::assertDispatched(HabitacionPendienteDeLimpieza::class);
});

test('rechaza check-out de habitacion con llaves pendientes sin autorizacion', function (): void {
    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Habitación 103',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => 1,
    ]);
    $habitacion = Habitacion::factory()->create([
        'reservable_id' => $recurso->id,
        'estado' => EstadoEspacio::Ocupado,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-003',
        'nombre_cliente' => 'Cliente Llaves',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDays(2),
        'fecha_check_out' => now(),
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $detalle = ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now()->subDays(2),
        'fecha_fin' => now(),
        'estado' => EstadoReservaDetalle::EN_USO,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'habitacion_id' => $habitacion->id,
        'check_in_at' => now()->subDays(2),
        'cantidad_llaves' => 2,
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    /** @var RealizarCheckOutHabitacion $interactor */
    $interactor = app(RealizarCheckOutHabitacion::class);

    expect(fn () => $interactor->ejecutar(new RealizarCheckOutData(
        estanciaId: $estancia->id,
        llavesDevueltas: 1,
    )))->toThrow(DomainException::class, 'Faltan llaves por devolver');
});

test('permite check-out de habitacion con saldo pendiente cuando credito esta autorizado', function (): void {
    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Habitación 104',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => 1,
    ]);
    $habitacion = Habitacion::factory()->create([
        'reservable_id' => $recurso->id,
        'estado' => EstadoEspacio::Ocupado,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-004',
        'nombre_cliente' => 'Cliente Crédito',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDays(2),
        'fecha_check_out' => now(),
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $detalle = ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now()->subDays(2),
        'fecha_fin' => now(),
        'estado' => EstadoReservaDetalle::EN_USO,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'habitacion_id' => $habitacion->id,
        'check_in_at' => now()->subDays(2),
        'cantidad_llaves' => 1,
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    Cuenta::query()->create([
        'numero_cuenta' => 'CTA-TEST-004',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'reserva_id' => $reserva->id,
        'estancia_id' => $estancia->id,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
        'saldo' => 150,
    ]);

    /** @var RealizarCheckOutHabitacion $interactor */
    $interactor = app(RealizarCheckOutHabitacion::class);

    $finalizada = $interactor->ejecutar(new RealizarCheckOutData(
        estanciaId: $estancia->id,
        autorizarSaldoPendiente: true,
        llavesDevueltas: 1,
    ));

    expect($finalizada->estado)->toBe(EstadoEstancia::FINALIZADA);
});
