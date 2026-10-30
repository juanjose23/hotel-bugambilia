<?php

declare(strict_types=1);

use App\BusinessLogic\Shared\Reportes\DecidirModoReporte;
use Tests\TestCase;

uses(TestCase::class);

test('genera directo cuando el rango de fechas es menor o igual a 31 dias', function (): void {
    $decisor = new DecidirModoReporte;

    // Rango de 20 días -> Directo
    $params = [
        'fecha_inicio' => '2026-09-01',
        'fecha_fin' => '2026-09-21',
    ];

    expect($decisor->debeIrABackground($params))->toBeFalse();
    expect($decisor->diasDeRango($params))->toBe(20);
});

test('pasa a segundo plano cuando el rango de fechas es mayor a 31 dias', function (): void {
    $decisor = new DecidirModoReporte;

    // Rango de 60 días -> Background
    $params = [
        'fecha_inicio' => '2026-07-01',
        'fecha_fin' => '2026-08-30',
    ];

    expect($decisor->debeIrABackground($params))->toBeTrue();
    expect($decisor->diasDeRango($params))->toBeGreaterThan(31);
});

test('sin fechas se considera reporte de catalogo y es siempre directo', function (): void {
    $decisor = new DecidirModoReporte;

    expect($decisor->debeIrABackground([]))->toBeFalse();
    expect($decisor->diasDeRango([]))->toBeNull();
});
