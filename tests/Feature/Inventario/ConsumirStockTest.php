<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Models\User;
use App\UseCases\Inventario\Movimientos\Mutations\ConsumirStock;
use App\UseCases\Inventario\Services\PutawayPolicy;

beforeEach(function () {
    // Reset static cache in PutawayPolicy to prevent test pollution
    $ref = new ReflectionClass(PutawayPolicy::class);
    $ref->setStaticPropertyValue('cache', null);

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén General',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);
});

it('puede consumir stock de un solo lote disponible usando la estrategia FEFO', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-001',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 100.0,
        'cantidad_inicial' => 100.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
    ]);

    $stock = Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $lote->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 100.0,
    ]);

    $consumir = app(ConsumirStock::class);
    $resultado = $consumir->execute(
        productoId: $this->producto->id,
        cantidadRequerida: 40.0,
        ubicacionId: $this->ubicacion->id,
        tipoMovimiento: 'MOV_SALIDA',
        creadoPorId: $this->user->id
    );

    expect($resultado)->toHaveCount(1);
    expect($resultado[0]['lote_id'])->toBe($lote->id);
    expect($resultado[0]['cantidad'])->toBe(40.0);

    // Verificar actualización del lote
    expect($lote->fresh()->cantidad_disponible)->toBe(60.0);
    expect($lote->fresh()->estado)->toBe(EstadoLote::Disponible);

    // Verificar stock local
    expect($stock->fresh()->cantidad)->toBe(60.0);

    // Verificar movimiento de stock registrado
    $movimiento = MovimientoStock::where('lote_id', $lote->id)->first();
    expect($movimiento)->not->toBeNull();
    expect($movimiento->tipo)->toBe('MOV_SALIDA');
    expect($movimiento->cantidad)->toBe(-40.0);
    expect($movimiento->creado_por_id)->toBe($this->user->id);
});

it('puede consumir stock de multiples lotes y agotar el primero', function () {
    // Lote 1: Vence en 1 mes (debe consumirse primero y agotarse)
    $lote1 = Lote::create([
        'codigo_lote' => 'LOT-001',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 15.0,
        'cantidad_inicial' => 20.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonth()->toDateString(),
    ]);

    $stock1 = Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $lote1->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 15.0,
    ]);

    // Lote 2: Vence en 3 meses (debe consumirse el sobrante)
    $lote2 = Lote::create([
        'codigo_lote' => 'LOT-002',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 30.0,
        'cantidad_inicial' => 30.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonths(3)->toDateString(),
    ]);

    $stock2 = Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $lote2->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 30.0,
    ]);

    $consumir = app(ConsumirStock::class);
    $resultado = $consumir->execute(
        productoId: $this->producto->id,
        cantidadRequerida: 25.0,
        ubicacionId: $this->ubicacion->id,
        tipoMovimiento: 'MOV_SALIDA',
        creadoPorId: $this->user->id
    );

    expect($resultado)->toHaveCount(2);

    // Lote 1 totalmente consumido (15.0)
    expect($resultado[0]['lote_id'])->toBe($lote1->id);
    expect($resultado[0]['cantidad'])->toBe(15.0);
    expect($lote1->fresh()->cantidad_disponible)->toBe(0.0);
    expect($lote1->fresh()->estado)->toBe(EstadoLote::Agotado);
    expect($stock1->fresh())->toBeNull(); // Se debió eliminar al quedar en 0

    // Lote 2 parcialmente consumido (10.0)
    expect($resultado[1]['lote_id'])->toBe($lote2->id);
    expect($resultado[1]['cantidad'])->toBe(10.0);
    expect($lote2->fresh()->cantidad_disponible)->toBe(20.0);
    expect($lote2->fresh()->estado)->toBe(EstadoLote::Disponible);
    expect($stock2->fresh()->cantidad)->toBe(20.0);
});

it('prioriza lotes segun FEFO (fecha de vencimiento mas proxima primero y null al final)', function () {
    // Lote con vencimiento lejano
    $loteLejano = Lote::create([
        'codigo_lote' => 'LOT-LEJANO',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10.0,
        'cantidad_inicial' => 10.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
    ]);

    Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $loteLejano->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 10.0,
    ]);

    // Lote con vencimiento proximo
    $loteProximo = Lote::create([
        'codigo_lote' => 'LOT-PROXIMO',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10.0,
        'cantidad_inicial' => 10.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addWeeks(2)->toDateString(),
    ]);

    Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $loteProximo->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 10.0,
    ]);

    // Lote sin fecha de vencimiento
    $loteNull = Lote::create([
        'codigo_lote' => 'LOT-NULL',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10.0,
        'cantidad_inicial' => 10.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => null,
    ]);

    Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $loteNull->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 10.0,
    ]);

    $consumir = app(ConsumirStock::class);
    $resultado = $consumir->execute(
        productoId: $this->producto->id,
        cantidadRequerida: 25.0,
        ubicacionId: $this->ubicacion->id,
        tipoMovimiento: 'MOV_SALIDA',
        creadoPorId: $this->user->id
    );

    expect($resultado)->toHaveCount(3);
    // Primero el proximo
    expect($resultado[0]['lote_id'])->toBe($loteProximo->id);
    // Luego el lejano
    expect($resultado[1]['lote_id'])->toBe($loteLejano->id);
    // Al final el null
    expect($resultado[2]['lote_id'])->toBe($loteNull->id);
});

it('falla si no hay stock suficiente en total', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-001',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10.0,
        'cantidad_inicial' => 10.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $lote->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 10.0,
    ]);

    $consumir = app(ConsumirStock::class);

    expect(fn () => $consumir->execute($this->producto->id, 15.0, $this->ubicacion->id, 'MOV_SALIDA'))
        ->toThrow(RuntimeException::class, 'Stock insuficiente');
});

it('ignora lotes en cuarentena, agotados, rechazados o vencidos', function () {
    // Lote en cuarentena
    $loteCuar = Lote::create([
        'codigo_lote' => 'LOT-CUAR',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Cuarentena,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $loteCuar->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 50.0,
    ]);

    // Lote vencido
    $loteVenc = Lote::create([
        'codigo_lote' => 'LOT-VENC',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Vencido,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $loteVenc->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 50.0,
    ]);

    $consumir = app(ConsumirStock::class);

    expect(fn () => $consumir->execute($this->producto->id, 10.0, $this->ubicacion->id, 'MOV_SALIDA'))
        ->toThrow(RuntimeException::class, 'Stock insuficiente');
});

it('ignora lotes que han sobrepasado su fecha de vencimiento', function () {
    // Lote caducado hace 1 día
    $loteCad = Lote::create([
        'codigo_lote' => 'LOT-CADUCADO',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->subMonths(6)->toDateString(),
        'fecha_vencimiento' => now()->subDay()->toDateString(),
    ]);

    Stock::create([
        'producto_id' => $this->producto->id,
        'lote_id' => $loteCad->id,
        'ubicacion_id' => $this->ubicacion->id,
        'cantidad' => 50.0,
    ]);

    $consumir = app(ConsumirStock::class);

    expect(fn () => $consumir->execute($this->producto->id, 10.0, $this->ubicacion->id, 'MOV_SALIDA'))
        ->toThrow(RuntimeException::class, 'Stock insuficiente');
});
