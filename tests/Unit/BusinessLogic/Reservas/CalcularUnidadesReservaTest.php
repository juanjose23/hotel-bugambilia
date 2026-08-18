<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\CalcularUnidadesReserva;
use App\Enums\Reservas\TipoReserva;

test('calcula dias para tipo habitacion con check out especificado', function (): void {
    $calculator = new CalcularUnidadesReserva;

    $checkIn = new DateTimeImmutable('2026-09-01');
    $checkOut = new DateTimeImmutable('2026-09-05');
    $inicio = $checkIn;
    $fin = $checkOut;

    $resultado = $calculator->calcular(TipoReserva::HABITACION, $checkIn, $checkOut, false, $inicio, $fin);

    expect($resultado)->toBe(4);
});

test('usa 1 dia por defecto para habitacion sin check out', function (): void {
    $calculator = new CalcularUnidadesReserva;

    $checkIn = new DateTimeImmutable('2026-09-01');
    $inicio = $checkIn;
    $fin = $checkIn;

    $resultado = $calculator->calcular(TipoReserva::HABITACION, $checkIn, null, false, $inicio, $fin);

    expect($resultado)->toBe(1);
});

test('calcula horas redondeando hacia arriba para restaurante por hora', function (): void {
    $calculator = new CalcularUnidadesReserva;

    $checkIn = new DateTimeImmutable('2026-09-01');
    $inicio = new DateTimeImmutable('2026-09-01 14:00:00');
    $fin = new DateTimeImmutable('2026-09-01 16:30:00'); // 2.5 horas => ceil => 3

    $resultado = $calculator->calcular(TipoReserva::RESTAURANTE, $checkIn, null, true, $inicio, $fin);

    expect($resultado)->toBe(3);
});

test('retorna 1 por defecto para otros tipos o no por hora', function (): void {
    $calculator = new CalcularUnidadesReserva;

    $checkIn = new DateTimeImmutable('2026-09-01');
    $inicio = new DateTimeImmutable('2026-09-01 14:00:00');
    $fin = new DateTimeImmutable('2026-09-01 18:00:00');

    $resultado = $calculator->calcular(TipoReserva::RESTAURANTE, $checkIn, null, false, $inicio, $fin);

    expect($resultado)->toBe(1);
});
