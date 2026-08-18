<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\ConfirmarReservaHabitacionData;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaConfirmada;
use App\Interactors\Reservas\Habitaciones\ConfirmarReservaHabitacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Support\Facades\Event;

test('confirma reserva de habitacion pendiente', function (): void {
    Event::fake([ReservaConfirmada::class]);

    $tipo = CatalogoTipo::query()->create(['codigo' => 'HAB-TC-'.Str::random(4), 'nombre' => 'Tipo Conf', 'estado' => 1]);
    $categoria = Catalogo::query()->create(['codigo' => 'CAT-CONF-'.Str::random(4), 'nombre' => 'Cat Conf', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $ubicacion = Ubicacion::query()->create(['nombre' => 'Ub Conf '.Str::random(4), 'tipo' => 1, 'estado' => 1]);

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Habitacion Confirmar Test',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    Habitacion::query()->create([
        'codigo' => 'HAB-CONF-'.Str::random(4),
        'nombre' => 'Habitacion Confirmar',
        'numero' => rand(100, 999),
        'slug' => 'hab-conf-'.Str::random(4),
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'reservable_id' => $recurso->id,
        'estado' => EstadoEspacio::Disponible,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CONF-HAB-'.Str::random(4),
        'nombre_cliente' => 'Cliente Confirmar',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDays(2)->toDateString(),
        'fecha_check_out' => now()->addDays(5)->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
        'tipo_pago' => TipoPagoReserva::SIN_PAGO,
        'total' => 300.00,
        'total_pagado' => 0.00,
    ]);

    ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'estado' => 1,
        'fecha_inicio' => now()->addDays(2),
        'fecha_fin' => now()->addDays(5),
        'precio_unitario' => 100.00,
        'subtotal' => 300.00,
    ]);

    $data = new ConfirmarReservaHabitacionData(
        reservaId: $reserva->id,
        observaciones: 'Confirmacion manual test',
        usuarioId: null,
    );

    $resultado = app(ConfirmarReservaHabitacion::class)->ejecutar($data);

    expect($resultado->estado)->toBe(EstadoReserva::CONFIRMADA);
    Event::assertDispatched(ReservaConfirmada::class);
});

test('lanza excepcion si la reserva de habitacion no esta pendiente', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CONF-ERR-'.Str::random(4),
        'nombre_cliente' => 'Cliente Conf Error',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDays(2)->toDateString(),
        'fecha_check_out' => now()->addDays(5)->toDateString(),
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    $data = new ConfirmarReservaHabitacionData(
        reservaId: $reserva->id,
    );

    app(ConfirmarReservaHabitacion::class)->ejecutar($data);
})->throws(DomainException::class, 'no se encuentra en estado pendiente');
