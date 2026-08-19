<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Gestion\CancelarReservaPorPagoFallido;
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
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;

test('cancela reserva pendiente cuando pago stripe falla', function (): void {
    $escenario = escenarioReservaPendiente();

    $interactor = app(CancelarReservaPorPagoFallido::class);
    $interactor->ejecutar($escenario['transaccion']);

    expect($escenario['reserva']->fresh()->estado)->toBe(EstadoReserva::CANCELADA);
    expect((float) $escenario['reserva']->fresh()->total)->toBe(0.0);
    expect((float) $escenario['reserva']->fresh()->total_pagado)->toBe(0.0);
    expect((float) $escenario['reserva']->fresh()->saldo)->toBe(0.0);

    $escenario['cuenta']->refresh();
    expect($escenario['cuenta']->estado)->toBe(EstadoCuenta::ANULADA);

    $bitacora = $escenario['reserva']->fresh()->ultimaEntradaBitacora('cancelacion');
    expect($bitacora)->not->toBeNull()
        ->and($bitacora['motivo'])->toBe('Pago rechazado por pasarela de cobro')
        ->and($bitacora['politica']['monto_penalizacion'])->toBe(0)
        ->and($bitacora['monto_reembolso'])->toBe(0);
});

test('no cancela reserva que ya esta confirmada cuando pago stripe falla', function (): void {
    $escenario = escenarioReservaConfirmada();

    $interactor = app(CancelarReservaPorPagoFallido::class);
    $interactor->ejecutar($escenario['transaccion']);

    expect($escenario['reserva']->fresh()->estado)->toBe(EstadoReserva::CONFIRMADA);

    $escenario['cuenta']->refresh();
    expect($escenario['cuenta']->estado)->toBe(EstadoCuenta::ABIERTA);
});

test('no cancela reserva que ya esta cancelada cuando pago stripe falla', function (): void {
    $escenario = escenarioReservaYaCancelada();

    $interactor = app(CancelarReservaPorPagoFallido::class);
    $interactor->ejecutar($escenario['transaccion']);

    expect($escenario['reserva']->fresh()->estado)->toBe(EstadoReserva::CANCELADA);
});

test('cancela reserva pendiente sin cuenta abierta', function (): void {
    $moneda = Moneda::query()->create([
        'codigo' => 'USN',
        'nombre' => 'Dolar Sin Cta',
        'simbolo' => '$',
        'es_predeterminada' => true,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-SIN-CTA-'.str()->random(6),
        'nombre_cliente' => 'Cliente Sin Cta',
        'telefono_cliente' => '88888888',
        'moneda_id' => $moneda->id,
        'total' => 100.00,
        'total_pagado' => 0,
        'saldo' => 100.00,
        'estado' => EstadoReserva::PENDIENTE,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(7)->toDateString(),
    ]);

    $pasarela = PasarelaPago::query()->create([
        'codigo' => 'stripe',
        'nombre' => 'Stripe',
        'activa' => true,
    ]);

    $transaccion = PagoTransaccion::query()->create([
        'pasarela_pago_id' => $pasarela->id,
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'moneda_base_id' => $moneda->id,
        'referencia_interna' => 'REF-NO-CTA-001',
        'referencia_pasarela' => 'pi_test_no_cta_'.str()->random(6),
        'idempotency_key' => 'idem-no-cta-001',
        'monto' => 100.00,
        'estado' => EstadoTransaccionPago::Pendiente,
    ]);

    $interactor = app(CancelarReservaPorPagoFallido::class);
    $interactor->ejecutar($transaccion);

    expect($reserva->fresh()->estado)->toBe(EstadoReserva::CANCELADA);
    expect((float) $reserva->fresh()->total)->toBe(0.0);
    expect((float) $reserva->fresh()->total_pagado)->toBe(0.0);
    expect((float) $reserva->fresh()->saldo)->toBe(0.0);
});

