<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\CalcularPenalizacionCancelacion;
use App\Enums\Politicas\UnidadAnticipacion;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Reservas\Reserva;

test('calcula penalización gratuita cuando la cancelación se realiza con suficiente anticipación', function () {
    $persona = Persona::factory()->create();
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_TEST_1', 'nombre' => 'Tipo Test 1', 'estado' => 1]);
    $cat = Catalogo::query()->create(['codigo' => 'CAT_TEST_1', 'nombre' => 'Cat Test 1', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $cliente = Cliente::query()->create(['persona_id' => $persona->id, 'catalogo_id' => $cat->id, 'codigo_cliente' => 'CLI-TEST-1', 'estado' => 1]);
    $moneda = Moneda::query()->create(['codigo' => 'US1', 'nombre' => 'Dólar 1', 'simbolo' => '$', 'es_predeterminada' => true]);

    /** @var Politica $politica */
    $politica = Politica::query()->create([
        'titulo' => 'Política Test 1',
        'aplica_penalizacion' => true,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $politica->penalizaciones()->createMany([
        [
            'min_unidades' => 1,
            'max_unidades' => null,
            'unidad' => UnidadAnticipacion::DIAS->value,
            'porcentaje' => 0.0,
            'aplica_no_show' => false,
            'orden' => 1,
        ],
        [
            'min_unidades' => 0,
            'max_unidades' => 0,
            'unidad' => UnidadAnticipacion::DIAS->value,
            'porcentaje' => 100.0,
            'aplica_no_show' => false,
            'orden' => 2,
        ],
    ]);

    $ub = Ubicacion::query()->create(['nombre' => 'Ubicacion Test 1', 'tipo' => 1, 'estado' => 1]);

    /** @var Habitacion $habitacion */
    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-TEST-1',
        'nombre' => 'Habitación Test 1',
        'categoria_id' => $cat->id,
        'ubicacion_id' => $ub->id,
        'precio_base' => 200.00,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $habitacion->politicas()->attach($politica->id);

    /** @var Reserva $reserva */
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-1',
        'nombre_cliente' => 'Cliente Test 1',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => 200.00,
        'total_pagado' => 0.00,
        'saldo' => 200.00,
        'estado' => EstadoReserva::CONFIRMADA->value,
        'fecha_check_in' => now()->addDays(5)->toDateString(),
        'fecha_check_out' => now()->addDays(7)->toDateString(),
    ]);

    $calculador = app(CalcularPenalizacionCancelacion::class);
    $resultado = $calculador->ejecutar($reserva, now());

    expect($resultado->porcentaje)->toBe(0.0)
        ->and($resultado->monto)->toBe(0.0);
});

test('calcula 100% de penalización cuando la cancelación es el mismo día o no-show', function () {
    $persona = Persona::factory()->create();
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_TEST_2', 'nombre' => 'Tipo Test 2', 'estado' => 1]);
    $cat = Catalogo::query()->create(['codigo' => 'CAT_TEST_2', 'nombre' => 'Cat Test 2', 'estado' => 1, 'catalogo_tipo_id' => $tipo->id]);
    $cliente = Cliente::query()->create(['persona_id' => $persona->id, 'catalogo_id' => $cat->id, 'codigo_cliente' => 'CLI-TEST-2', 'estado' => 1]);
    $moneda = Moneda::query()->create(['codigo' => 'US2', 'nombre' => 'Dólar 2', 'simbolo' => '$', 'es_predeterminada' => true]);

    /** @var Politica $politica */
    $politica = Politica::query()->create([
        'titulo' => 'Política Test 2',
        'aplica_penalizacion' => true,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $politica->penalizaciones()->createMany([
        [
            'min_unidades' => null,
            'max_unidades' => null,
            'unidad' => UnidadAnticipacion::DIAS->value,
            'porcentaje' => 100.0,
            'aplica_no_show' => true,
            'orden' => 1,
        ],
    ]);

    $ub = Ubicacion::query()->create(['nombre' => 'Ubicacion Test 2', 'tipo' => 1, 'estado' => 1]);

    /** @var Habitacion $habitacion */
    $habitacion = Habitacion::query()->create([
        'codigo' => 'HAB-TEST-2',
        'nombre' => 'Habitación Test 2',
        'categoria_id' => $cat->id,
        'ubicacion_id' => $ub->id,
        'precio_base' => 150.00,
        'estado' => EstadoGeneral::Activo->value,
    ]);

    $habitacion->politicas()->attach($politica->id);

    /** @var Reserva $reserva */
    $reserva = Reserva::query()->create([
        'codigo_reserva' => 'RES-TEST-2',
        'nombre_cliente' => 'Cliente Test 2',
        'telefono_cliente' => '88888888',
        'habitacion_id' => $habitacion->id,
        'cliente_id' => $cliente->id,
        'moneda_id' => $moneda->id,
        'total' => 150.00,
        'total_pagado' => 0.00,
        'saldo' => 150.00,
        'estado' => EstadoReserva::CONFIRMADA->value,
        'fecha_check_in' => now()->toDateString(),
        'fecha_check_out' => now()->addDays(2)->toDateString(),
    ]);

    $calculador = app(CalcularPenalizacionCancelacion::class);
    $resultado = $calculador->ejecutar($reserva, now(), esNoShow: true);

    expect($resultado->porcentaje)->toBe(100.0)
        ->and($resultado->monto)->toBe(150.00);
});
