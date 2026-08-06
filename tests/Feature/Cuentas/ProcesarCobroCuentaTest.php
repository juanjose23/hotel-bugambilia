<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Cobros\ProcesarCobroCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;

test('procesa el cobro de una cuenta correctamente sin error de tipo float/string', function (): void {
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
        'numero_cuenta' => 'CTA-COBRO-001',
        'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO,
        'estado' => EstadoCuenta::ABIERTA,
        'moneda_id' => $moneda->id,
        'abierta_at' => now(),
    ]);

    app(RegistrarDetalleCuenta::class)->ejecutar(
        cuenta: $cuenta,
        concepto: 'Plato Principal',
        precioUnitario: 500.0,
    );

    $cuentaFresh = $cuenta->fresh();
    $saldoACobrar = (float) $cuentaFresh->saldo;

    $resultado = app(ProcesarCobroCuenta::class)->ejecutar($cuentaFresh, null, [
        'forma_pago' => MetodoPago::EFECTIVO->value,
        'moneda_pago_id' => $moneda->id,
        'monto' => $saldoACobrar,
        'propina' => 0.0,
        'tipo_comprobante' => 'voucher',
    ]);

    expect($resultado['cerrada'])->toBeTrue()
        ->and((float) $resultado['saldo'])->toBe(0.0)
        ->and($resultado['cuenta']->estado)->toBe(EstadoCuenta::CERRADA);
});

test('calcula el vuelto y liquida la cuenta cuando el pago ingresado es mayor al saldo pendiente', function (): void {
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
        'numero_cuenta' => 'CTA-VUELTO-001',
        'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO,
        'estado' => EstadoCuenta::ABIERTA,
        'moneda_id' => $moneda->id,
        'abierta_at' => now(),
    ]);

    app(RegistrarDetalleCuenta::class)->ejecutar(
        cuenta: $cuenta,
        concepto: 'Consumo Restaurante',
        precioUnitario: 400.0,
    );

    $cuentaFresh = $cuenta->fresh();
    $saldo = (float) $cuentaFresh->saldo;

    // Se realiza un pago de 500.0 sobre una cuenta con saldo de 400.0 (o + cargos)
    $montoConExceso = $saldo + 100.0;

    $resultado = app(ProcesarCobroCuenta::class)->ejecutar($cuentaFresh, null, [
        'forma_pago' => MetodoPago::EFECTIVO->value,
        'moneda_pago_id' => $moneda->id,
        'monto' => $montoConExceso,
        'propina' => 0.0,
        'tipo_comprobante' => 'voucher',
    ]);

    expect($resultado['cerrada'])->toBeTrue()
        ->and((float) $resultado['saldo'])->toBe(0.0)
        ->and($resultado['cuenta']->estado)->toBe(EstadoCuenta::CERRADA);

    $ultimoPago = $resultado['cuenta']->pagos()->latest('id')->first();
    expect($ultimoPago)->not->toBeNull()
        ->and((float) $ultimoPago->monto)->toBe($saldo)
        ->and($ultimoPago->observaciones)->toContain('Vuelto: C$ 100.00');
});
