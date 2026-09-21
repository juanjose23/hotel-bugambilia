<?php

declare(strict_types=1);

use App\Actions\Shared\GenerarCorrelativoCodigoAction;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Queries\Catalogos\GenerarCodigoBarras;

beforeEach(function () {
    $this->action = app(GenerarCorrelativoCodigoAction::class);
    $this->generarBarras = app(GenerarCodigoBarras::class);
});

test('it genera un codigo correlativo base para habitacion', function () {
    $codigo = $this->action->ejecutar('HAB', Habitacion::class);
    expect($codigo)->toBe('HAB-0001');
});

test('it genera un codigo de barras limpio', function () {
    $producto = new Producto(['nombre' => 'Test Product - 456']);
    $barcode = $this->generarBarras->ejecutar($producto);
    expect($barcode)->toBe('TestProduct456');
});
