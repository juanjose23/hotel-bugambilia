<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Facturacion\EstadoConciliacionPago;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Habitaciones\CancelarReservaHabitacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Facturacion\PagoConciliacion;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Facturacion\PasarelaPago;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\Http;

test('cancelar con penalizacion igual al total pagado anula la cuenta sin reembolso', function (): void {
    $escenario = escenarioConciliacion(total: 100.0, pagado: 100.0);

    $interactor = app(CancelarReservaHabitacion::class);
    $reserva = $interactor->ejecutar(new CancelarReservaHabitacionData(
        reservaId: $escenario['reserva']->id,
        motivo: 'Penalización total',
        montoPenalizacion: 100.0,
    ));

    expect($reserva->estado)->toBe(EstadoReserva::CANCELADA);

    $escenario['cuenta']->refresh();
    expect($escenario['cuenta']->estado)->toBe(EstadoCuenta::ANULADA);
});

test('cancelar con reembolso completo marca conciliacion como reembolsada', function (): void {
    $escenario = escenarioConciliacion(total: 100.0, pagado: 100.0);

    config(['services.stripe.secret' => 'sk_test_mock']);

    Http::fake([
        'https://api.stripe.com/v1/refunds' => Http::response([
            'id' => 're_conciliacion_1',
            'status' => 'succeeded',
            'amount' => 10000,
        ], 200),
    ]);

    $interactor = app(CancelarReservaHabitacion::class);
    $interactor->ejecutar(new CancelarReservaHabitacionData(
        reservaId: $escenario['reserva']->id,
        motivo: 'Reembolso total',
        montoPenalizacion: 0.0,
    ));

    $escenario['transaccion']->refresh();
    expect($escenario['transaccion']->estado)->toBe(EstadoTransaccionPago::Reembolsada);

    $conciliacion = PagoConciliacion::query()
        ->where('pago_transaccion_id', $escenario['transaccion']->id)
        ->first();

    expect($conciliacion)->not->toBeNull()
        ->and($conciliacion->estado)->toBe(EstadoConciliacionPago::Reembolsada)
        ->and((float) $conciliacion->monto_recibido)->toBe(0.0);
});

test('cancelar con reembolso parcial marca conciliacion como diferencia', function (): void {
    $escenario = escenarioConciliacion(total: 100.0, pagado: 100.0);

    config(['services.stripe.secret' => 'sk_test_mock']);

    Http::fake([
        'https://api.stripe.com/v1/refunds' => Http::response([
            'id' => 're_conciliacion_parcial',
            'status' => 'succeeded',
            'amount' => 5000,
        ], 200),
    ]);

    $interactor = app(CancelarReservaHabitacion::class);
    $interactor->ejecutar(new CancelarReservaHabitacionData(
        reservaId: $escenario['reserva']->id,
        motivo: 'Reembolso parcial',
        montoPenalizacion: 50.0,
    ));

    $escenario['transaccion']->refresh();
    expect((float) $escenario['transaccion']->monto)->toBe(100.0);

    $conciliacion = PagoConciliacion::query()
        ->where('pago_transaccion_id', $escenario['transaccion']->id)
        ->first();

    expect($conciliacion)->not->toBeNull()
        ->and($conciliacion->estado)->toBe(EstadoConciliacionPago::Diferencia)
        ->and((float) $conciliacion->monto_recibido)->toBe(50.0);
});

