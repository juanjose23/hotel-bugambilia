<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Gestion\ActualizarReserva;
use App\Interactors\Reservas\Gestion\CrearReserva;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Shared\Precio;

test('permite crear y editar una reserva con habitaciones adicionales recalculando el total', function (): void {
    $rec1 = RecursoReservable::query()->create([
        'nombre' => 'Hab 101',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
    ]);
    $hab1 = Habitacion::factory()->create(['reservable_id' => $rec1->id, 'estado' => EstadoEspacio::Disponible]);

    $rec2 = RecursoReservable::query()->create([
        'nombre' => 'Hab 102',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
    ]);
    $hab2 = Habitacion::factory()->create(['reservable_id' => $rec2->id, 'estado' => EstadoEspacio::Disponible]);

    $datosCrear = [
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'nombre_cliente' => 'Cliente Múltiples Habitaciones',
        'fecha_check_in' => now()->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(2)->format('Y-m-d'),
        'habitacion_id' => $hab1->id,
        'adultos' => 4,
    ];

    $habitacionesAdicionales = [
        ['habitacion_id' => $hab2->id],
    ];

    /** @var CrearReserva $crearReserva */
    $crearReserva = app(CrearReserva::class);
    $reserva = $crearReserva->ejecutar($datosCrear, [], [], $habitacionesAdicionales);

    expect($reserva->estado)->toBe(EstadoReserva::PENDIENTE);
    expect($reserva->detalles()->count())->toBe(2);

    /** @var ActualizarReserva $actualizarReserva */
    $actualizarReserva = app(ActualizarReserva::class);
    $reservaEditada = $actualizarReserva->ejecutar($reserva->refresh(), [
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'nombre_cliente' => 'Cliente Múltiples Habitaciones Actualizado',
        'fecha_check_in' => now()->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(2)->format('Y-m-d'),
        'habitacion_id' => $hab1->id,
        'habitaciones_adicionales' => [
            ['habitacion_id' => $hab2->id],
        ],
    ]);

    expect($reservaEditada->detalles()->count())->toBe(2);
});

test('permite agregar habitaciones adicionales estando en check-in acumulando el cobro en el saldo para pagar en checkout', function (): void {
    $moneda = Moneda::query()->where('es_predeterminada', true)->first()
        ?? Moneda::factory()->create(['es_predeterminada' => true]);

    $rec1 = RecursoReservable::query()->create([
        'nombre' => 'Hab 201',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
    ]);
    $hab1 = Habitacion::factory()->create(['reservable_id' => $rec1->id, 'estado' => EstadoEspacio::Ocupado]);
    Precio::query()->create([
        'moneda_id' => $moneda->id,
        'priceable_type' => Habitacion::class,
        'priceable_id' => $hab1->id,
        'tipo_precio' => 'base',
        'precio' => 100.0,
        'fecha_inicio' => today(),
        'estado' => EstadoGeneral::Activo,
    ]);

    $rec2 = RecursoReservable::query()->create([
        'nombre' => 'Hab 202',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
    ]);
    $hab2 = Habitacion::factory()->create(['reservable_id' => $rec2->id, 'estado' => EstadoEspacio::Disponible]);
    Precio::query()->create([
        'moneda_id' => $moneda->id,
        'priceable_type' => Habitacion::class,
        'priceable_id' => $hab2->id,
        'tipo_precio' => 'base',
        'precio' => 150.0,
        'fecha_inicio' => today(),
        'estado' => EstadoGeneral::Activo,
    ]);

    /** @var CrearReserva $crearReserva */
    $crearReserva = app(CrearReserva::class);
    $reserva = $crearReserva->ejecutar([
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'nombre_cliente' => 'Huésped En Check-In',
        'fecha_check_in' => now()->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(2)->format('Y-m-d'),
        'habitacion_id' => $hab1->id,
    ]);

    $reserva->update(['estado' => EstadoReserva::CHECKED_IN]);
    $saldoInicial = (float) $reserva->saldo;

    /** @var ActualizarReserva $actualizarReserva */
    $actualizarReserva = app(ActualizarReserva::class);
    $reservaActualizada = $actualizarReserva->ejecutar($reserva->refresh(), [
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'nombre_cliente' => 'Huésped En Check-In',
        'fecha_check_in' => now()->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(2)->format('Y-m-d'),
        'habitacion_id' => $hab1->id,
        'habitaciones_adicionales' => [
            ['habitacion_id' => $hab2->id],
        ],
    ]);

    expect($reservaActualizada->estado)->toBe(EstadoReserva::CHECKED_IN);
    expect($reservaActualizada->detalles()->count())->toBe(2);
    expect((float) $reservaActualizada->saldo)->toBeGreaterThan($saldoInicial);
});

