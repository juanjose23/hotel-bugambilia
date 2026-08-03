<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\DividirCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Monedas\TasaCambio;

beforeEach(function (): void {
    $monedaNio = Moneda::query()->where('codigo', 'NIO')->first();
    if ($monedaNio === null) {
        $monedaNio = Moneda::query()->create([
            'codigo' => 'NIO',
            'nombre' => 'Córdoba Nicaragüense',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]);
    }

    $monedaUsd = Moneda::query()->where('codigo', 'USD')->first();
    if ($monedaUsd === null) {
        $monedaUsd = Moneda::query()->create([
            'codigo' => 'USD',
            'nombre' => 'Dólar Estadounidense',
            'simbolo' => '$',
            'es_predeterminada' => false,
            'estado' => EstadoGeneral::Activo,
        ]);
    }

    TasaCambio::query()->firstOrCreate(
        [
            'moneda_origen_id' => $monedaUsd->id,
            'moneda_destino_id' => $monedaNio->id,
        ],
        [
            'tasa' => 36.65,
            'fecha' => now()->format('Y-m-d'),
            'estado' => EstadoGeneral::Activo,
        ]
    );
});

test('calcula tasa de cambio y divide el saldo pendiente de una cuenta en partes iguales', function (): void {
    $monedaNio = Moneda::query()->where('codigo', 'NIO')->firstOrFail();

    $cuenta = Cuenta::query()->create([
        'codigo' => 'CTA-TEST-001',
        'numero_cuenta' => 'CTA-REST-001',
        'tipo' => TipoCuenta::RESTAURANTE_DIRECTO,
        'estado' => EstadoCuenta::ABIERTA,
        'moneda_id' => $monedaNio->id,
        'subtotal' => 1200.00,
        'total' => 1200.00,
        'monto_pagado' => 600.00, // Abono previo del 50%
        'saldo' => 600.00,
        'abierta_at' => now(),
    ]);

    $dividirCuenta = app(DividirCuenta::class);
    $partes = $dividirCuenta->ejecutar($cuenta, 2);

    expect($partes)->toHaveCount(2)
        ->and((float) $partes[0]['monto_total'])->toBe(300.00)
        ->and((float) $partes[1]['monto_total'])->toBe(300.00);

    $tasa = TasaCambio::obtenerTasa(now(), 'USD', 'NIO');
    expect($tasa)->toBe(36.65);
});
