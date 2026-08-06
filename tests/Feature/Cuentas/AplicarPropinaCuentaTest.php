<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Interactors\Cuentas\Cobros\AplicarPropinaCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;

test('aplica y recalcula propina voluntaria del diez por ciento en una cuenta', function (): void {
    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-PROPINA-001',
        'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
    ]);

    app(RegistrarDetalleCuenta::class)->ejecutar(
        cuenta: $cuenta,
        concepto: 'Cena Especial',
        precioUnitario: 1000.0,
    );

    $cuentaConPropina = app(AplicarPropinaCuenta::class)->ejecutar(
        cuenta: $cuenta,
        porcentajeOMonto: 10.0,
        esPorcentaje: true,
    );

    expect((float) $cuentaConPropina->propina_total)->toBe(100.0)
        ->and((float) $cuentaConPropina->subtotal)->toBe(1000.0);
});
