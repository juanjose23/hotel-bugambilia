<?php

declare(strict_types=1);

use App\BusinessLogic\Facturacion\Stripe\ReintentarOperacionStripe;
use App\Exceptions\StripeApiException;

it('retorna exito en el primer intento cuando Stripe responde', function (): void {
    $reintentar = new ReintentarOperacionStripe(esperaMilisegundos: 0);
    $intentosUsados = 0;

    $resultado = $reintentar->ejecutar(fn () => 'ok', $intentosUsados);

    expect($resultado)->toBe('ok')
        ->and($intentosUsados)->toBe(1);
});

it('reintenta y logra exito cuando Stripe falla las primeras veces', function (): void {
    $reintentar = new ReintentarOperacionStripe(maxIntentos: 3, esperaMilisegundos: 0);
    $llamadas = 0;
    $intentosUsados = 0;

    $resultado = $reintentar->ejecutar(function () use (&$llamadas): string {
        $llamadas++;

        if ($llamadas < 3) {
            throw new StripeApiException('Stripe no responde');
        }

        return 'exito-al-tercero';
    }, $intentosUsados);

    expect($llamadas)->toBe(3)
        ->and($resultado)->toBe('exito-al-tercero')
        ->and($intentosUsados)->toBe(3);
});

it('agota los intentos y lanza el ultimo error de Stripe', function (): void {
    $reintentar = new ReintentarOperacionStripe(maxIntentos: 3, esperaMilisegundos: 0);
    $llamadas = 0;
    $intentosUsados = 0;

    try {
        $reintentar->ejecutar(function () use (&$llamadas): string {
            $llamadas++;

            throw new StripeApiException('Stripe no responde');
        }, $intentosUsados);

        throw new RuntimeException('Debería haber lanzado StripeApiException');
    } catch (StripeApiException $exception) {
        expect($llamadas)->toBe(3)
            ->and($exception->getMessage())->toBe('Stripe no responde')
            ->and($intentosUsados)->toBe(3);
    }
});

it('no reintenta cuando el error no es de conexion con Stripe', function (): void {
    $reintentar = new ReintentarOperacionStripe(maxIntentos: 3, esperaMilisegundos: 0);
    $llamadas = 0;
    $intentosUsados = 0;

    $reintentar->ejecutar(function () use (&$llamadas): string {
        $llamadas++;

        throw new DomainException('Regla de negocio rota');
    }, $intentosUsados);
})->throws(DomainException::class, 'Regla de negocio rota');

it('se respeta un maximo de intentos distinto', function (): void {
    $reintentar = new ReintentarOperacionStripe(maxIntentos: 2, esperaMilisegundos: 0);
    $llamadas = 0;
    $intentosUsados = 0;

    try {
        $reintentar->ejecutar(function () use (&$llamadas): string {
            $llamadas++;

            throw new StripeApiException('Stripe no responde');
        }, $intentosUsados);

        throw new RuntimeException('Debería haber lanzado StripeApiException');
    } catch (StripeApiException $exception) {
        expect($llamadas)->toBe(2)
            ->and($intentosUsados)->toBe(2);
    }
});
