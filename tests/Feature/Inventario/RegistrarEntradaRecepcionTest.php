<?php

use App\Enums\Activos\EstadoIndividualizacion;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Inventario\EstadoLote;
use App\Models\Activos\Activo;
use App\Models\Activos\RegistroIndividualizacion;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\User;
use App\UseCases\Inventario\Recepciones\Mutations\RegistrarEntradaRecepcion;
use App\UseCases\Inventario\Services\PutawayPolicy;

beforeEach(function () {
    // Reset static cache in PutawayPolicy to prevent test pollution
    $ref = new ReflectionClass(PutawayPolicy::class);
    $ref->setStaticPropertyValue('cache', null);

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén Principal',
        'tipo' => 'zona',
        'estado' => 1,
    ]);

    $this->proveedor = Proveedor::factory()->create();

    $this->orden = OrdenCompra::factory()->create([
        'proveedor_id' => $this->proveedor->id,
        'estado' => EstadoOrdenCompra::Emitida,
    ]);

    $this->ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $this->orden->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 100.0,
    ]);

    $this->recepcion = RecepcionCompra::factory()->create([
        'orden_compra_id' => $this->orden->id,
    ]);

    $this->recepcionItem = RecepcionItem::factory()->create([
        'recepcion_id' => $this->recepcion->id,
        'orden_item_id' => $this->ordenItem->id,
        'producto_id' => $this->producto->id,
        'cantidad_recibida' => 100.0,
    ]);
});

it('registra automaticamente los activos fijos al recepcionar un producto tipo 3', function () {
    $activoProducto = Producto::create([
        'categoria_id' => $this->producto->categoria_id,
        'nombre' => 'Televisor Samsung 55',
        'tipo' => 3,
        'estado' => 1,
    ]);

    $ordenItem = OrdenCompraItem::factory()->create([
        'orden_compra_id' => $this->orden->id,
        'producto_id' => $activoProducto->id,
        'cantidad' => 5,
    ]);

    $recepcionItem = RecepcionItem::factory()->create([
        'recepcion_id' => $this->recepcion->id,
        'orden_item_id' => $ordenItem->id,
        'producto_id' => $activoProducto->id,
        'cantidad_recibida' => 5.0,
        'cantidad_rechazada' => 0.0,
        'lote_proveedor' => 'SERIE-XYZ',
    ]);

    app(RegistrarEntradaRecepcion::class)->execute(
        nuevoEstado: 'Completa',
        items: [
            [
                'id' => $recepcionItem->id,
                'producto_id' => $activoProducto->id,
                'producto_variante_id' => null,
                'cantidad_recibida' => 5.0,
                'cantidad_rechazada' => 0.0,
                'lote_proveedor' => 'SERIE-XYZ',
                'fecha_vencimiento' => null,
            ],
        ],
        proveedorId: $this->proveedor->id,
        creadoPorId: $this->user->id,
    );

    $this->assertDatabaseMissing((new Lote)->getTable(), [
        'producto_id' => $activoProducto->id,
    ]);

    $this->assertDatabaseHas((new RegistroIndividualizacion)->getTable(), [
        'recepcion_item_id' => $recepcionItem->id,
        'producto_id' => $activoProducto->id,
        'cantidad_total' => 5,
        'cantidad_registrada' => 5,
        'estado' => EstadoIndividualizacion::Completado->value,
    ]);

    $registro = RegistroIndividualizacion::where('recepcion_item_id', $recepcionItem->id)->first();
    expect($registro)->not->toBeNull();

    $activos = Activo::where('individualizacion_id', $registro->id)->get();
    expect($activos)->toHaveCount(5);
    expect($activos->every(fn (Activo $activo) => $activo->producto_id === $activoProducto->id))->toBeTrue();
});

it('registra entrada de recepcion completa en estado Disponible', function () {
    $item = [
        'id' => $this->recepcionItem->id,
        'producto_id' => $this->producto->id,
        'producto_variante_id' => null,
        'cantidad_recibida' => 10.0,
        'cantidad_rechazada' => 0.0,
        'lote_proveedor' => 'PROV-LOT-ABC',
        'fecha_vencimiento' => now()->addYear()->toDateString(),
    ];

    $registrar = app(RegistrarEntradaRecepcion::class);
    $registrar->execute(
        nuevoEstado: 'Completa',
        items: [$item],
        proveedorId: $this->proveedor->id,
        creadoPorId: $this->user->id
    );

    // Verificar lote creado
    $lote = Lote::where('recepcion_item_id', $this->recepcionItem->id)->first();
    expect($lote)->not->toBeNull();
    expect($lote->codigo_lote)->toBe('PROV-LOT-ABC');
    expect($lote->estado)->toBe(EstadoLote::Disponible);
    expect($lote->cantidad_disponible)->toBe(10.0);
    expect($lote->ubicacion_id)->toBe($this->ubicacion->id);

    // Verificar movimiento de stock
    $movimiento = MovimientoStock::where('lote_id', $lote->id)->first();
    expect($movimiento)->not->toBeNull();
    expect($movimiento->tipo)->toBe('MOV_ENTRADA');
    expect($movimiento->cantidad)->toBe(10.0);
    expect($movimiento->ubicacion_destino_id)->toBe($this->ubicacion->id);
});

