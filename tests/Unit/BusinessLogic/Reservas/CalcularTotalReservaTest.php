<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\CalcularTotalReserva;

test('calcula total principal multiplicando por unidades', function (): void {
    $calculator = new CalcularTotalReserva;

    $resultado = $calculator->calcular(150.00, 3, []);

    expect($resultado)->toBe(450.00);
});

test('toma minimo 1 unidad cuando unidades es 0 o negativo', function (): void {
    $calculator = new CalcularTotalReserva;

    $resultado = $calculator->calcular(100.00, 0, []);

    expect($resultado)->toBe(100.00);
});

test('suma servicios adicionales al total', function (): void {
    $calculator = new CalcularTotalReserva;

    $servicios = [
        ['precio' => 25.50, 'cantidad' => 2], // 51.00
        ['precio' => 10.00, 'cantidad' => 1], // 10.00
    ];

    $resultado = $calculator->calcular(100.00, 2, $servicios); // 200 + 51 + 10 = 261.00

    expect($resultado)->toBe(261.00);
});

test('redondea el resultado a 2 decimales', function (): void {
    $calculator = new CalcularTotalReserva;

    $servicios = [
        ['precio' => 12.345, 'cantidad' => 1],
    ];

    $resultado = $calculator->calcular(10.00, 1, $servicios);

    expect($resultado)->toBe(22.35);
});
