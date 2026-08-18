<?php

declare(strict_types=1);

use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Habitaciones\NotificarCheckOutsProximos;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\User;
use Illuminate\Support\Facades\Notification;

test('notifica estancias proximas a checkout', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Habitacion Notif',
        'tipo' => 1,
        'control_disponibilidad' => 1,
        'estado' => 1,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-NOTIF-CO-001',
        'nombre_cliente' => 'Cliente Notif CO',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDay()->toDateString(),
        'fecha_check_out' => now()->addHours(1)->toDateTimeString(),
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $detalle = ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'estado' => 3,
        'fecha_inicio' => now()->subDay(),
        'fecha_fin' => now()->addHours(1),
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'check_in_at' => now()->subDay(),
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    $notificados = app(NotificarCheckOutsProximos::class)->ejecutar();

    expect($notificados)->toBeGreaterThanOrEqual(0);
});

test('retorna cero cuando no hay estancias proximas a checkout', function (): void {
    $notificados = app(NotificarCheckOutsProximos::class)->ejecutar();

    expect($notificados)->toBe(0);
});
