<?php

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Espacios\Habitacion;
use App\Models\Espacios\PlantillaDotacion;
use App\Models\Espacios\PlantillaItem;
use App\Models\Espacios\TipoHabitacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\ParStock;
use App\Models\Inventario\Stock;
use App\Models\User;
use App\UseCases\Inventario\Dotacion\PrepararEspacio;
use App\UseCases\Inventario\Movimientos\Mutations\TrasladarEntreBodegas;
use App\UseCases\Inventario\Recepciones\Mutations\RegistrarEntradaRecepcion;
use App\UseCases\Inventario\Reposiciones\Mutations\GenerarReposicionesBodega;
use App\UseCases\Inventario\Reposiciones\Mutations\ProcesarReposicion;
use App\UseCases\Inventario\Services\PutawayPolicy;

beforeEach(function () {
    // Reset static cache in PutawayPolicy to prevent test pollution
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

    $this->tipoHab = TipoHabitacion::create([
        'codigo' => 'STD',
        'nombre' => 'Estándar',
        'capacidad_max' => 4,
        'activo' => true,
    ]);

    $this->habitacion = Habitacion::create([
        'numero' => '101',
        'tipo_id' => $this->tipoHab->id,
        'piso' => 1,
        'ubicacion_id' => $this->bodegaPiso->id, // La bodega del piso 1 abastece esta habitación
        'estado' => 'disponible',
        'activa' => true,
    ]);
});

it('ejecuta todo el flujo de inventario completo exitosamente', function () {
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

    // Verificar que el lote se creó y que inv_stock se actualizó en el almacén
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
    // Trasladamos 3 unidades del Almacén General a la Bodega Piso 1
    $trasladar = app(TrasladarEntreBodegas::class);
    $trasladar->execute(
        productoId: $this->producto->id,
        loteId: $lote->id,
        cantidad: 3.0,
        origenId: $this->almacen->id,
        destinoId: $this->bodegaPiso->id,
        creadoPorId: $this->user->id,
        referencia: 'Traslado inicial a Bodega Piso 1'
    );

    // Verificar stock origen y destino
    expect($stockAlmacen->fresh()->cantidad)->toBe(7.0);

    $stockBodega = Stock::where([
        'producto_id' => $this->producto->id,
        'lote_id' => $lote->id,
        'ubicacion_id' => $this->bodegaPiso->id,
    ])->first();

    expect($stockBodega)->not->toBeNull();
    expect($stockBodega->cantidad)->toBe(3.0);

    // Verificar bitácora de movimientos (TRASLADO)
    $movTraslado = MovimientoStock::where('tipo', 'TRASLADO')->first();
    expect($movTraslado)->not->toBeNull();
    expect($movTraslado->lote_id)->toBe($lote->id);
    expect($movTraslado->cantidad)->toBe(3.0);

    // --- PASO 3: PREPARAR HABITACIÓN (APLICAR PLANTILLA) ---
    // Creamos plantilla de dotación para este tipo de habitación
    $plantilla = PlantillaDotacion::create([
        'nombre' => 'Dotación Estándar',
        'espacio_tipo' => 'habitacion',
        'tipo_id' => $this->tipoHab->id,
        'activa' => true,
        'notas' => 'Plantilla para pruebas estándar',
    ]);

    PlantillaItem::create([
        'plantilla_id' => $plantilla->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 1.0,
        'es_reposicion_diaria' => true,
    ]);

    $preparar = app(PrepararEspacio::class);
    $preparar->execute(
        espacioTipo: 'habitacion',
        espacioId: $this->habitacion->id,
        plantillaId: $plantilla->id,
        ubicacionId: $this->bodegaPiso->id,
        usuarioId: $this->user->id,
        notas: 'Preparación de habitación estándar'
    );

    // Verificar que el stock en la Bodega Piso 1 disminuyó de 3 a 2
    expect($stockBodega->fresh()->cantidad)->toBe(2.0);

    // El stock del lote global disminuyó de 10 a 9 (7 en almacén, 2 en bodega)
    expect($lote->fresh()->cantidad_disponible)->toBe(9.0);

    // Verificar bitácora de movimientos (SALIDA_DOTACION)
    $movDotacion = MovimientoStock::where('tipo', 'SALIDA_DOTACION')->first();
    expect($movDotacion)->not->toBeNull();
    expect($movDotacion->cantidad)->toBe(-1.0);

    // --- PASO 4: REPOSICIÓN AUTOMÁTICA (PAR STOCK) ---
    // Configuramos un PAR Stock para la Bodega Piso 1: mínimo 3, objetivo 5
    ParStock::create([
        'producto_id' => $this->producto->id,
        'ubicacion_id' => $this->bodegaPiso->id,
        'stock_minimo' => 3.0,
        'stock_objetivo' => 5.0,
    ]);

    // Como la bodega tiene stock 2.0 (menor que el mínimo 3.0),
    // al ejecutar GenerarReposicionesBodega se debe generar una orden para surtir 3.0 (5.0 - 2.0)
    $generar = app(GenerarReposicionesBodega::class);
    $reposiciones = $generar->execute(creadoPorId: $this->user->id);

    expect($reposiciones)->toHaveCount(1);
    $rep = $reposiciones[0];
    expect($rep->estado)->toBe('pendiente');
    expect($rep->origen_id)->toBe($this->almacen->id);
    expect($rep->destino_id)->toBe($this->bodegaPiso->id);

    $repItem = $rep->items()->first();
    expect($repItem)->not->toBeNull();
    expect($repItem->producto_id)->toBe($this->producto->id);
    expect($repItem->cantidad_solicitada)->toBe(3.0);

    // --- PASO 5: PROCESAR REPOSICIÓN ---
    // Procesamos la reposición surtiendo desde el Almacén General
    $procesar = app(ProcesarReposicion::class);
    $procesar->execute(reposicionId: $rep->id, procesadoPorId: $this->user->id);

    // Verificar que la orden de reposición pasó a procesada
    expect($rep->fresh()->estado)->toBe('procesada');

    // Verificar que se consumieron 3 unidades del Almacén General: disminuye de 7 a 4
    expect($stockAlmacen->fresh()->cantidad)->toBe(4.0);

    // Verificar que llegaron 3 unidades a la Bodega Piso 1: aumenta de 2 a 5
    expect($stockBodega->fresh()->cantidad)->toBe(5.0);

    // Verificar que la cantidad del lote global sigue siendo 9.0 (4 en almacén, 5 en bodega)
    expect($lote->fresh()->cantidad_disponible)->toBe(9.0);
});