test('cancela reserva pendiente con detalle adicional', function (): void {
    $escenario = escenarioReservaPendiente();
    $reserva = $escenario['reserva'];

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Habitación Test Detalle',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => 1,
        'estado' => 1,
        'capacidad' => 2,
    ]);

    ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'tipo_recurso' => TipoRecursoReservable::HABITACION,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now()->addDays(5)->toDateString(),
        'fecha_fin' => now()->addDays(7)->toDateString(),
        'precio_unitario' => 50.00,
        'subtotal' => 100.00,
        'total' => 100.00,
        'estado' => EstadoReservaDetalle::PENDIENTE,
    ]);

    $interactor = app(CancelarReservaPorPagoFallido::class);
    $interactor->ejecutar($escenario['transaccion']);

    expect($reserva->fresh()->estado)->toBe(EstadoReserva::CANCELADA);

    $detalles = $reserva->fresh()->detalles;
    foreach ($detalles as $detalle) {
        expect($detalle->estado)->toBe(EstadoReservaDetalle::CANCELADO);
    }
});

test('cancela reserva y crea entrada en bitacora', function (): void {
    $escenario = escenarioReservaPendiente();

    $interactor = app(CancelarReservaPorPagoFallido::class);
    $interactor->ejecutar($escenario['transaccion']);

    expect($escenario['reserva']->fresh()->estado)->toBe(EstadoReserva::CANCELADA);

    $bitacora = $escenario['reserva']->fresh()->ultimaEntradaBitacora('cancelacion');
    expect($bitacora)->not->toBeNull()
        ->and($bitacora['motivo'])->toBe('Pago rechazado por pasarela de cobro');
});

test('transaccion sin reserva no causa error', function (): void {
    $moneda = Moneda::query()->create([
        'codigo' => 'USR',
        'nombre' => 'Dolar Sin Reserva',
        'simbolo' => '$',
        'es_predeterminada' => true,
    ]);

    $pasarela = PasarelaPago::query()->create([
        'codigo' => 'stripe',
        'nombre' => 'Stripe',
        'activa' => true,
    ]);

    $transaccion = PagoTransaccion::query()->create([
        'pasarela_pago_id' => $pasarela->id,
        'moneda_id' => $moneda->id,
        'moneda_base_id' => $moneda->id,
        'referencia_interna' => 'REF-NO-RES-001',
        'referencia_pasarela' => 'pi_test_no_res_'.str()->random(6),
        'idempotency_key' => 'idem-no-res-001',
        'monto' => 50.00,
        'estado' => EstadoTransaccionPago::Pendiente,
    ]);

    $interactor = app(CancelarReservaPorPagoFallido::class);
    $interactor->ejecutar($transaccion);

    expect(true)->toBeTrue();
});

// ── Helpers ────────────────────────────────────────────────────

/**
 * @return array{reserva: Reserva, cuenta: Cuenta, transaccion: PagoTransaccion}
 */
function escenarioReservaPendiente(): array
{
    $moneda = Moneda::query()->create([
        'codigo' => 'USD',
        'nombre' => 'Dólar',
        'simbolo' => '$',
        'es_predeterminada' => true,
    ]);

    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_PFP', 'nombre' => 'Tipo PFP', 'estado' => 1]);
    $catalogo = Catalogo::query()->create([
        'codigo' => 'CAT_PFP',
        'nombre' => 'Cat PFP',
        'estado' => 1,
        'catalogo_tipo_id' => $tipo->id,
    ]);

    $persona = Persona::factory()->create();
    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'codigo_cliente' => 'CLI-PFP-001',
        'estado' => 1,
    ]);

    $ubicacion = Ubicacion::query()->create(['nombre' => 'Ubicacion PFP', 'tipo' => 1, 'estado' => 1]);

    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-PFP-001',
        'nombre' => 'Habitación PFP',
        'categoria_id' => $catalogo->id,
        'ubicacion_id' => $ubicacion->id,
        'precio_base' => 100.00,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $recurso = RecursoReservable::query()->create([
        'nombre' => 'Recurso PFP',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => 1,
        'estado' => 1,
        'capacidad' => 2,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-PFP-'.str()->random(8),
        'nombre_cliente' => 'Cliente PFP',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => 100.00,
        'total_pagado' => 0,
        'saldo' => 100.00,
        'estado' => EstadoReserva::PENDIENTE,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(7)->toDateString(),
    ]);

    ReservaDetalle::query()->create([
        'reserva_id' => $reserva->id,
        'tipo_recurso' => TipoRecursoReservable::HABITACION,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now()->addDays(5)->toDateString(),
        'fecha_fin' => now()->addDays(7)->toDateString(),
        'precio_unitario' => 100.00,
        'subtotal' => 100.00,
        'total' => 100.00,
        'estado' => EstadoReservaDetalle::PENDIENTE,
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-PFP-001',
        'abierta_at' => now(),
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'subtotal' => 100.00,
        'total' => 100.00,
        'total_pagado' => 0,
        'saldo' => 100.00,
        'estado' => EstadoCuenta::ABIERTA->value,
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
        'referencia_interna' => 'REF-PFP-001',
        'idempotency_key' => 'idem-pfp-001',
        'referencia_pasarela' => 'pi_test_pfp_'.str()->random(6),
        'monto' => 100.00,
        'estado' => EstadoTransaccionPago::Pendiente,
    ]);

    return [
        'reserva' => $reserva,
        'cuenta' => $cuenta,
        'transaccion' => $transaccion,
    ];
}

