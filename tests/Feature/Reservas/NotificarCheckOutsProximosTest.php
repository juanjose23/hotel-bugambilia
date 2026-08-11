<?php

declare(strict_types=1);

use App\Enums\Estancias\EstadoEstancia;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Reservas\Habitaciones\NotificarCheckOutsProximos;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;

test('notifica sobre estancias activas con check-out proximo a vencer a usuarios con permisos', function (): void {
    User::factory()->create(['is_admin' => true]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CHK-PRX',
        'nombre_cliente' => 'Cliente Salida Próxima',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => now()->subDay()->format('Y-m-d'),
        'fecha_check_out' => now()->format('Y-m-d'),
        'estado' => EstadoReserva::CHECKED_IN,
    ]);

    $estancia = Estancia::query()->create([
        'reserva_id' => $reserva->id,
        'fecha_entrada_programada' => now()->subDay(),
        'fecha_salida_programada' => now()->addHour(),
        'check_in_at' => now()->subDay(),
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    /** @var NotificarCheckOutsProximos $interactor */
    $interactor = app(NotificarCheckOutsProximos::class);
    $notificados = $interactor->ejecutar();

    expect($notificados)->toBeGreaterThanOrEqual(1);
});
