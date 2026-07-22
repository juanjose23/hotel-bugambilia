<?php

declare(strict_types=1);

use App\Models\Habitaciones\Habitacion;
use App\Services\Shared\GeneradorCodigoService;

beforeEach(function () {
    $this->service = app(GeneradorCodigoService::class);
});

test('it genera un codigo correlativo base para habitacion', function () {
    $codigo = $this->service->generarCorrelativo('HAB', Habitacion::class);
    expect($codigo)->toBe('HAB-0001');
});

test('it genera un codigo de barras limpio', function () {
    $barcode = $this->service->generarCodigoBarras('Test Product - 456');
    expect($barcode)->toBe('TestProduct456');
});
