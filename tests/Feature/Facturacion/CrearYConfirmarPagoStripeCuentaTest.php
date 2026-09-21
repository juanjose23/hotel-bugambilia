<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Interactors\Facturacion\Stripe\ConfirmarPagoStripe;
use App\Interactors\Facturacion\Stripe\CrearIntentoPagoStripe;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use Database\Seeders\PasarelaPagoSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->seed(PasarelaPagoSeeder::class);

    config([
        'services.stripe' => [
            'enabled' => true,
            'key' => 'pk_test_12345',
            'secret' => 'sk_test_12345',
            'webhook_secret' => 'whsec_test_12345',
            'mode' => 'test',
        ],
    ]);
});

test('crea un PaymentIntent en Stripe y luego lo confirma acreditando el saldo a la cuenta', function (): void {
    Http::fake([
        'https://api.stripe.com/v1/customers*' => Http::response(['id' => 'cus_test_999'], 200),
        'https://api.stripe.com/v1/payment_intents' => Http::response([
            'id' => 'pi_test_checkout_123',
            'client_secret' => 'pi_test_checkout_123_secret_456',
            'amount' => 172500,
            'currency' => 'usd',
            'status' => 'requires_payment_method',
        ], 200),
        'https://api.stripe.com/v1/payment_intents/pi_test_checkout_123' => Http::response([
            'id' => 'pi_test_checkout_123',
            'amount' => 172500,
            'amount_received' => 172500,
            'currency' => 'usd',
            'status' => 'succeeded',
        ], 200),
    ]);

    $moneda = Moneda::query()->firstOrCreate(
        ['codigo' => 'USD'],
        [
            'nombre' => 'Dólar Estadounidense',
            'simbolo' => '$',
            'es_predeterminada' => false,
            'estado' => EstadoGeneral::Activo,
        ]
    );

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-STRIPE-001',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'moneda_id' => $moneda->id,
        'abierta_at' => now(),
    ]);

    app(RegistrarDetalleCuenta::class)->ejecutar(
        cuenta: $cuenta,
        concepto: 'Estancia Deluxe 2 Noches',
        precioUnitario: 1500.0,
    );

    $cuentaFresh = $cuenta->fresh();
    $saldoTotal = (float) $cuentaFresh->saldo;
    expect($saldoTotal)->toBe(1725.0);

    // 1. Crear Intento
    $resIntento = app(CrearIntentoPagoStripe::class)->ejecutarParaCuenta($cuentaFresh, $saldoTotal);

    expect($resIntento['payment_intent_id'])->toBe('pi_test_checkout_123')
        ->and($resIntento['client_secret'])->toBe('pi_test_checkout_123_secret_456')
        ->and($resIntento['transaccion']->estado)->toBe(EstadoTransaccionPago::Pendiente);

    // 2. Confirmar Pago Aprobado
    $resConfirmacion = app(ConfirmarPagoStripe::class)->ejecutarParaCuenta($cuentaFresh, 'pi_test_checkout_123');

    expect((float) $resConfirmacion['cuenta']->saldo)->toBe(0.0)
        ->and((float) $resConfirmacion['cuenta']->total_pagado)->toBe(1725.0)
        ->and((float) $resConfirmacion['pago']->monto)->toBe(1725.0)
        ->and($resConfirmacion['transaccion']->estado)->toBe(EstadoTransaccionPago::Capturada);
});
