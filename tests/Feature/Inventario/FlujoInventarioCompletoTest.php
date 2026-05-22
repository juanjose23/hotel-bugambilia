<?php

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Models\User;
use App\UseCases\Inventario\Movimientos\Mutations\TrasladarEntreBodegas;
use App\UseCases\Inventario\Recepciones\Mutations\RegistrarEntradaRecepcion;
use App\UseCases\Inventario\Services\PutawayPolicy;

beforeEach(function () {
    $ref = new ReflectionClass(PutawayPolicy::class);
    $ref->setStaticPropertyValue('cache', null);

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->almacen = Ubicacion::create([
        'nombre' => 'Almacén General',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);

    $this->bodegaPiso = Ubicacion::create([
        'nombre' => 'Bodega Piso 1',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);
});

it('ejecuta el flujo de recepción y traslado de inventario exitosamente', function () {
    // --- PASO 1: RECEPCION DE COMPRA ---
    $proveedor = Proveedor::factory()->create();
    $orden = OrdenCompra::factory()->create([
        'proveedor_id' => $proveedor->id,
        'estado' => EstadoOrdenCompra::Emitida,
    ]);
    $ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $orden->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 10.0,
    ]);
    $recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $orden->id,
    ]);
    $recepcionItem = RecepcionItem::factory()->create([
        'recepcion_id' => $recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'producto_id' => $this->producto->id,
        'cantidad_recibida' => 10.0,
    ]);

    $itemData = [
        'id' => $recepcionItem->id,
        'producto_id' => $this->producto->id,
        'producto_variante_id' => null,
        'cantidad_recibida' => 10.0,
        'cantidad_rechazada' => 0.0,
        'lote_proveedor' => 'LOTE-FLOW-10',
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'ubicacion_id' => $this->almacen->id,
    ];

    $registrar = app(RegistrarEntradaRecepcion::class);
    $registrar->execute(
        nuevoEstado: 'Completa',
        items: [$itemData],
        proveedorId: $proveedor->id,
        creadoPorId: $this->user->id
    );

    $lote = Lote::where('recepcion_item_id', $recepcionItem->id)->first();
    expect($lote)->not->toBeNull();
    expect($lote->cantidad_disponible)->toBe(10.0);

    $stockAlmacen = Stock::where([
        'producto_id' => $this->producto->id,
        'lote_id' => $lote->id,
        'ubicacion_id' => $this->almacen->id,
    ])->first();

    expect($stockAlmacen)->not->toBeNull();
    expect($stockAlmacen->cantidad)->toBe(10.0);
    // --- PASO 2: TRASLADO INTERNO ENTRE BODEGAS ---
    $trasladar = app(TrasladarEntreBodegas::class);
    $trasladar->execute(
        productoId: $this->producto->id,
        loteId: $lote->id,
        cantidad: 2.0,
        origenId: $this->almacen->id,
        destinoId: $this->bodegaPiso->id,
        creadoPorId: $this->user->id,
        referencia: 'Traslado inicial a Bodega Piso 1'
    );

    expect($stockAlmacen->fresh()->cantidad)->toBe(8.0);

    $stockBodega = Stock::where([
        'producto_id' => $this->producto->id,
        'lote_id' => $lote->id,
        'ubicacion_id' => $this->bodegaPiso->id,
    ])->first();

    expect($stockBodega)->not->toBeNull();
    expect($stockBodega->cantidad)->toBe(2.0);

    $movTraslado = MovimientoStock::where('tipo', 'TRASLADO')->first();
    expect($movTraslado)->not->toBeNull();
    expect($movTraslado->lote_id)->toBe($lote->id);
    expect($movTraslado->cantidad)->toBe(2.0);

});
