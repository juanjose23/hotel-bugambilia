<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\CalcularResumenRestaurante;

test('calcula horas mesas preorden total y abono del cincuenta por ciento', function (): void {
    $resumen = app(CalcularResumenRestaurante::class)->calcular(
        comensales: 7,
        horas: 3,
        mesas: [
            ['capacidad' => 4, 'tarifa' => 100.0, 'por_hora' => true],
            ['capacidad' => 4, 'tarifa' => 100.0, 'por_hora' => true],
        ],
        preorden: [
            ['cantidad' => 2, 'precio' => 150.0],
            ['cantidad' => 1, 'precio' => 80.0],
        ],
    );

    expect($resumen)
        ->toMatchArray([
            'horas' => 3,
            'mesas_requeridas' => 2,
            'mesas_seleccionadas' => 2,
            'capacidad_total' => 8,
            'costo_mesas' => 600.0,
            'costo_preorden' => 380.0,
            'subtotal' => 980.0,
            'abono_50' => 490.0,
        ]);
});
