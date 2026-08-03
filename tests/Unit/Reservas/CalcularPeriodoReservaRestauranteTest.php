<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\CalcularPeriodoReserva;

test('respeta las horas seleccionadas al calcular el fin de una reserva de mesa', function (): void {
    [$inicio, $fin] = (new CalcularPeriodoReserva)->calcular(
        new DateTimeImmutable('2026-08-10'),
        null,
        ['hora_reserva' => '19:00', 'duracion_horas' => 3],
        60,
    );

    expect($inicio->format('Y-m-d H:i'))->toBe('2026-08-10 19:00')
        ->and($fin->format('Y-m-d H:i'))->toBe('2026-08-10 22:00');
});