test('crea una nueva cuenta si la cuenta previa de la reserva ya está cerrada al actualizar', function (): void {
    $moneda = Moneda::query()->where('es_predeterminada', true)->first()
        ?? Moneda::factory()->create(['es_predeterminada' => true]);

    $rec1 = RecursoReservable::query()->create([
        'nombre' => 'Hab 301',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
    ]);
    $hab1 = Habitacion::factory()->create(['reservable_id' => $rec1->id, 'estado' => EstadoEspacio::Ocupado]);
    Precio::query()->create([
        'moneda_id' => $moneda->id,
        'priceable_type' => Habitacion::class,
        'priceable_id' => $hab1->id,
        'tipo_precio' => 'base',
        'precio' => 120.0,
        'fecha_inicio' => today(),
        'estado' => EstadoGeneral::Activo,
    ]);

    $rec2 = RecursoReservable::query()->create([
        'nombre' => 'Hab 302',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
    ]);
    $hab2 = Habitacion::factory()->create(['reservable_id' => $rec2->id, 'estado' => EstadoEspacio::Disponible]);
    Precio::query()->create([
        'moneda_id' => $moneda->id,
        'priceable_type' => Habitacion::class,
        'priceable_id' => $hab2->id,
        'tipo_precio' => 'base',
        'precio' => 180.0,
        'fecha_inicio' => today(),
        'estado' => EstadoGeneral::Activo,
    ]);

    /** @var CrearReserva $crearReserva */
    $reserva = app(CrearReserva::class)->ejecutar([
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'nombre_cliente' => 'Huésped Con Cuenta Cerrada',
        'fecha_check_in' => now()->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(2)->format('Y-m-d'),
        'habitacion_id' => $hab1->id,
    ]);

    /** @var Cuenta $cuenta */
    $cuenta = Cuenta::query()->where('reserva_id', $reserva->id)->latest('id')->first();
    $cuenta->update(['subtotal' => 240.0, 'total' => 240.0, 'total_pagado' => 240.0, 'saldo' => 0.0, 'estado' => EstadoCuenta::CERRADA]);
    $reserva->update(['subtotal' => 240.0, 'total' => 240.0, 'total_pagado' => 240.0, 'saldo' => 0.0]);

    /** @var ActualizarReserva $actualizarReserva */
    $actualizarReserva = app(ActualizarReserva::class);
    $reservaActualizada = $actualizarReserva->ejecutar($reserva->refresh(), [
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'nombre_cliente' => 'Huésped Con Cuenta Cerrada',
        'fecha_check_in' => now()->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(2)->format('Y-m-d'),
        'habitacion_id' => $hab1->id,
        'habitaciones_adicionales' => [
            ['habitacion_id' => $hab2->id],
        ],
    ]);

    $nuevaCuenta = Cuenta::query()->where('reserva_id', $reserva->id)->latest('id')->first();

    expect($nuevaCuenta->id)->not()->toBe($cuenta->id);
    expect($nuevaCuenta->estado)->toBe(EstadoCuenta::ABIERTA);
    expect((float) $nuevaCuenta->subtotal)->toBe(360.0);
    expect((float) $reservaActualizada->subtotal)->toBe(600.0);
    expect((float) $reservaActualizada->total)->toBe(round((float) $nuevaCuenta->total + (float) $cuenta->total, 2));
    expect((float) $reservaActualizada->total_pagado)->toBe(240.0);
    expect((float) $reservaActualizada->saldo)->toBe(round((float) $reservaActualizada->total - 240.0, 2));
});
