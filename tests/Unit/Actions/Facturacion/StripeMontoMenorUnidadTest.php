<?php

declare(strict_types=1);

use App\Actions\Facturacion\StripeMontoMenorUnidad;

it('convierte el monto a centavos para monedas con decimales', function (): void {
    $action = new StripeMontoMenorUnidad;

    expect($action->ejecutar(100.00, 'USD'))->toBe(10000)
        ->and($action->ejecutar(99.99, 'USD'))->toBe(9999)
        ->and($action->ejecutar(0.01, 'USD'))->toBe(1)
        ->and($action->ejecutar(1500.00, 'EUR'))->toBe(150000);
});

it('no multiplica monedas ISO 4217 sin menor unidad', function (): void {
    $action = new StripeMontoMenorUnidad;

    expect($action->ejecutar(100.00, 'JPY'))->toBe(100)
        ->and($action->ejecutar(1234.00, 'KRW'))->toBe(1234)
        ->and($action->ejecutar(50.00, 'CLP'))->toBe(50);
});

it('normaliza el código de moneda a mayúsculas', function (): void {
    $action = new StripeMontoMenorUnidad;

    expect($action->ejecutar(100.00, 'usd'))->toBe(10000)
        ->and($action->ejecutar(100.00, 'jpy'))->toBe(100);
});

it('redondea los montos a la menor unidad más cercana', function (): void {
    $action = new StripeMontoMenorUnidad;

    expect($action->ejecutar(10.005, 'USD'))->toBe(1001)
        ->and($action->ejecutar(10.004, 'USD'))->toBe(1000);
});
