<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Gestion\LimpiarReservasNoConfirmadas;
use App\Repository\Models\Reservas\Reserva;

test('limpia y cancela reservaciones pendientes vencidas', function (): void {
    $reservaVencida = Reserva::query()->create([
        'codigo_reserva' => 'RES-PND-VNC',
        'nombre_cliente' => 'Cliente Vencido',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDay()->format('Y-m-d'),
        'fecha_check_out' => now()->addDay()->format('Y-m-d'),
        'estado' => EstadoReserva::PENDIENTE,
        'hold_expires_at' => now()->subHour(),
    ]);

    /** @var LimpiarReservasNoConfirmadas $interactor */
    $interactor = app(LimpiarReservasNoConfirmadas::class);
    $procesadas = $interactor->ejecutar();

    expect($procesadas)->toBeGreaterThanOrEqual(1);
    expect($reservaVencida->fresh()->estado)->toBe(EstadoReserva::CANCELADA);
});
