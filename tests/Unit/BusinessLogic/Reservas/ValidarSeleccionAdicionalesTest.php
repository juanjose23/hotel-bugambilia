<?php

declare(strict_types=1);

use App\BusinessLogic\Reservas\ValidarSeleccionAdicionales;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

function crearMonedaPredeterminada(): Moneda
{
    return Moneda::query()->firstOrCreate(
        ['codigo' => 'USD'],
        [
            'nombre' => 'Dolar Val',
            'simbolo' => '$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo->value,
        ]
    );
}

test('resuelve servicios adicionales correctamente', function (): void {
    $moneda = crearMonedaPredeterminada();

    Precio::query()->create([
        'moneda_id' => $moneda->id,
        'priceable_type' => Servicio::class,
        'priceable_id' => 10,
        'estado' => EstadoGeneral::Activo,
        'tipo_precio' => 'base',
        'precio' => 25.00,
        'fecha_inicio' => today(),
    ]);

    $validator = new ValidarSeleccionAdicionales(app(ObtenerTarifasReservaQuery::class));

    $solicitados = [
        ['servicio_id' => 10, 'cantidad' => 2],
    ];

    $resultado = $validator->resolverServicios($solicitados, null);

    expect($resultado)->toHaveCount(1);
    expect($resultado[0])->toBe([
        'servicio_id' => 10,
        'cantidad' => 2,
        'precio' => 25.00,
    ]);
});

test('lanza excepcion si el servicio principal se agrega como adicional', function (): void {
    $validator = new ValidarSeleccionAdicionales(app(ObtenerTarifasReservaQuery::class));

    $validator->resolverServicios([['servicio_id' => 5]], 5);
})->throws(InvalidArgumentException::class, 'El servicio principal no puede agregarse nuevamente como adicional.');

test('resuelve espacios adicionales y valida que la cantidad sea 1', function (): void {
    $moneda = crearMonedaPredeterminada();

    Precio::query()->create([
        'moneda_id' => $moneda->id,
        'priceable_type' => Espacio::class,
        'priceable_id' => 3,
        'estado' => EstadoGeneral::Activo,
        'tipo_precio' => 'base',
        'precio' => 150.00,
        'fecha_inicio' => today(),
    ]);

    $validator = new ValidarSeleccionAdicionales(app(ObtenerTarifasReservaQuery::class));

    $resultado = $validator->resolverEspacios([['espacio_id' => 3]], null);

    expect($resultado[0])->toBe([
        'espacio_id' => 3,
        'cantidad' => 1,
        'precio' => 150.00,
    ]);
});

test('lanza excepcion si la cantidad de un espacio no es 1', function (): void {
    $validator = new ValidarSeleccionAdicionales(app(ObtenerTarifasReservaQuery::class));

    $validator->resolverEspacios([['espacio_id' => 3, 'cantidad' => 2]], null);
})->throws(InvalidArgumentException::class, 'Cada espacio físico debe agregarse una sola vez.');

test('resuelve habitaciones adicionales correctamente', function (): void {
    $moneda = crearMonedaPredeterminada();

    Precio::query()->create([
        'moneda_id' => $moneda->id,
        'priceable_type' => Habitacion::class,
        'priceable_id' => 1,
        'estado' => EstadoGeneral::Activo,
        'tipo_precio' => 'base',
        'precio' => 120.00,
        'fecha_inicio' => today(),
    ]);

    $validator = new ValidarSeleccionAdicionales(app(ObtenerTarifasReservaQuery::class));

    $resultado = $validator->resolverHabitaciones([['habitacion_id' => 1]], null);

    expect($resultado[0])->toBe([
        'habitacion_id' => 1,
        'cantidad' => 1,
        'precio' => 120.00,
    ]);
});