test('cancelar reserva sin cuenta anula correctamente', function (): void {
    $moneda = Moneda::query()->create(['codigo' => 'NOC', 'nombre' => 'No Cuenta', 'simbolo' => '$', 'es_predeterminada' => true]);
    $persona = Persona::factory()->create();
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_NC', 'nombre' => 'Tipo NC', 'estado' => 1]);
    $cat = Catalogo::query()->create(['codigo' => 'CAT_NC', 'nombre' => 'Cat NC', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $cliente = Cliente::query()->create(['persona_id' => $persona->id, 'catalogo_id' => $cat->id, 'codigo_cliente' => 'CLI-NC', 'estado' => 1]);
    $ub = Ubicacion::query()->create(['nombre' => 'Ubic NC', 'tipo' => 1, 'estado' => 1]);
    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-NC',
        'nombre' => 'Hab NC',
        'categoria_id' => $cat->id,
        'ubicacion_id' => $ub->id,
        'precio_base' => 50.0,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-NC-001',
        'nombre_cliente' => 'Cliente NC',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => 50.0,
        'total_pagado' => 0.0,
        'saldo' => 50.0,
        'estado' => EstadoReserva::CONFIRMADA->value,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(7)->toDateString(),
    ]);

    $interactor = app(CancelarReservaHabitacion::class);
    $reservaCancelada = $interactor->ejecutar(new CancelarReservaHabitacionData(
        reservaId: $reserva->id,
        motivo: 'Sin cuenta',
        montoPenalizacion: 0.0,
    ));

    expect($reservaCancelada->estado)->toBe(EstadoReserva::CANCELADA);

    $cuenta = Cuenta::query()->where('reserva_id', $reserva->id)->first();
    expect($cuenta)->toBeNull();
});

function escenarioConciliacion(float $total, float $pagado): array
{
    $moneda = Moneda::query()->create(['codigo' => 'USD', 'nombre' => 'Dólar', 'simbolo' => '$', 'es_predeterminada' => true]);
    $persona = Persona::factory()->create();
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_CON', 'nombre' => 'Tipo Con', 'estado' => 1]);
    $cat = Catalogo::query()->create(['codigo' => 'CAT_CON', 'nombre' => 'Cat Con', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $cliente = Cliente::query()->create(['persona_id' => $persona->id, 'catalogo_id' => $cat->id, 'codigo_cliente' => 'CLI-CON', 'estado' => 1]);
    $ub = Ubicacion::query()->create(['nombre' => 'Ubic Con', 'tipo' => 1, 'estado' => 1]);
    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-CON',
        'nombre' => 'Hab Con',
        'categoria_id' => $cat->id,
        'ubicacion_id' => $ub->id,
        'precio_base' => $total,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-CON-'.str()->random(6),
        'nombre_cliente' => 'Cliente Con',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => $total,
        'total_pagado' => $pagado,
        'saldo' => round($total - $pagado, 2),
        'estado' => EstadoReserva::CONFIRMADA->value,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(7)->toDateString(),
    ]);

    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-CON-'.str()->random(4),
        'abierta_at' => now(),
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'subtotal' => $total,
        'total' => $total,
        'total_pagado' => $pagado,
        'saldo' => round($total - $pagado, 2),
        'estado' => EstadoCuenta::ABIERTA->value,
    ]);

    $pagoCuenta = $cuenta->pagos()->create([
        'forma_pago' => MetodoPago::TARJETA_CREDITO,
        'moneda_id' => $moneda->id,
        'monto' => $pagado,
        'estado' => EstadoPago::APLICADO,
        'referencia_transaccion' => 'pi_con_'.str()->random(6),
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
        'referencia_interna' => 'REF-CON-001',
        'idempotency_key' => 'idem-con-001',
        'referencia_pasarela' => $pagoCuenta->referencia_transaccion,
        'monto' => $pagado,
        'estado' => EstadoTransaccionPago::Capturada,
        'capturada_at' => now(),
    ]);

    PagoConciliacion::query()->create([
        'pago_transaccion_id' => $transaccion->id,
        'estado' => EstadoConciliacionPago::Conciliada,
        'monto_esperado' => $pagado,
        'monto_recibido' => $pagado,
        'diferencia' => 0.0,
        'conciliada_at' => now(),
    ]);

    return [
        'reserva' => $reserva,
        'cuenta' => $cuenta,
        'transaccion' => $transaccion,
    ];
}