it('registra entrada de recepcion en cuarentena en estado Cuarentena', function () {
    $item = [
        'id' => $this->recepcionItem->id,
        'producto_id' => $this->producto->id,
        'producto_variante_id' => null,
        'cantidad_recibida' => 15.0,
        'cantidad_rechazada' => 0.0,
        'lote_proveedor' => 'PROV-LOT-DEF',
        'fecha_vencimiento' => null,
    ];

    $registrar = app(RegistrarEntradaRecepcion::class);
    $registrar->execute(
        nuevoEstado: 'EnCuarentena',
        items: [$item],
        proveedorId: $this->proveedor->id,
        creadoPorId: $this->user->id
    );

    $lote = Lote::where('recepcion_item_id', $this->recepcionItem->id)->first();
    expect($lote)->not->toBeNull();
    expect($lote->codigo_lote)->toBe('PROV-LOT-DEF');
    expect($lote->estado)->toBe(EstadoLote::Cuarentena);
    expect($lote->cantidad_disponible)->toBe(15.0);
});

it('registra entrada de recepcion con discrepancia dividiendo el lote segun decisiones', function () {
    $item = [
        'id' => $this->recepcionItem->id,
        'producto_id' => $this->producto->id,
        'producto_variante_id' => null,
        'cantidad_recibida' => 20.0,
        'cantidad_rechazada' => 0.0,
        'lote_proveedor' => 'PROV-LOT-XYZ',
        'fecha_vencimiento' => null,
    ];

    // Decisión de discrepancia: 12 unidades a Disponible y 8 unidades a Cuarentena
    $decisiones = [
        $this->recepcionItem->id => [
            'disponible' => 12.0,
            'cuarentena' => 8.0,
        ],
    ];

    $registrar = app(RegistrarEntradaRecepcion::class);
    $registrar->execute(
        nuevoEstado: 'ConDiscrepancia',
        items: [$item],
        proveedorId: $this->proveedor->id,
        creadoPorId: $this->user->id,
        decisionesDiscrepancia: $decisiones
    );

    // Deben haberse creado 2 lotes
    $lotes = Lote::where('recepcion_item_id', $this->recepcionItem->id)->get();
    expect($lotes)->toHaveCount(2);

    $loteDisponible = $lotes->firstWhere('estado', EstadoLote::Disponible);
    expect($loteDisponible)->not->toBeNull();
    expect($loteDisponible->codigo_lote)->toBe('PROV-LOT-XYZ-DISP');
    expect($loteDisponible->cantidad_disponible)->toBe(12.0);

    $loteCuarentena = $lotes->firstWhere('estado', EstadoLote::Cuarentena);
    expect($loteCuarentena)->not->toBeNull();
    expect($loteCuarentena->codigo_lote)->toBe('PROV-LOT-XYZ-CUAR');
    expect($loteCuarentena->cantidad_disponible)->toBe(8.0);
});

it('registra entrada de recepcion asignando el lote a la ubicacion especifica seleccionada por el usuario', function () {
    $customUbicacion = Ubicacion::create([
        'nombre' => 'Refrigeradora de Cocina',
        'tipo' => 'zona',
        'estado' => 1,
    ]);

    $item = [
        'id' => $this->recepcionItem->id,
        'producto_id' => $this->producto->id,
        'producto_variante_id' => null,
        'cantidad_recibida' => 50.0,
        'cantidad_rechazada' => 0.0,
        'lote_proveedor' => 'PROV-LOT-COLD',
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'ubicacion_id' => $customUbicacion->id,
    ];

    $registrar = app(RegistrarEntradaRecepcion::class);
    $registrar->execute(
        nuevoEstado: 'Completa',
        items: [$item],
        proveedorId: $this->proveedor->id,
        creadoPorId: $this->user->id
    );

    $lote = Lote::where('recepcion_item_id', $this->recepcionItem->id)->first();
    expect($lote)->not->toBeNull();
    expect($lote->codigo_lote)->toBe('PROV-LOT-COLD');
    expect($lote->ubicacion_id)->toBe($customUbicacion->id);
    expect($lote->ubicacion->nombre)->toBe('Refrigeradora de Cocina');
});
