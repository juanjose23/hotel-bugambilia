<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Politicas\UnidadAnticipacion;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Reservas\Habitaciones\CancelarReservaHabitacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Reservas\Reserva;

test('cancela reserva con penalización, reembolsa excedente y anula cuenta formalmente', function () {
    $persona = Persona::factory()->create();
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_TEST_3', 'nombre' => 'Tipo Test 3', 'estado' => 1]);
    $cat = Catalogo::query()->create(['codigo' => 'CAT_TEST_3', 'nombre' => 'Cat Test 3', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $cliente = Cliente::query()->create(['persona_id' => $persona->id, 'catalogo_id' => $cat->id, 'codigo_cliente' => 'CLI-TEST-3', 'estado' => 1]);
    $moneda = Moneda::query()->create(['codigo' => 'USD3', 'nombre' => 'Dólar 3', 'simbolo' => '$', 'es_predeterminada' => true]);

    /** @var Politica $politica */
    $politica = Politica::query()->create([
        'titulo' => 'Política 50%',
        'aplica_penalizacion' => true,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $politica->penalizaciones()->create([
        'min_unidades' => 0,
        'max_unidades' => 2,
        'unidad' => UnidadAnticipacion::DIAS->value,
        'porcentaje' => 50.0,
        'aplica_no_show' => false,
        'orden' => 1,
    ]);

    $ub = Ubicacion::query()->create(['nombre' => 'Ubicacion Test 3', 'tipo' => 1, 'estado' => 1]);

    /** @var Habitacion $habitacion */
    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-TEST-3',
        'nombre' => 'Habitación Test 3',
        'categoria_id' => $cat->id,
        'ubicacion_id' => $ub->id,
        'precio_base' => 200.00,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $habitacion->politicas()->attach($politica->id);

    /** @var Reserva $reserva */
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-3',
        'nombre_cliente' => 'Cliente Test 3',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => 200.00,
        'total_pagado' => 200.00,
        'saldo' => 0.00,
        'estado' => EstadoReserva::CONFIRMADA->value,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
    ]);

    /** @var Cuenta $cuenta */
    $cuenta = Cuenta::query()->create([
        'numero_cuenta' => 'CTA-TEST-1',
        'abierta_at' => now(),
        'reserva_id' => $reserva->id,
        'moneda_id' => $moneda->id,
        'subtotal' => 200.00,
        'total' => 200.00,
        'total_pagado' => 200.00,
        'saldo' => 0.00,
        'estado' => EstadoCuenta::ABIERTA->value,
    ]);

    $cuenta->pagos()->create([
        'forma_pago' => MetodoPago::EFECTIVO,
        'moneda_id' => $moneda->id,
        'monto' => 200.00,
        'estado' => EstadoPago::APLICADO,
    ]);

    $interactor = app(CancelarReservaHabitacion::class);
    $reservaCancelada = $interactor->ejecutar(new CancelarReservaHabitacionData(
        reservaId: $reserva->id,
        motivo: 'Cancelación por cliente'
    ));

    expect($reservaCancelada->estado)->toBe(EstadoReserva::CANCELADA)
        ->and((float) $reservaCancelada->total)->toBe(100.00)
        ->and((float) $reservaCancelada->saldo)->toBe(0.00);

    $cuenta->refresh();
    expect($cuenta->estado)->toBe(EstadoCuenta::ANULADA);
});
