<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Clientes\ObtenerDetalleReservaPortal;
use App\Interactors\Clientes\ObtenerReservasCliente;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Pais;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use Database\Seeders\Configuracion\CatalogoSeeder;
use Database\Seeders\Configuracion\CatalogoTipoSeeder;
use Database\Seeders\Configuracion\MonedaSeeder;
use Database\Seeders\Configuracion\PaisSeeder;
use Database\Seeders\PasarelaPagoSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->seed([
        PaisSeeder::class,
        MonedaSeeder::class,
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        PasarelaPagoSeeder::class,
    ]);

    config([
        'services.stripe' => [
            'enabled' => true,
            'key' => 'pk_test_sal_12345',
            'secret' => 'sk_test_sal_12345',
            'webhook_secret' => 'whsec_test_sal_12345',
            'mode' => 'test',
        ],
    ]);
});

test('genera url_pago_saldo valida con codigo en el portal cuando hay saldo pendiente', function (): void {
    $pais = Pais::query()->firstOrFail();
    $persona = Persona::factory()->create(['pais_id' => $pais->id]);
    $catalogo = Catalogo::query()->firstOrFail();
    $moneda = Moneda::query()->where('es_predeterminada', true)->firstOrFail();

    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'codigo_cliente' => 'CLI-001',
        'estado' => EstadoGeneral::Activo,
    ]);

    $user = User::query()->create([
        'name' => 'Juan Perez',
        'email' => 'juan.perez@ejemplo.com',
        'password' => bcrypt('password'),
        'persona_id' => $persona->id,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-PORTAL-01',
        'cliente_id' => $cliente->id,
        'nombre_cliente' => 'Juan Perez',
        'email_cliente' => 'juan.perez@ejemplo.com',
        'telefono_cliente' => '88889999',
        'tipo_reserva' => TipoReserva::HABITACION,
        'estado' => EstadoReserva::CONFIRMADA,
        'tipo_pago' => TipoPagoReserva::ABONO_50,
        'moneda_id' => $moneda->id,
        'total' => 250.00,
        'total_pagado' => 125.00,
        'saldo' => 125.00,
        'fecha_check_in' => now()->addDays(2)->toDateString(),
        'fecha_check_out' => now()->addDays(4)->toDateString(),
        'adultos' => 2,
        'ninos' => 0,
    ]);

    $detallePortal = app(ObtenerDetalleReservaPortal::class)->ejecutar($reserva->id);

    expect($detallePortal['url_pago_saldo'])->not->toBeNull()
        ->and($detallePortal['url_pago_saldo'])->toContain('codigo=RES-TEST-PORTAL-01')
        ->and($detallePortal['saldo'])->toBe(125.0);

    $this->actingAs($user);
    $listaReservas = app(ObtenerReservasCliente::class)->ejecutar();
    $reservaLista = collect($listaReservas)->firstWhere('id', $reserva->id);

    expect($reservaLista)->not->toBeNull()
        ->and($reservaLista['url_pago_saldo'])->not->toBeNull()
        ->and($reservaLista['url_pago_saldo'])->toContain('codigo=RES-TEST-PORTAL-01');
});

test('permite crear intento y confirmar pago de saldo restante con Stripe en reserva checked_in', function (): void {
    Http::fake([
        'https://api.stripe.com/v1/customers*' => Http::response(['id' => 'cus_test_saldo_99'], 200),
        'https://api.stripe.com/v1/payment_intents' => Http::response([
            'id' => 'pi_test_saldo_789',
            'client_secret' => 'pi_test_saldo_789_secret_abc',
            'amount' => 12000,
            'currency' => 'usd',
            'status' => 'requires_payment_method',
        ], 200),
        'https://api.stripe.com/v1/payment_intents/pi_test_saldo_789' => Http::response([
            'id' => 'pi_test_saldo_789',
            'amount' => 12000,
            'amount_received' => 12000,
            'currency' => 'usd',
            'status' => 'succeeded',
        ], 200),
    ]);

    $pais = Pais::query()->firstOrFail();
    $persona = Persona::factory()->create(['pais_id' => $pais->id]);
    $catalogo = Catalogo::query()->firstOrFail();
    $moneda = Moneda::query()->where('es_predeterminada', true)->firstOrFail();

    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'codigo_cliente' => 'CLI-002',
        'estado' => EstadoGeneral::Activo,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CHECKEDIN-88',
        'cliente_id' => $cliente->id,
        'nombre_cliente' => 'Carlos Mendoza',
        'email_cliente' => 'carlos@ejemplo.com',
        'tipo_reserva' => TipoReserva::HABITACION,
        'estado' => EstadoReserva::CHECKED_IN,
        'tipo_pago' => TipoPagoReserva::ABONO_50,
        'moneda_id' => $moneda->id,
        'total' => 276.00,
        'total_pagado' => 156.00,
        'saldo' => 120.00,
        'fecha_check_in' => now()->toDateString(),
        'fecha_check_out' => now()->addDays(2)->toDateString(),
        'adultos' => 2,
        'ninos' => 0,
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-RES-88',
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'reserva_id' => $reserva->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'subtotal' => 240.00,
        'total' => 276.00,
        'total_pagado' => 156.00,
        'saldo' => 120.00,
        'abierta_at' => now(),
    ]);

    $cuenta->pagos()->create([
        'forma_pago' => MetodoPago::TARJETA_CREDITO,
        'moneda_id' => $moneda->id,
        'estado' => EstadoPago::APLICADO,
        'monto' => 156.00,
    ]);

    // 1. Crear intento de pago para la reserva checked-in
    $responseIntento = $this->postJson('/pagos/stripe/reservas/intento', [
        'reserva_id' => $reserva->id,
        'codigo_reserva' => $reserva->codigo_reserva,
        'monto' => 120.00,
    ]);

    $responseIntento->assertOk()
        ->assertJsonPath('client_secret', 'pi_test_saldo_789_secret_abc')
        ->assertJsonPath('monto', 120);

    // 2. Confirmar pago desde el cliente
    $responseConfirmar = $this->postJson('/pagos/stripe/reservas/confirmar', [
        'reserva_id' => $reserva->id,
        'codigo_reserva' => $reserva->codigo_reserva,
        'payment_intent_id' => 'pi_test_saldo_789',
    ]);

    $responseConfirmar->assertOk()
        ->assertJsonPath('lifecycle.saldo_reserva', '0.00')
        ->assertJsonPath('lifecycle.total_pagado_reserva', '276.00');

    // Verificar en BD que el saldo de la reserva es 0 y sigue en CHECKED_IN
    $reservaFresh = $reserva->fresh();
    expect((float) $reservaFresh->saldo)->toBe(0.0)
        ->and((float) $reservaFresh->total_pagado)->toBe(276.0)
        ->and($reservaFresh->estado)->toBe(EstadoReserva::CHECKED_IN)
        ->and($reservaFresh->tipo_pago)->toBe(TipoPagoReserva::PAGO_COMPLETO);

    // Verificar que la cuenta de estancia se actualizó con el abono
    $cuentaFresh = $cuenta->fresh();
    expect((float) $cuentaFresh->saldo)->toBe(0.0)
        ->and((float) $cuentaFresh->total_pagado)->toBe(276.0);
});
