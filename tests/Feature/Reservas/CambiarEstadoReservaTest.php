<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Gestion\CambiarEstadoReserva;
use App\Repository\Models\Reservas\Reserva;

test('cambia estado de pendiente a confirmada', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CAMBIAR-001',
        'nombre_cliente' => 'Cliente Cambio',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
    ]);

    app(CambiarEstadoReserva::class)->ejecutar($reserva, EstadoReserva::CONFIRMADA, null, 'Confirmación manual');

    expect($reserva->fresh()->estado)->toBe(EstadoReserva::CONFIRMADA);
});

test('rechaza transicion invalida de pendiente a checked_in', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CAMBIAR-002',
        'nombre_cliente' => 'Cliente Invalido',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
    ]);

    app(CambiarEstadoReserva::class)->ejecutar($reserva, EstadoReserva::CHECKED_IN, null, 'Salto invalido');
})->throws(DomainException::class);

test('cambia estado dentro de transaccion y sincroniza modelo', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CAMBIAR-003',
        'nombre_cliente' => 'Cliente Transaccion',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
        'estado' => EstadoReserva::PENDIENTE,
    ]);

    app(CambiarEstadoReserva::class)->ejecutar($reserva, EstadoReserva::CONFIRMADA, null, 'Test transaccion');

    expect($reserva->estado)->toBe(EstadoReserva::CONFIRMADA);
    expect($reserva->fresh()->estado)->toBe(EstadoReserva::CONFIRMADA);
});