/**
 * @return array{reserva: Reserva, cuenta: Cuenta, transaccion: PagoTransaccion}
 */
function escenarioReservaConfirmada(): array
{
    $moneda = Moneda::query()->create([
        'codigo' => 'USC',
        'nombre' => 'Dólar Confirmada',
        'simbolo' => '$',
        'es_predeterminada' => true,
    ]);

    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_CFMP', 'nombre' => 'Tipo CFMP', 'estado' => 1]);
    $catalogo = Catalogo::query()->create([
        'codigo' => 'CAT_CFMP',
        'nombre' => 'Cat CFMP',
        'estado' => 1,
        'catalogo_tipo_id' => $tipo->id,
    ]);

    $persona = Persona::factory()->create();
    $cliente = Cliente::query()->create([
        'persona_id' => $persona->id,
        'catalogo_id' => $catalogo->id,
        'codigo_cliente' => 'CLI-CFMP-001',
        'estado' => 1,
    ]);

    $ubicacion = Ubicacion::query()->create(['nombre' => 'Ubicacion CFMP', 'tipo' => 1, 'estado' => 1]);

    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-CFMP-001',
        'nombre' => 'Habitación CFMP',
        'categoria_id' => $catalogo->id,
        'ubicacion_id' => $ubicacion->id,
        'precio_base' => 100.00,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CFMP-'.str()->random(8),
        'nombre_cliente' => 'Cliente CFMP',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => 100.00,
        'total_pagado' => 100.00,
        'saldo' => 0.00,
        'estado' => EstadoReserva::CONFIRMADA,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(7)->toDateString(),
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-CFMP-001',
        'abierta_at' => now(),
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'subtotal' => 100.00,
        'total' => 100.00,
        'total_pagado' => 100.00,
        'saldo' => 0.00,
        'estado' => EstadoCuenta::ABIERTA->value,
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
        'referencia_interna' => 'REF-CFMP-001',
        'idempotency_key' => 'idem-cfmp-001',
        'referencia_pasarela' => 'pi_test_cfmp_'.str()->random(6),
        'monto' => 100.00,
        'estado' => EstadoTransaccionPago::Capturada,
        'capturada_at' => now(),
    ]);

    return [
        'reserva' => $reserva,
        'cuenta' => $cuenta,
        'transaccion' => $transaccion,
    ];
}

/**
 * @return array{reserva: Reserva, transaccion: PagoTransaccion}
 */
function escenarioReservaYaCancelada(): array
{
    $moneda = Moneda::query()->create([
        'codigo' => 'USX',
        'nombre' => 'Dólar Cancelada',
        'simbolo' => '$',
        'es_predeterminada' => true,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CANC-'.str()->random(8),
        'nombre_cliente' => 'Cliente Cancelada',
        'telefono_cliente' => '88888888',
        'moneda_id' => $moneda->id,
        'total' => 100.00,
        'total_pagado' => 0,
        'saldo' => 100.00,
        'estado' => EstadoReserva::CANCELADA,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(7)->toDateString(),
    ]);

    $pasarela = PasarelaPago::query()->create([
        'codigo' => 'stripe',
        'nombre' => 'Stripe',
        'activa' => true,
    ]);

    $transaccion = PagoTransaccion::query()->create([
        'pasarela_pago_id' => $pasarela->id,
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'moneda_base_id' => $moneda->id,
        'referencia_interna' => 'REF-CANC-001',
        'referencia_pasarela' => 'pi_test_canc_'.str()->random(6),
        'idempotency_key' => 'idem-canc-001',
        'monto' => 100.00,
        'estado' => EstadoTransaccionPago::Fallida,
    ]);

    return [
        'reserva' => $reserva,
        'transaccion' => $transaccion,
    ];
}
