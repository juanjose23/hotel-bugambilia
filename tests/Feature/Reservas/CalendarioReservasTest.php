<?php

declare(strict_types=1);

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Queries\Reservas\ObtenerCalendarioReservasQuery;

test('muestra una reserva en todos los días de su período y completa las semanas', function (): void {
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CAL-2026-001',
        'nombre_cliente' => 'Huésped del calendario',
        'tipo_reserva' => TipoReserva::HABITACION,
        'fecha_check_in' => '2026-08-05',
        'fecha_check_out' => '2026-08-08',
        'adultos' => 2,
        'ninos' => 0,
        'estado' => EstadoReserva::CONFIRMADA,
        'subtotal' => 400,
        'descuento' => 0,
        'total' => 460,
    ]);

    $calendario = app(ObtenerCalendarioReservasQuery::class)
        ->ejecutar(8, 2026, 'todos');

    expect($calendario['days'])->toHaveCount(42)
        ->and(count($calendario['days']) % 7)->toBe(0);

    foreach ([5, 6, 7, 8] as $dia) {
        expect($calendario['reservasPorDia']->get($dia)?->pluck('id')->all())
            ->toContain($reserva->id);
    }

    expect($calendario['reservasPorDia']->get(5)?->first()['es_llegada'])->toBeTrue()
        ->and($calendario['reservasPorDia']->get(8)?->first()['es_salida'])->toBeTrue();
});
