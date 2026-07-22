<?php

declare(strict_types=1);

use App\Services\Shared\TasaCambioService;
use Database\Seeders\TasaCambioSeeder;

beforeEach(function () {
    $this->seed(TasaCambioSeeder::class);
    $this->service = app(TasaCambioService::class);
});

test('it obtiene la tasa de cambio vigente', function () {
    $tasa = $this->service->obtenerTasa(now(), 'USD', 'NIO');
    expect($tasa)->toBe(36.5200);
});

test('it realiza conversion de usd a nio', function () {
    $converted = $this->service->convertir(100, 'USD', 'NIO', now());
    expect($converted)->toBe(3652.00);
});

test('it realiza conversion inversa de nio a usd', function () {
    $converted = $this->service->convertir(3652, 'NIO', 'USD', now());
    expect($converted)->toBe(100.0);
});

test('it retorna el mismo monto si las monedas son iguales', function () {
    $converted = $this->service->convertir(100, 'USD', 'USD', now());
    expect($converted)->toBe(100.0);
});
