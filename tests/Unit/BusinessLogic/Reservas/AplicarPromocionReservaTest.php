<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\AplicarPromocionReserva;

test('calcula descuento por porcentaje correctamente', function (): void {
    $promocion = new AplicarPromocionReserva;

    $resultado = $promocion->calcular(200.00, 15.0, null);

    expect($resultado['subtotal'])->toBe(200.00);
    expect($resultado['descuento'])->toBe(30.00);
    expect($resultado['total'])->toBe(170.00);
});

test('calcula descuento por monto fijo sin exceder el subtotal', function (): void {
    $promocion = new AplicarPromocionReserva;

    $resultado = $promocion->calcular(100.00, null, 25.0);

    expect($resultado['descuento'])->toBe(25.00);
    expect($resultado['total'])->toBe(75.00);

    $resultadoExcesivo = $promocion->calcular(50.00, null, 100.0);
    expect($resultadoExcesivo['descuento'])->toBe(50.00);
    expect($resultadoExcesivo['total'])->toBe(0.00);
});

test('calcula descuento usando precio de paquete prioritario', function (): void {
    $promocion = new AplicarPromocionReserva;

    $resultado = $promocion->calcular(300.00, 10.0, 20.0, 250.0);

    expect($resultado['descuento'])->toBe(50.00);
    expect($resultado['total'])->toBe(250.00);
});
