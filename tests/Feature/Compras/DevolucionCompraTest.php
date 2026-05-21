<?php

use App\Enums\Compras\EstadoDevolucion;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\DevolucionCompra;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\User;
use App\UseCases\Compras\Devoluciones\Mutations\DevolverMercanciaProveedor;
use App\UseCases\Compras\Devoluciones\Mutations\GenerarCodigoDevolucion;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén General',
        'tipo' => 'zona',
        'estado' => 1,
    ]);
    $this->merma = Ubicacion::create([
        'nombre' => 'Zona de Merma',
        'tipo' => 'zona',
        'estado' => 1,
    ]);

    $this->orden = OrdenCompra::factory()->create([
        'estado' => EstadoOrdenCompra::Emitida,
    ]);

    $this->ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $this->orden->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 10,
    ]);

    $this->recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $this->orden->id,
    ]);

    $this->recepcionItem = RecepcionItem::factory()->create([
        'recepcion_id' => $this->recepcion->id,
        'orden_item_id' => $this->ordenItem->id,
        'producto_id' => $this->producto->id,
        'cantidad_recibida' => 10,
    ]);
});

it('puede generar un codigo correlativo de devolucion unico', function () {
    $codigo1 = app(GenerarCodigoDevolucion::class)->execute();
    expect($codigo1)->toMatch('/^DEV-\d{4}-\d{3}$/');

    DevolucionCompra::create([
        'codigo' => $codigo1,
        'orden_compra_id' => $this->orden->id,
        'fecha_devolucion' => now(),
        'estado' => EstadoDevolucion::Borrador,
        'motivo' => 'Motivo de prueba',
        'creado_por_id' => $this->user->id,
    ]);

    $codigo2 = app(GenerarCodigoDevolucion::class)->execute();
    expect($codigo2)->not->toBe($codigo1);
});

it('falla al confirmar devolucion si el lote tiene stock insuficiente', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-TEST-1',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 3, // Solo 3 disponibles
        'cantidad_inicial' => 10,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now(),
        'recepcion_item_id' => $this->recepcionItem->id,
    ]);

    $devolucion = DevolucionCompra::create([
        'codigo' => 'DEV-TEST-001',
        'orden_compra_id' => $this->orden->id,
        'fecha_devolucion' => now(),
        'estado' => EstadoDevolucion::Borrador,
        'motivo' => 'Stock insuficiente test',
        'creado_por_id' => $this->user->id,
    ]);

    $devolucion->items()->create([
        'lote_id' => $lote->id,
        'recepcion_item_id' => $this->recepcionItem->id,
        'producto_id' => $this->producto->id,
        'cantidad_devolver' => 5, // Intentamos devolver 5
    ]);

    expect(fn () => app(DevolverMercanciaProveedor::class)->execute($devolucion, $this->user->id))
        ->toThrow(RuntimeException::class, 'Stock insuficiente para devolver');
});

it('descuenta stock y libera saldo del PO al confirmar devolucion de lote disponible', function () {
    $lote = Lote::create([
        'codigo_lote' => 'LOT-TEST-2',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10,
        'cantidad_inicial' => 10,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now(),
        'recepcion_item_id' => $this->recepcionItem->id,
    ]);

    $devolucion = DevolucionCompra::create([
        'codigo' => 'DEV-TEST-002',
        'orden_compra_id' => $this->orden->id,
        'recepcion_compra_id' => $this->recepcion->id,
        'fecha_devolucion' => now(),
        'estado' => EstadoDevolucion::Borrador,
        'motivo' => 'Devolución exitosa disponible',
        'creado_por_id' => $this->user->id,
    ]);

    $item = $devolucion->items()->create([
        'lote_id' => $lote->id,
        'recepcion_item_id' => $this->recepcionItem->id,
        'producto_id' => $this->producto->id,
        'cantidad_devolver' => 4,
    ]);

    app(DevolverMercanciaProveedor::class)->execute($devolucion, $this->user->id);

    // Verificar stock del lote
    expect($lote->fresh()->cantidad_disponible)->toBe(6.00);

    // Verificar movimiento de stock
    $movimiento = MovimientoStock::where('lote_id', $lote->id)
        ->where('tipo', 'MOV_SALIDA')
        ->first();

    expect($movimiento)->not->toBeNull();
    expect($movimiento->cantidad)->toBe(4.00);
    expect($movimiento->ubicacion_origen_id)->toBe($this->ubicacion->id);
    expect($movimiento->ubicacion_destino_id)->toBeNull();

    // Verificar liberación de saldo de la orden
    $updatedItem = OrdenCompraItem::withSum('recepcionItems', 'cantidad_recibida')->find($this->ordenItem->id);
    expect((float) $updatedItem->recepcion_items_sum_cantidad_recibida)->toBe(6.00);

    // Verificar estado de la devolución
    expect($devolucion->fresh()->estado)->toBe(EstadoDevolucion::Confirmada);
});

it('no descuenta stock adicional pero registra salida fisica de lotes rechazados', function () {
    // Un lote rechazado tiene cantidad_disponible = 0 (se movió a la Zona de Merma)
    $lote = Lote::create([
        'codigo_lote' => 'LOT-RECH-1',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Rechazado,
        'cantidad_disponible' => 0,
        'cantidad_inicial' => 10,
        'ubicacion_id' => $this->merma->id,
        'fecha_recepcion' => now(),
        'recepcion_item_id' => $this->recepcionItem->id,
    ]);

    $devolucion = DevolucionCompra::create([
        'codigo' => 'DEV-TEST-003',
        'orden_compra_id' => $this->orden->id,
        'recepcion_compra_id' => $this->recepcion->id,
        'fecha_devolucion' => now(),
        'estado' => EstadoDevolucion::Borrador,
        'motivo' => 'Devolución física lote rechazado',
        'creado_por_id' => $this->user->id,
    ]);

    $item = $devolucion->items()->create([
        'lote_id' => $lote->id,
        'recepcion_item_id' => $this->recepcionItem->id,
        'producto_id' => $this->producto->id,
        'cantidad_devolver' => 10,
    ]);

    app(DevolverMercanciaProveedor::class)->execute($devolucion, $this->user->id);

    // La cantidad disponible del lote sigue siendo 0
    expect($lote->fresh()->cantidad_disponible)->toBe(0.00);
    expect($lote->fresh()->estado)->toBe(EstadoLote::Rechazado);

    // Se registra movimiento de stock tipo MOV_SALIDA
    $movimiento = MovimientoStock::where('lote_id', $lote->id)
        ->where('tipo', 'MOV_SALIDA')
        ->first();

    expect($movimiento)->not->toBeNull();
    expect($movimiento->cantidad)->toBe(10.00);
    expect($movimiento->ubicacion_origen_id)->toBe($this->merma->id); // Desde la Zona de Merma
    expect($movimiento->ubicacion_destino_id)->toBeNull();

    // Se ajusta la cantidad recibida en la Orden
    $updatedItem = OrdenCompraItem::withSum('recepcionItems', 'cantidad_recibida')->find($this->ordenItem->id);
    expect((float) $updatedItem->recepcion_items_sum_cantidad_recibida)->toBe(0.00);
});
