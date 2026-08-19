<?php

declare(strict_types=1);

use App\Enums\Politicas\UnidadAnticipacion;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Events\Reservas\ReservaCancelada;
use App\Interactors\Reservas\Gestion\CancelarReserva;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Reservas\Reserva;

test('cancelar reserva por gestion dispara el evento ReservaCancelada una sola vez', function () {
    Event::fake([ReservaCancelada::class]);

    $persona = Persona::factory()->create();
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_DISPATCH', 'nombre' => 'Tipo Dispatch', 'estado' => 1]);
    $cat = Catalogo::query()->create(['codigo' => 'CAT_DISPATCH', 'nombre' => 'Cat Dispatch', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $cliente = Cliente::query()->create(['persona_id' => $persona->id, 'catalogo_id' => $cat->id, 'codigo_cliente' => 'CLI-DISPATCH', 'estado' => 1]);
    $moneda = Moneda::query()->create(['codigo' => 'USD', 'nombre' => 'Dólar Dispatch', 'simbolo' => '$', 'es_predeterminada' => true]);

    $politica = Politica::query()->create([
        'titulo' => 'Política Dispatch',
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

    $ub = Ubicacion::query()->create(['nombre' => 'Ubicacion Dispatch', 'tipo' => 1, 'estado' => 1]);

    /** @var Habitacion $habitacion */
    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-DISPATCH',
        'nombre' => 'Habitación Dispatch',
        'categoria_id' => $cat->id,
        'ubicacion_id' => $ub->id,
        'precio_base' => 200.00,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $habitacion->politicas()->attach($politica->id);

    /** @var Reserva $reserva */
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-DISPATCH',
        'nombre_cliente' => 'Cliente Dispatch',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => 200.00,
        'total_pagado' => 0.00,
        'saldo' => 200.00,
        'estado' => EstadoReserva::CONFIRMADA->value,
        'fecha_check_in' => now()->addDay()->toDateString(),
        'fecha_check_out' => now()->addDays(3)->toDateString(),
    ]);

    app(CancelarReserva::class)->ejecutar($reserva, null, 'Cancelación por cliente');

    Event::assertDispatchedTimes(ReservaCancelada::class, 1);
});
