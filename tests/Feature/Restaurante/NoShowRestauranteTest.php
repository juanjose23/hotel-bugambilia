<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Interactors\Restaurante\Mesas\ProcesarNoShowsRestaurante;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Carbon;

test('expira reservaciones de restaurante con retraso mayor a tolerancia y libera la mesa', function (): void {
    Carbon::setTestNow('2026-08-03 19:40:00');

    $mesa = Espacio::query()->create([
        'codigo' => 'M-NOSHOW-01',
        'nombre' => 'Mesa No Show 1',
        'tipo' => 'mesa',
        'estado' => EstadoEspacio::Reservado,
    ]);

    $reservaVencida = Reserva::query()->create([
        'codigo_reserva' => 'RES-NOSHOW-001',
        'nombre_cliente' => 'Cliente Retrasado',
        'tipo_reserva' => TipoReserva::RESTAURANTE,
        'espacio_id' => $mesa->id,
        'fecha_check_in' => '2026-08-03',
        'hora_reserva' => '19:00',
        'estado' => EstadoReserva::CONFIRMADA,
    ]);

    $interactor = app(ProcesarNoShowsRestaurante::class);
    $procesados = $interactor->ejecutar(30);

    expect($procesados)->toBeGreaterThanOrEqual(1)
        ->and($reservaVencida->fresh()?->estado)->toBe(EstadoReserva::CANCELADA)
        ->and($mesa->fresh()?->estado)->toBe(EstadoEspacio::Disponible);
});
