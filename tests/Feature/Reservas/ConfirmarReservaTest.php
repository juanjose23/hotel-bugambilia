<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Events\Reservas\ReservaConfirmada;
use App\Interactors\Reservas\Gestion\ConfirmarReserva;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\Event;

test('confirma una reserva pendiente y cambia estado a confirmada', function (): void {
    Event::fake([ReservaConfirmada::class]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CONF-001',
        'nombre_cliente' => 'Cliente Confirmar',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
    ]);

    app(ConfirmarReserva::class)->ejecutar($reserva);

    expect($reserva->fresh()->estado)->toBe(EstadoReserva::CONFIRMADA);
    Event::assertDispatched(ReservaConfirmada::class);
});

test('lanza excepcion al intentar confirmar una reserva ya cancelada', function (): void {
    Event::fake([ReservaConfirmada::class]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CONF-002',
        'nombre_cliente' => 'Cliente Cancelado',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'estado' => EstadoReserva::CANCELADA,
    ]);

    app(ConfirmarReserva::class)->ejecutar($reserva);
})->throws(DomainException::class);

test('confirmar reserva dispatcha evento una sola vez', function (): void {
    Event::fake([ReservaConfirmada::class]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CONF-003',
        'nombre_cliente' => 'Cliente Evento',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
    ]);

    app(ConfirmarReserva::class)->ejecutar($reserva);

    Event::assertDispatchedTimes(ReservaConfirmada::class, 1);
});
