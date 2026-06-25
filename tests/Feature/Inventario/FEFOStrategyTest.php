<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\UseCases\Inventario\Services\FEFOStrategy;

beforeEach(function () {
    $this->producto = Producto::factory()->create();
    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén General',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);

    $this->strategy = app(FEFOStrategy::class);
});

it('selecciona el unico lote cuando hay suficiente cantidad', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-UNICO',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 100.0,
        'cantidad_inicial' => 100.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
    ]);

    $lotes = Lote::whereIn('id', [$lote->id])->get();
    $resultado = $this->strategy->seleccionarLotes($lotes, 40.0);

    expect($resultado)->toHaveCount(1);
    expect($resultado[0]['lote']->id)->toBe($lote->id);
    expect($resultado[0]['cantidad'])->toBe(40.0);
});

it('selecciona de lotes ordenados por fecha de vencimiento (FEFO)', function () {
    $loteLejano = Lote::create([
        'codigo_lote' => 'LOT-LEJANO',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 20.0,
        'cantidad_inicial' => 20.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
    ]);

    $loteProximo = Lote::create([
        'codigo_lote' => 'LOT-PROXIMO',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10.0,
        'cantidad_inicial' => 10.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonth()->toDateString(),
    ]);

    $lotes = Lote::whereIn('id', [$loteLejano->id, $loteProximo->id])->get();
    $resultado = $this->strategy->seleccionarLotes($lotes, 15.0);

    expect($resultado)->toHaveCount(2);
    expect($resultado[0]['lote']->id)->toBe($loteProximo->id);
    expect($resultado[0]['cantidad'])->toBe(10.0);
    expect($resultado[1]['lote']->id)->toBe($loteLejano->id);
    expect($resultado[1]['cantidad'])->toBe(5.0);
});

it('coloca lotes sin fecha de vencimiento al final', function () {
    $loteConVenc = Lote::create([
        'codigo_lote' => 'LOT-CON-FECHA',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10.0,
        'cantidad_inicial' => 10.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
    ]);

    $loteSinVenc = Lote::create([
        'codigo_lote' => 'LOT-SIN-FECHA',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10.0,
        'cantidad_inicial' => 10.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => null,
    ]);

    $lotes = Lote::whereIn('id', [$loteSinVenc->id, $loteConVenc->id])->get();
    $resultado = $this->strategy->seleccionarLotes($lotes, 15.0);

    expect($resultado)->toHaveCount(2);
    expect($resultado[0]['lote']->id)->toBe($loteConVenc->id);
    expect($resultado[1]['lote']->id)->toBe($loteSinVenc->id);
});

it('toma solo la cantidad necesaria de un lote', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-GRANDE',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 100.0,
        'cantidad_inicial' => 100.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonths(3)->toDateString(),
    ]);

    $lotes = Lote::where('id', $lote->id)->get();
    $resultado = $this->strategy->seleccionarLotes($lotes, 30.0);

    expect($resultado)->toHaveCount(1);
    expect($resultado[0]['cantidad'])->toBe(30.0);
});

it('retorna arreglo vacio cuando la coleccion de lotes esta vacia', function () {
    $lotes = Lote::whereRaw('1 = 0')->get();
    $resultado = $this->strategy->seleccionarLotes($lotes, 10.0);

    expect($resultado)->toBe([]);
});

it('retorna todos los lotes cuando no hay suficiente cantidad total', function () {
    $lote1 = Lote::create([
        'codigo_lote' => 'LOT-1',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 5.0,
        'cantidad_inicial' => 5.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonth()->toDateString(),
    ]);

    $lote2 = Lote::create([
        'codigo_lote' => 'LOT-2',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 3.0,
        'cantidad_inicial' => 3.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonths(2)->toDateString(),
    ]);

    $lotes = Lote::whereIn('id', [$lote1->id, $lote2->id])->get();
    $resultado = $this->strategy->seleccionarLotes($lotes, 15.0);

    expect($resultado)->toHaveCount(2);
    expect($resultado[0]['cantidad'])->toBe(5.0);
    expect($resultado[1]['cantidad'])->toBe(3.0);
});
