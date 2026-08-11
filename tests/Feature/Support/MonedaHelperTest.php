<?php

declare(strict_types=1);

use App\Repository\Models\Monedas\Moneda;
use App\Support\MonedaHelper;

test('moneda helper devuelve codigo y simbolo predeterminado si no se pasa modelo', function (): void {
    MonedaHelper::resetCache();

    $monedaDefault = Moneda::query()->firstOrCreate(
        ['codigo' => 'NIO'],
        [
            'nombre' => 'Córdoba',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
        ]
    );

    if (! $monedaDefault->es_predeterminada) {
        $monedaDefault->update(['es_predeterminada' => true]);
    }

    expect(MonedaHelper::codigo())->toBe($monedaDefault->codigo)
        ->and(MonedaHelper::simbolo())->toBe($monedaDefault->simbolo)
        ->and(MonedaHelper::formatear(150.50))->toBe("{$monedaDefault->simbolo} 150.50");
});

test('moneda helper devuelve codigo y simbolo del modelo pasado dinamicamente', function (): void {
    MonedaHelper::resetCache();

    $monedaUSD = Moneda::query()->firstOrCreate(
        ['codigo' => 'USD'],
        [
            'nombre' => 'Dólar Estadounidense',
            'simbolo' => '$',
            'es_predeterminada' => false,
        ]
    );

    expect(MonedaHelper::codigo($monedaUSD))->toBe('USD')
        ->and(MonedaHelper::simbolo($monedaUSD))->toBe('$')
        ->and(MonedaHelper::formatear(25.00, $monedaUSD))->toBe('$ 25.00');
});
