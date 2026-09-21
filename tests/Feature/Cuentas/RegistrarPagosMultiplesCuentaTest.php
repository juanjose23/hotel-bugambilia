<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Cobros\RegistrarPagosMultiplesCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;

test('registra multiples pagos divididos a una cuenta y reduce el saldo correctamente', function (): void {
    $moneda = Moneda::query()->firstOrCreate(
        ['codigo' => 'NIO'],
        [
            'nombre' => 'Córdoba Nicaragüense',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]
    );

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-SPLIT-001',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'moneda_id' => $moneda->id,
        'abierta_at' => now(),
    ]);

    app(RegistrarDetalleCuenta::class)->ejecutar(
        cuenta: $cuenta,
        concepto: 'Hospedaje 2 noches',
        precioUnitario: 3000.0,
    );

    $cuentaFresh = $cuenta->fresh();
    $saldoTotal = (float) $cuentaFresh->saldo;

    $resultado = app(RegistrarPagosMultiplesCuenta::class)->ejecutar(
        cuenta: $cuentaFresh,
        pagos: [
            [
                'forma_pago' => MetodoPago::EFECTIVO,
                'monto' => 1450.0,
                'propina' => 0.0,
                'referencia_transaccion' => 'EF-001',
                'observaciones' => 'Parte en efectivo',
            ],
            [
                'forma_pago' => MetodoPago::TARJETA_CREDITO,
                'monto' => 2000.0,
                'propina' => 100.0,
                'referencia_transaccion' => 'BAC-AUTH-1234',
                'observaciones' => 'Parte con tarjeta de crédito',
            ],
        ],
    );

    expect($resultado['pagos'])->toHaveCount(2)
        ->and($resultado['totalPagado'])->toBe($saldoTotal)
        ->and($resultado['saldoRestante'])->toBe(0.0)
        ->and((float) $resultado['cuenta']->saldo)->toBe(0.0)
        ->and((float) $resultado['cuenta']->total_pagado)->toBe($saldoTotal);
});
