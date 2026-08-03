<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Interactors\Cuentas\AnularPagoCuenta;
use App\Interactors\Cuentas\RegistrarDetalleCuenta;
use App\Interactors\Cuentas\RegistrarPagoCuenta;
use App\Repository\Models\Cuentas\Cuenta;

test('anula un pago correctamente y recalcula el saldo pendiente de la cuenta', function (): void {
    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-ANULAR-001',
        'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
    ]);

    app(RegistrarDetalleCuenta::class)->ejecutar(
        cuenta: $cuenta,
        concepto: 'Almuerzo Ejecutivo',
        precioUnitario: 400.0,
    );

    $pago = app(RegistrarPagoCuenta::class)->ejecutar(
        cuenta: $cuenta,
        metodoPago: MetodoPago::EFECTIVO,
        monto: 200.0,
    );

    expect((float) $cuenta->refresh()->total_pagado)->toBe(200.0);

    $cuentaActualizada = app(AnularPagoCuenta::class)->ejecutar(
        pago: $pago,
        motivo: 'Error de digitación en monto',
    );

    expect($pago->refresh()->estado)->toBe(EstadoPago::ANULADO)
        ->and((float) $cuentaActualizada->total_pagado)->toBe(0.0)
        ->and((float) $cuentaActualizada->saldo)->toBe((float) $cuentaActualizada->total);
});
