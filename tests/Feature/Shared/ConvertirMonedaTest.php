<?php

declare(strict_types=1);

use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Queries\Monedas\ObtenerTasaCambioQuery;
use Database\Seeders\Configuracion\MonedaSeeder;
use Database\Seeders\TasaCambioSeeder;

beforeEach(function () {
    $this->seed([
        MonedaSeeder::class,
        TasaCambioSeeder::class,
    ]);
    $this->tasaQuery = app(ObtenerTasaCambioQuery::class);
    $this->convertirMoneda = app(ConvertirMoneda::class);
});

test('it obtiene la tasa de cambio vigente', function () {
    $tasa = $this->tasaQuery->ejecutar(now(), 'USD', 'NIO');
    expect($tasa)->toBe(36.5200);
});

test('it realiza conversion de usd a nio', function () {
    $usdId = (int) Moneda::query()->where('codigo', 'USD')->value('id');
    $converted = $this->convertirMoneda->aBase(100, $usdId);
    expect($converted)->toBe(3652.00);
});

test('it realiza conversion inversa de nio a usd', function () {
    $usdId = (int) Moneda::query()->where('codigo', 'USD')->value('id');
    $converted = $this->convertirMoneda->desdeBase(3652, $usdId);
    expect($converted)->toBe(100.0);
});

test('it retorna el mismo monto si la moneda es nio', function () {
    $nioId = (int) Moneda::query()->where('codigo', 'NIO')->value('id');
    $converted = $this->convertirMoneda->aBase(100, $nioId);
    expect($converted)->toBe(100.0);
});
