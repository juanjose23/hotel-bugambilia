<?php

declare(strict_types=1);

use App\BusinessLogic\Restaurante\Delivery\ObtenerDepartamentosDelivery;
use App\Repository\Models\Restaurante\ZonaDelivery;

test('obtiene las zonas de delivery configuradas en la base de datos', function (): void {
    ZonaDelivery::query()->truncate();

    ZonaDelivery::create([
        'codigo' => 'EST',
        'nombre' => 'Estelí',
        'activo' => true,
        'costo_envio' => 50.00,
        'orden' => 1,
        'municipios' => ['Estelí', 'La Trinidad', 'Condega'],
    ]);

    ZonaDelivery::create([
        'codigo' => 'MAD',
        'nombre' => 'Madriz',
        'activo' => false,
        'costo_envio' => 120.00,
        'orden' => 2,
        'municipios' => ['Somoto', 'Palacagüina'],
    ]);

    $businessLogic = app(ObtenerDepartamentosDelivery::class);

    $todos = $businessLogic->ejecutar(soloActivos: false);
    expect($todos)->toHaveCount(2)
        ->and($todos[0]['codigo'])->toBe('EST')
        ->and($todos[0]['costo_envio'])->toBe(50.0)
        ->and($todos[0]['municipios'])->toContain('Estelí')
        ->and($todos[1]['codigo'])->toBe('MAD');

    $soloActivos = $businessLogic->ejecutar(soloActivos: true);
    expect($soloActivos)->toHaveCount(1)
        ->and($soloActivos[0]['codigo'])->toBe('EST');
});

test('ordena las zonas de delivery por el campo orden secuencial', function (): void {
    ZonaDelivery::query()->truncate();

    ZonaDelivery::create([
        'codigo' => 'MGA',
        'nombre' => 'Managua',
        'activo' => true,
        'costo_envio' => 250.00,
        'orden' => 10,
        'municipios' => ['Managua'],
    ]);

    ZonaDelivery::create([
        'codigo' => 'EST',
        'nombre' => 'Estelí',
        'activo' => true,
        'costo_envio' => 50.00,
        'orden' => 1,
        'municipios' => ['Estelí'],
    ]);

    $businessLogic = app(ObtenerDepartamentosDelivery::class);
    $zonas = $businessLogic->ejecutar();

    expect($zonas)->toHaveCount(2)
        ->and($zonas[0]['codigo'])->toBe('EST')
        ->and($zonas[1]['codigo'])->toBe('MGA');
});
