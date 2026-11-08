<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoPagoReserva;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Facturacion\Stripe\CobrarCuentaConTarjetaStripe;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\User;
use Database\Seeders\PasarelaPagoSeeder;
use Illuminate\Support\Facades\Http;

test('CobrarCuentaConTarjetaStripe procesa el cobro y descuenta el saldo de la cuenta', function (): void {
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

    Http::fake([
        'https://api.stripe.com/v1/payment_methods' => Http::response([
            'id' => 'pm_test_card_12345',
            'type' => 'card',
            'card' => [
                'brand' => 'visa',
                'last4' => '4242',
            ],
        ], 200),
        'https://api.stripe.com/v1/payment_intents' => Http::response([
            'id' => 'pi_test_confirmed_99999',
            'status' => 'succeeded',
            'amount' => 50000,
            'currency' => 'usd',
            'charges' => [
                'data' => [
                    [
                        'id' => 'ch_12345',
                        'payment_method_details' => [
                            'card' => [
                                'brand' => 'visa',
                                'last4' => '4242',
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();

    $moneda = Moneda::firstOrCreate(
        ['codigo' => 'USD'],
        ['nombre' => 'Dólar estadounidense', 'simbolo' => '$', 'tasa_cambio' => 1.0, 'activo' => true]
    );

    $persona = Persona::factory()->create(['primer_nombre' => 'Carlos']);
    PersonaNatural::create([
        'persona_id' => $persona->id,
        'primer_apellido' => 'Sánchez',
        'fecha_nacimiento' => '1988-03-15',
    ]);
    $tipoCat = CatalogoTipo::firstOrCreate(['codigo' => 'tipo_cliente'], ['nombre' => 'Tipo Cliente', 'estado' => 1]);
    $cat = Catalogo::firstOrCreate(['codigo' => 'REGULAR_STRIPE', 'catalogo_tipo_id' => $tipoCat->id], ['nombre' => 'Regular', 'estado' => 1]);

    $cliente = Cliente::create([
        'persona_id' => $persona->id,
        'catalogo_id' => $cat->id,
        'codigo_cliente' => 'CLI-STRIPE-001',
        'estado' => EstadoGeneral::Activo,
    ]);

    $recurso = RecursoReservable::create([
        'nombre' => 'Habitación 301 Reservable',
        'codigo' => 'REC-301',
        'tipo' => TipoRecursoReservable::HABITACION,
        'control_disponibilidad' => ControlDisponibilidad::FECHAS,
        'capacidad' => 2,
        'estado' => EstadoRecursoReservable::ACTIVO,
    ]);

    $habitacion = Habitacion::factory()->create([
        'numero' => '301',
        'nombre' => 'Suite Ejecutiva 301',
        'estado' => EstadoEspacio::Ocupado,
        'reservable_id' => $recurso->id,
    ]);

    $reserva = Reserva::create([
        'codigo_reserva' => 'RES-STRIPE-001',
        'cliente_id' => $cliente->id,
        'nombre_cliente' => 'Carlos Sánchez',
        'email_cliente' => 'carlos@hoteltest.com',
        'habitacion_id' => $habitacion->id,
        'moneda_id' => $moneda->id,
        'tipo_reserva' => TipoReserva::HABITACION,
        'estado' => EstadoReserva::CHECKED_IN,
        'tipo_pago' => TipoPagoReserva::PAGO_COMPLETO,
        'fecha_check_in' => now()->subDays(2),
        'fecha_check_out' => now(),
        'subtotal' => 500.0,
        'total' => 500.0,
        'total_pagado' => 0.0,
        'saldo' => 500.0,
        'adultos' => 2,
        'ninos' => 0,
    ]);

    $detalle = ReservaDetalle::create([
        'reserva_id' => $reserva->id,
        'reservable_id' => $recurso->id,
        'fecha_inicio' => now()->subDays(2),
        'fecha_fin' => now(),
        'cantidad_adultos' => 2,
        'cantidad_ninos' => 0,
        'precio_unitario' => 250.0,
        'subtotal' => 500.0,
        'impuestos' => 0.0,
        'total' => 500.0,
        'estado' => EstadoReservaDetalle::EN_USO,
    ]);

    $estancia = Estancia::create([
        'reserva_id' => $reserva->id,
        'reserva_detalle_id' => $detalle->id,
        'habitacion_id' => $habitacion->id,
        'usuario_check_in_id' => $user->id,
        'check_in_at' => now()->subDays(2),
        'fecha_entrada_programada' => now()->subDays(2),
        'fecha_salida_programada' => now(),
        'cantidad_llaves' => 2,
        'estado' => EstadoEstancia::ACTIVA,
    ]);

    $cuenta = Cuenta::create([
        'numero_cuenta' => 'CTA-STRIPE-001',
        'estancia_id' => $estancia->id,
        'reserva_id' => $reserva->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'tipo_cuenta' => TipoCuenta::ESTANCIA,
        'estado' => EstadoCuenta::ABIERTA,
        'abierta_at' => now(),
        'total' => 500.0,
        'total_pagado' => 0.0,
        'saldo' => 500.0,
    ]);

    $interactor = app(CobrarCuentaConTarjetaStripe::class);

    $resultado = $interactor->ejecutar(
        cuenta: $cuenta,
        monto: 500.0,
        datosTarjeta: [
            'numero' => '4242 4242 4242 4242',
            'exp_mes' => '12',
            'exp_anio' => (string) ((int) now()->format('Y') + 2),
            'cvc' => '123',
            'titular' => 'Carlos Sánchez',
        ],
        usuarioId: $user->id,
    );

    expect($resultado)->toHaveKeys(['pago', 'payment_intent_id', 'ultimos4', 'marca', 'monto'])
        ->and($resultado['ultimos4'])->toBe('4242')
        ->and($resultado['pago']->forma_pago)->toBe(MetodoPago::TARJETA_CREDITO)
        ->and((float) $resultado['pago']->monto)->toBe(500.0);

    $cuenta->refresh();
    expect((float) $cuenta->saldo)->toBe(0.0)
        ->and((float) $cuenta->total_pagado)->toBe(500.0);
});
