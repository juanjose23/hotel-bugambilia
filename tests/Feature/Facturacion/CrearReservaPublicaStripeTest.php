<?php

declare(strict_types=1);

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\HabitacionSeeder;
use Database\Seeders\MonedaSeeder;
use Database\Seeders\PaisSeeder;
use Database\Seeders\TasaCambioSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->seed([
        PaisSeeder::class,
        MonedaSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
        TasaCambioSeeder::class,
        HabitacionSeeder::class,
    ]);

    config([
        'services.stripe' => [
            'enabled' => true,
            'key' => 'pk_test_123',
            'secret' => 'sk_test_123',
            'webhook_secret' => 'whsec_test_123',
            'mode' => 'test',
        ],
    ]);
});

test('si Stripe falla al crear la reserva publica no se crea nada (rollback)', function (): void {
    Http::fake([
        'https://api.stripe.com/v1/customers*' => Http::response(['id' => 'cus_test_123'], 200),
        'https://api.stripe.com/v1/payment_intents' => Http::response([
            'error' => [
                'type' => 'invalid_request_error',
                'message' => 'Su tarjeta fue rechazada.',
            ],
        ], 402),
    ]);

    $habitacion = Habitacion::query()->firstOrFail();

    $response = $this->postJson(route('reservas.crear'), [
        'nombre_cliente' => 'Cliente Stripe Fallo',
        'telefono_cliente' => '+505 8888 1234',
        'email_cliente' => 'stripe-fallo@ejemplo.com',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => now()->addDays(30)->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(32)->format('Y-m-d'),
        'adultos' => 2,
        'tipo_pago_reserva' => TipoPagoReserva::ABONO_50->value,
        'canal_pago_reserva' => 'stripe',
    ]);

    $response->assertStatus(502)
        ->assertJsonPath('error', 'stripe_api_error')
        ->assertJsonPath('message', 'No se pudo conectar con Stripe. No se creo la reserva.');

    $this->assertDatabaseMissing('reservas', ['email_cliente' => 'stripe-fallo@ejemplo.com']);
    $this->assertDatabaseCount('cuentas', 0);
    $this->assertDatabaseCount('pago_transacciones', 0);
});

test('crea la reserva pendiente y la transaccion de Stripe cuando la pasarela responde', function (): void {
    Http::fake([
        'https://api.stripe.com/v1/customers*' => Http::response(['id' => 'cus_test_456'], 200),
        'https://api.stripe.com/v1/payment_intents' => Http::response([
            'id' => 'pi_test_456',
            'client_secret' => 'pi_test_456_secret',
            'status' => 'requires_payment_method',
        ], 200),
    ]);

    $habitacion = Habitacion::query()->firstOrFail();

    $response = $this->postJson(route('reservas.crear'), [
        'nombre_cliente' => 'Cliente Stripe Ok',
        'telefono_cliente' => '+505 8888 5678',
        'email_cliente' => 'stripe-ok@ejemplo.com',
        'tipo_reserva' => TipoReserva::HABITACION->value,
        'habitacion_id' => $habitacion->id,
        'fecha_check_in' => now()->addDays(35)->format('Y-m-d'),
        'fecha_check_out' => now()->addDays(37)->format('Y-m-d'),
        'adultos' => 2,
        'tipo_pago_reserva' => TipoPagoReserva::ABONO_50->value,
        'canal_pago_reserva' => 'stripe',
    ]);

    $response->assertOk()
        ->assertJsonPath('requiere_pago_stripe', true)
        ->assertJsonPath('stripe_pago.client_secret', 'pi_test_456_secret')
        ->assertJsonPath('stripe_pago.publishable_key', 'pk_test_123')
        ->assertJsonPath('stripe_pago.moneda', 'NIO');

    $reserva = Reserva::query()->where('email_cliente', 'stripe-ok@ejemplo.com')->firstOrFail();

    expect($reserva->estado)->toBe(EstadoReserva::PENDIENTE)
        ->and($reserva->tipo_pago)->toBe(TipoPagoReserva::ABONO_50)
        ->and((float) $reserva->total_pagado)->toBe(0.0)
        ->and($reserva->cuentas)->not->toBeEmpty();

    $this->assertDatabaseHas('pago_transacciones', [
        'reserva_id' => $reserva->id,
        'referencia_pasarela' => 'pi_test_456',
        'estado' => EstadoTransaccionPago::Pendiente->value,
    ]);
});
