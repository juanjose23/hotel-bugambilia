<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Habitaciones\CancelarReservaHabitacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Facturacion\PasarelaPago;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\Http;

test('webhook charge.refunded procesa y marca la transacción y el pago como reembolsados', function () {
    $moneda = Moneda::query()->create(['codigo' => 'USD', 'nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => true]);
    $persona = Persona::factory()->create();
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_TEST_R1', 'nombre' => 'Tipo R1', 'estado' => 1]);
    $cat = Catalogo::query()->create(['codigo' => 'CAT_TEST_R1', 'nombre' => 'Cat R1', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $cliente = Cliente::query()->create(['persona_id' => $persona->id, 'catalogo_id' => $cat->id, 'codigo_cliente' => 'CLI-TEST-R1', 'estado' => 1]);
    $ub = Ubicacion::query()->create(['nombre' => 'Ubicacion Test R1', 'tipo' => 1, 'estado' => 1]);

    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-TEST-R1',
        'nombre' => 'Habitación R1',
        'categoria_id' => $cat->id,
        'ubicacion_id' => $ub->id,
        'precio_base' => 100.00,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-R1',
        'nombre_cliente' => 'Cliente R1',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => 100.00,
        'total_pagado' => 100.00,
        'saldo' => 0.00,
        'estado' => EstadoReserva::CONFIRMADA->value,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(7)->toDateString(),
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-TEST-R1',
        'abierta_at' => now(),
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'subtotal' => 100.00,
        'total' => 100.00,
        'total_pagado' => 100.00,
        'saldo' => 0.00,
        'estado' => EstadoCuenta::ABIERTA->value,
    ]);

    $pagoCuenta = $cuenta->pagos()->create([
        'forma_pago' => MetodoPago::TARJETA_CREDITO,
        'moneda_id' => $moneda->id,
        'monto' => 100.00,
        'estado' => EstadoPago::APLICADO,
        'referencia_transaccion' => 'pi_test_refund_123',
    ]);

    $pasarela = PasarelaPago::query()->create([
        'codigo' => 'stripe',
        'nombre' => 'Stripe',
        'activa' => true,
    ]);

    $transaccion = PagoTransaccion::query()->create([
        'pasarela_pago_id' => $pasarela->id,
        'cuenta_id' => $cuenta->id,
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'moneda_base_id' => $moneda->id,
        'pago_cuenta_id' => $pagoCuenta->id,
        'referencia_interna' => 'REF-INT-R1',
        'idempotency_key' => 'idem-test-r1',
        'referencia_pasarela' => 'pi_test_refund_123',
        'monto' => 100.00,
        'estado' => EstadoTransaccionPago::Capturada,
        'capturada_at' => now(),
    ]);

    $secret = 'whsec_test_secret_key_123';
    config(['services.stripe.webhook_secret' => $secret]);

    $payloadArr = [
        'id' => 'evt_test_refund_123',
        'type' => 'charge.refunded',
        'data' => [
            'object' => [
                'id' => 'ch_test_123',
                'payment_intent' => 'pi_test_refund_123',
                'amount' => 10000,
                'amount_refunded' => 10000,
                'refunded' => true,
            ],
        ],
    ];

    $payloadJson = json_encode($payloadArr, JSON_UNESCAPED_SLASHES);
    $time = time();
    $signature = hash_hmac('sha256', $time.'.'.$payloadJson, $secret);

    $response = $this->call(
        method: 'POST',
        uri: '/stripe/webhook',
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$time},v1={$signature}",
        ],
        content: $payloadJson
    );

    $response->assertStatus(200)
        ->assertJson([
            'received' => true,
            'event' => 'charge.refunded',
            'reembolsada' => true,
        ]);

    $transaccion->refresh();
    expect($transaccion->estado)->toBe(EstadoTransaccionPago::Reembolsada);

    $pagoCuenta->refresh();
    expect($pagoCuenta->estado)->toBe(EstadoPago::REEMBOLSADO);
});

test('cancelar reserva con pago Stripe procesa reembolso real', function () {
    $moneda = Moneda::query()->create(['codigo' => 'USD2', 'nombre' => 'Dólar 2', 'simbolo' => '$', 'es_predeterminada' => true]);
    $persona = Persona::factory()->create();
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_TEST_R2', 'nombre' => 'Tipo R2', 'estado' => 1]);
    $cat = Catalogo::query()->create(['codigo' => 'CAT_TEST_R2', 'nombre' => 'Cat R2', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $cliente = Cliente::query()->create(['persona_id' => $persona->id, 'catalogo_id' => $cat->id, 'codigo_cliente' => 'CLI-TEST-R2', 'estado' => 1]);
    $ub = Ubicacion::query()->create(['nombre' => 'Ubicacion Test R2', 'tipo' => 1, 'estado' => 1]);

    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-TEST-R2',
        'nombre' => 'Habitación R2',
        'categoria_id' => $cat->id,
        'ubicacion_id' => $ub->id,
        'precio_base' => 200.00,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-R2',
        'nombre_cliente' => 'Cliente R2',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => 200.00,
        'total_pagado' => 200.00,
        'saldo' => 0.00,
        'estado' => EstadoReserva::CONFIRMADA->value,
        'fecha_check_in' => now()->addDays(10)->toDateString(),
        'fecha_check_out' => now()->addDays(12)->toDateString(),
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-TEST-R2',
        'abierta_at' => now(),
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'subtotal' => 200.00,
        'total' => 200.00,
        'total_pagado' => 200.00,
        'saldo' => 0.00,
        'estado' => EstadoCuenta::ABIERTA->value,
    ]);

    $pagoCuenta = $cuenta->pagos()->create([
        'forma_pago' => MetodoPago::TARJETA_CREDITO,
        'moneda_id' => $moneda->id,
        'monto' => 200.00,
        'estado' => EstadoPago::APLICADO,
        'referencia_transaccion' => 'pi_test_refund_456',
    ]);

    $pasarela = PasarelaPago::query()->create([
        'codigo' => 'stripe',
        'nombre' => 'Stripe',
        'activa' => true,
    ]);

    $transaccion = PagoTransaccion::query()->create([
        'pasarela_pago_id' => $pasarela->id,
        'cuenta_id' => $cuenta->id,
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'moneda_base_id' => $moneda->id,
        'pago_cuenta_id' => $pagoCuenta->id,
        'referencia_interna' => 'REF-INT-R2',
        'idempotency_key' => 'idem-test-r2',
        'referencia_pasarela' => 'pi_test_refund_456',
        'monto' => 200.00,
        'estado' => EstadoTransaccionPago::Capturada,
        'capturada_at' => now(),
    ]);

    config(['services.stripe.secret' => 'sk_test_mock_secret']);

    Http::fake([
        'https://api.stripe.com/v1/refunds' => Http::response([
            'id' => 're_test_456',
            'status' => 'succeeded',
            'amount' => 20000,
        ], 200),
    ]);

    $cancelarInteractor = app(CancelarReservaHabitacion::class);
    $reservaCancelada = $cancelarInteractor->ejecutar(new CancelarReservaHabitacionData(
        reservaId: $reserva->id,
        motivo: 'Cancelación anticipada'
    ));

    expect($reservaCancelada->estado)->toBe(EstadoReserva::CANCELADA);

    $transaccion->refresh();
    expect($transaccion->estado)->toBe(EstadoTransaccionPago::Reembolsada);

    $cuenta->refresh();
    expect($cuenta->estado)->toBe(EstadoCuenta::ANULADA);
});
