<?php

declare(strict_types=1);

use App\BusinessLogic\Facturacion\Stripe\ReintentarOperacionStripe;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Shared\EstadoGeneral;
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
use App\Repository\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->app->instance(
        ReintentarOperacionStripe::class,
        new ReintentarOperacionStripe(esperaMilisegundos: 0),
    );
});

test('el propietario cancela via web service y Stripe reembolsa sin pendientes', function (): void {
    $escenario = escenarioReservaPaga();

    config(['services.stripe.secret' => 'sk_test_mock_secret']);

    Http::fake([
        'https://api.stripe.com/v1/refunds' => Http::response([
            'id' => 're_test_ok_1',
            'status' => 'succeeded',
            'amount' => 10000,
        ], 200),
    ]);

    $response = $this->actingAs($escenario['usuario'])
        ->postJson(route('web-services.reservas.cancelar', $escenario['reserva']));

    $response->assertOk()
        ->assertJsonPath('codigo_reserva', $escenario['reserva']->codigo_reserva)
        ->assertJsonPath('estado', EstadoReserva::CANCELADA->value)
        ->assertJsonPath('reembolso.pendiente_administracion', false)
        ->assertJsonPath('message', 'Tu reserva fue cancelada correctamente.');

    expect($escenario['reserva']->fresh()?->estado)->toBe(EstadoReserva::CANCELADA);

    $escenario['transaccion']->refresh();
    expect($escenario['transaccion']->estado)->toBe(EstadoTransaccionPago::Reembolsada);

    $escenario['cuenta']->refresh();
    expect($escenario['cuenta']->estado)->toBe(EstadoCuenta::ANULADA);
});

test('Stripe caido: reintenta tres veces y luego cancela avisando contactar a administracion', function (): void {
    $escenario = escenarioReservaPaga();

    config(['services.stripe.secret' => 'sk_test_mock_secret']);

    Http::fake([
        'https://api.stripe.com/*' => Http::response([
            'error' => ['message' => 'La conexion con Stripe no esta disponible'],
        ], 500),
    ]);

    $response = $this->actingAs($escenario['usuario'])
        ->postJson(route('web-services.reservas.cancelar', $escenario['reserva']));

    $response->assertOk()
        ->assertJsonPath('codigo_reserva', $escenario['reserva']->codigo_reserva)
        ->assertJsonPath('estado', EstadoReserva::CANCELADA->value)
        ->assertJsonPath('reembolso.pendiente_administracion', true)
        ->assertJsonPath('reembolso.intentos_stripe', 3)
        ->assertJsonPath('message', 'Tu reserva fue cancelada, pero no pudimos procesar el reembolso automaticamente. Por favor contacta a la administracion para resolver tu reembolso.');

    expect($escenario['reserva']->fresh()?->estado)->toBe(EstadoReserva::CANCELADA);

    $metaDatos = $escenario['reserva']->fresh()?->ultimaEntradaBitacora('cancelacion') ?? [];
    $pendientes = $metaDatos['reembolsos_pendientes_administracion'] ?? [];
    expect($pendientes)
        ->toHaveCount(1)
        ->and((float) ($pendientes[0]['monto'] ?? 0))->toBe(100.0);

    $escenario['transaccion']->refresh();
    expect($escenario['transaccion']->estado)->toBe(EstadoTransaccionPago::Capturada);
});

test('Stripe caido dos veces y al tercer intento reembolsa', function (): void {
    $escenario = escenarioReservaPaga();

    config(['services.stripe.secret' => 'sk_test_mock_secret']);

    Http::fake([
        'https://api.stripe.com/v1/refunds' => Http::sequence()
            ->push(['error' => ['message' => 'Conexion caida']], 500)
            ->push(['error' => ['message' => 'Conexion caida']], 500)
            ->push(['id' => 're_test_ok_3', 'status' => 'succeeded', 'amount' => 10000], 200),
        'https://api.stripe.com/*' => Http::response([
            'error' => ['message' => 'La conexion con Stripe no esta disponible'],
        ], 500),
    ]);

    $response = $this->actingAs($escenario['usuario'])
        ->postJson(route('web-services.reservas.cancelar', $escenario['reserva']));

    $response->assertOk()
        ->assertJsonPath('estado', EstadoReserva::CANCELADA->value)
        ->assertJsonPath('reembolso.pendiente_administracion', false)
        ->assertJsonPath('reembolso.intentos_stripe', 3);

    $escenario['transaccion']->refresh();
    expect($escenario['transaccion']->estado)->toBe(EstadoTransaccionPago::Reembolsada);
});

test('otro cliente no puede cancelar una reserva ajena via web service', function (): void {
    $escenario = escenarioReservaPaga();
    $otroCliente = User::factory()->create();

    $this->actingAs($otroCliente)
        ->postJson(route('web-services.reservas.cancelar', $escenario['reserva']))
        ->assertForbidden();

    expect($escenario['reserva']->fresh()?->estado)->toBe(EstadoReserva::CONFIRMADA);
});

test('un visitante no puede cancelar una reserva via web service', function (): void {
    $escenario = escenarioReservaPaga();

    $this->post(route('web-services.reservas.cancelar', $escenario['reserva']))
        ->assertRedirect(route('login'));

    expect($escenario['reserva']->fresh()?->estado)->toBe(EstadoReserva::CONFIRMADA);
});

/**
 * @return array{usuario: User, reserva: Reserva, cuenta: Cuenta, transaccion: PagoTransaccion}
 */
function escenarioReservaPaga(): array
{
    $moneda = Moneda::query()->create([
        'codigo' => 'USD',
        'nombre' => 'Dólar',
        'simbolo' => '$',
        'es_predeterminada' => true,
    ]);

    $persona = Persona::factory()->create();
    $usuario = User::factory()->create(['persona_id' => $persona->id]);

    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_WS', 'nombre' => 'Tipo WS', 'estado' => 1]);
    $catalogo = Catalogo::query()->create([
        'codigo' => 'CAT_WS',
        'nombre' => 'Cat WS',
        'estado' => 1,
        'catalogo_tipo_id' => $tipo->id,
    ]);

    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'codigo_cliente' => 'CLI-WS-001',
        'estado' => 1,
    ]);

    $ubicacion = Ubicacion::query()->create(['nombre' => 'Ubicacion WS', 'tipo' => 1, 'estado' => 1]);

    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-WS-001',
        'nombre' => 'Habitación WS',
        'categoria_id' => $catalogo->id,
        'ubicacion_id' => $ubicacion->id,
        'precio_base' => 100.00,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-WS-'.str()->random(8),
        'nombre_cliente' => 'Cliente WS',
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
        'numero_cuenta' => 'CTA-WS-001',
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
        'referencia_transaccion' => 'pi_test_ws_'.str()->random(6),
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
        'referencia_interna' => 'REF-WS-001',
        'idempotency_key' => 'idem-ws-001',
        'referencia_pasarela' => $pagoCuenta->referencia_transaccion,
        'monto' => 100.00,
        'estado' => EstadoTransaccionPago::Capturada,
        'capturada_at' => now(),
    ]);

    return [
        'usuario' => $usuario,
        'reserva' => $reserva,
        'cuenta' => $cuenta,
        'transaccion' => $transaccion,
    ];
}
