<?php

declare(strict_types=1);

use App\BusinessLogic\Cuentas\CalcularVuelto;

it('no genera vuelto cuando el monto pagado no supera el monto a cobrar', function (): void {
    $calcularVuelto = new CalcularVuelto;

    $resultado = $calcularVuelto->ejecutar(
        saldoCuenta: 100.0,
        codigoMonedaPago: 'NIO',
        codigoMonedaVuelto: 'NIO',
        simboloMonedaVuelto: 'C$',
        tasaConversion: 1.0,
        data: ['monto' => 100.0, 'monto_recibido' => 0],
    );

    expect($resultado)->toBeNull();
});

it('calcula vuelto en la misma moneda sin conversion', function (): void {
    $calcularVuelto = new CalcularVuelto;

    $resultado = $calcularVuelto->ejecutar(
        saldoCuenta: 100.0,
        codigoMonedaPago: 'NIO',
        codigoMonedaVuelto: 'NIO',
        simboloMonedaVuelto: 'C$',
        tasaConversion: 1.0,
        data: ['monto' => 100.0, 'monto_recibido' => 150.0],
    );

    expect($resultado)->not->toBeNull()
        ->and($resultado['vuelto'])->toBe(50.0)
        ->and($resultado['texto'])->toBe('Vuelto entregado: C$ 50.00 (NIO)');
});

it('convierte el vuelto de USD a la moneda destino usando la tasa', function (): void {
    $calcularVuelto = new CalcularVuelto;

    $resultado = $calcularVuelto->ejecutar(
        saldoCuenta: 100.0,
        codigoMonedaPago: 'USD',
        codigoMonedaVuelto: 'NIO',
        simboloMonedaVuelto: 'C$',
        tasaConversion: 36.5,
        data: ['monto' => 100.0, 'monto_recibido' => 120.0],
    );

    expect($resultado)->not->toBeNull()
        ->and($resultado['vuelto'])->toBe(730.0);
});

it('convierte el vuelto a USD cuando se paga en otra moneda', function (): void {
    $calcularVuelto = new CalcularVuelto;

    $resultado = $calcularVuelto->ejecutar(
        saldoCuenta: 1000.0,
        codigoMonedaPago: 'NIO',
        codigoMonedaVuelto: 'USD',
        simboloMonedaVuelto: '$',
        tasaConversion: 36.5,
        data: ['monto' => 1000.0, 'monto_recibido' => 1100.0],
    );

    expect($resultado)->not->toBeNull()
        ->and($resultado['vuelto'])->toBe(2.74);
});

it('limita el monto base de cobro al saldo de la cuenta', function (): void {
    $calcularVuelto = new CalcularVuelto;

    $resultado = $calcularVuelto->ejecutar(
        saldoCuenta: 50.0,
        codigoMonedaPago: 'NIO',
        codigoMonedaVuelto: 'NIO',
        simboloMonedaVuelto: 'C$',
        tasaConversion: 1.0,
        data: ['monto' => 100.0, 'monto_recibido' => 120.0],
    );

    expect($resultado)->not->toBeNull()
        ->and($resultado['vuelto'])->toBe(70.0);
});
