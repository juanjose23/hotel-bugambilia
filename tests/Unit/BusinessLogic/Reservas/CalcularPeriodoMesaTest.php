<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Calculos\CalcularPeriodoReserva;

it('reserva una mesa por horas cuando entrada y salida tienen la misma fecha', function (): void {
    [$inicio, $fin] = (new CalcularPeriodoReserva)->calcular(new DateTimeImmutable('2027-01-03'), new DateTimeImmutable('2027-01-03'), ['hora_reserva' => '12:00', 'duracion_horas' => 2], 60);
    expect($inicio->format('Y-m-d H:i'))->toBe('2027-01-03 12:00')->and($fin->format('Y-m-d H:i'))->toBe('2027-01-03 14:00');
});
