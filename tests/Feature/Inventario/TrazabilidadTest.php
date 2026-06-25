<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\Proveedor;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\UseCases\Inventario\Queries\Trazabilidad\TrazabilidadHaciaAtras;
use App\UseCases\Inventario\Queries\Trazabilidad\TrazabilidadLoteHaciaAdelante;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// ──────────────────────────────────────────────
// TrazabilidadHaciaAtras
// ──────────────────────────────────────────────

describe('TrazabilidadHaciaAtras', function () {
    beforeEach(function () {
        $this->producto = Producto::factory()->create();
        $this->variante = ProductoVariante::create([
            'producto_id' => $this->producto->id,
            'codigo' => 'VAR-ATRAS',
            'nombre_variante' => 'Atrás',
            'estado' => 1,
        ]);
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Almacén General',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        // Cadena completa: Proveedor -> OC -> Recepcion -> Item -> Lote -> Movimiento
        $this->proveedor = Proveedor::factory()->create();
        $this->ordenCompra = OrdenCompra::factory()->create([
            'proveedor_id' => $this->proveedor->id,
        ]);
        $this->ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $this->ordenCompra->id,
            'producto_id' => $this->producto->id,
        ]);
        $this->recepcion = RecepcionCompra::factory()->create([
            'orden_compra_id' => $this->ordenCompra->id,
        ]);
        $this->recepcionItem = RecepcionItem::factory()->create([
            'recepcion_id' => $this->recepcion->id,
            'orden_item_id' => $this->ordenItem->id,
            'producto_id' => $this->producto->id,
            'cantidad_recibida' => 50.0,
        ]);

        $this->lote = Lote::create([
            'codigo_lote' => 'LOT-TRAZ-1',
            'producto_id' => $this->producto->id,
            'producto_variante_id' => $this->variante->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 50.0,
            'cantidad_inicial' => 50.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'recepcion_item_id' => $this->recepcionItem->id,
        ]);

        $this->movimiento = MovimientoStock::create([
            'tipo' => 'MOV_SALIDA',
            'lote_id' => $this->lote->id,
            'producto_id' => $this->producto->id,
            'cantidad' => -10.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'documento_tipo' => 'consumo',
            'documento_id' => 100,
            'referencia' => 'Consumo habitación 101',
            'created_at' => now(),
        ]);
    });

    it('retorna movimientos para un documento de salida con relaciones', function () {
        $resultado = app(TrazabilidadHaciaAtras::class)->ejecutar(
            documentoTipo: 'consumo',
            documentoId: 100
        );

        expect($resultado)->toHaveCount(1);
        $mov = $resultado->first();
        expect($mov->id)->toBe($this->movimiento->id);
        expect($mov->lote)->not->toBeNull();
        expect($mov->lote->recepcionItem)->not->toBeNull();
        expect($mov->lote->recepcionItem->recepcion)->not->toBeNull();
        expect($mov->lote->recepcionItem->recepcion->ordenCompra)->not->toBeNull();
        expect($mov->lote->recepcionItem->recepcion->ordenCompra->proveedor)->not->toBeNull();
        expect($mov->lote->recepcionItem->recepcion->ordenCompra->proveedor->id)->toBe($this->proveedor->id);
    });

    it('lanza RuntimeException cuando documentoTipo esta vacio', function () {
        expect(fn () => app(TrazabilidadHaciaAtras::class)->ejecutar(
            documentoTipo: '',
            documentoId: 100
        ))->toThrow(RuntimeException::class, 'tipo de documento es obligatorio');
    });

    it('lanza RuntimeException cuando documentoTipo es null', function () {
        expect(fn () => app(TrazabilidadHaciaAtras::class)->ejecutar(
            documentoTipo: '   ',
            documentoId: 100
        ))->toThrow(RuntimeException::class, 'tipo de documento es obligatorio');
    });

    it('retorna vacio cuando no hay movimientos para el documento', function () {
        $resultado = app(TrazabilidadHaciaAtras::class)->ejecutar(
            documentoTipo: 'consumo',
            documentoId: 999
        );

        expect($resultado)->toBeEmpty();
    });

    it('filtra correctamente por tipo y id de documento', function () {
        // Otro movimiento con diferente tipo de documento
        MovimientoStock::create([
            'tipo' => 'MOV_SALIDA',
            'lote_id' => $this->lote->id,
            'producto_id' => $this->producto->id,
            'cantidad' => -5.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'documento_tipo' => 'requisicion',
            'documento_id' => 200,
            'created_at' => now(),
        ]);

        $resultado = app(TrazabilidadHaciaAtras::class)->ejecutar(
            documentoTipo: 'consumo',
            documentoId: 100
        );

        expect($resultado)->toHaveCount(1);
    });

    it('incluye relaciones de lote, producto y variante', function () {
        $resultado = app(TrazabilidadHaciaAtras::class)->ejecutar(
            documentoTipo: 'consumo',
            documentoId: 100
        );

        expect($resultado)->toHaveCount(1);
        $mov = $resultado->first();
        expect($mov->lote->producto)->not->toBeNull();
        expect($mov->lote->producto->nombre)->toBe($this->producto->nombre);
        expect($mov->lote->variante)->not->toBeNull();
    });
});

// ──────────────────────────────────────────────
// TrazabilidadLoteHaciaAdelante
// ──────────────────────────────────────────────

describe('TrazabilidadLoteHaciaAdelante', function () {
    beforeEach(function () {
        $this->producto = Producto::factory()->create();
        $this->variante = ProductoVariante::create([
            'producto_id' => $this->producto->id,
            'codigo' => 'VAR-ADEL',
            'nombre_variante' => 'Adelante',
            'estado' => 1,
        ]);
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Almacén General',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        $this->proveedor = Proveedor::factory()->create();
        $this->ordenCompra = OrdenCompra::factory()->create([
            'proveedor_id' => $this->proveedor->id,
        ]);
        $this->ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $this->ordenCompra->id,
            'producto_id' => $this->producto->id,
        ]);
        $this->recepcion = RecepcionCompra::factory()->create([
            'orden_compra_id' => $this->ordenCompra->id,
        ]);
        $this->recepcionItem = RecepcionItem::factory()->create([
            'recepcion_id' => $this->recepcion->id,
            'orden_item_id' => $this->ordenItem->id,
            'producto_id' => $this->producto->id,
            'cantidad_recibida' => 100.0,
        ]);

        $this->lote = Lote::create([
            'codigo_lote' => 'LOT-ADEL-1',
            'producto_id' => $this->producto->id,
            'producto_variante_id' => $this->variante->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 80.0,
            'cantidad_inicial' => 100.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'recepcion_item_id' => $this->recepcionItem->id,
        ]);

        $this->movimiento1 = MovimientoStock::create([
            'tipo' => 'MOV_ENTRADA',
            'lote_id' => $this->lote->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 100.0,
            'ubicacion_destino_id' => $this->ubicacion->id,
            'created_at' => now()->subDays(10),
        ]);

        $this->movimiento2 = MovimientoStock::create([
            'tipo' => 'MOV_SALIDA',
            'lote_id' => $this->lote->id,
            'producto_id' => $this->producto->id,
            'cantidad' => -20.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'documento_tipo' => 'consumo',
            'documento_id' => 101,
            'created_at' => now()->subDays(5),
        ]);
    });

    it('retorna lote con relaciones y movimientos ordenados', function () {
        $resultado = app(TrazabilidadLoteHaciaAdelante::class)->ejecutar(
            loteId: $this->lote->id
        );

        expect($resultado)->toHaveKeys(['lote', 'movimientos']);
        expect($resultado['lote']->id)->toBe($this->lote->id);
        expect($resultado['movimientos'])->toHaveCount(2);
    });

    it('los movimientos se ordenan por created_at ascendente', function () {
        $resultado = app(TrazabilidadLoteHaciaAdelante::class)->ejecutar(
            loteId: $this->lote->id
        );

        $movimientos = $resultado['movimientos'];
        expect($movimientos[0]->id)->toBe($this->movimiento1->id);
        expect($movimientos[1]->id)->toBe($this->movimiento2->id);
        expect($movimientos[0]->created_at->lte($movimientos[1]->created_at))->toBeTrue();
    });

    it('incluye relaciones de lote: producto, variante, ubicacion y cadena de recepcion', function () {
        $resultado = app(TrazabilidadLoteHaciaAdelante::class)->ejecutar(
            loteId: $this->lote->id
        );

        $lote = $resultado['lote'];
        expect($lote->producto)->not->toBeNull();
        expect($lote->variante)->not->toBeNull();
        expect($lote->ubicacion)->not->toBeNull();
        expect($lote->recepcionItem)->not->toBeNull();
        expect($lote->recepcionItem->recepcion)->not->toBeNull();
        expect($lote->recepcionItem->recepcion->ordenCompra)->not->toBeNull();
        expect($lote->recepcionItem->recepcion->ordenCompra->proveedor)->not->toBeNull();
    });

    it('lanza ModelNotFoundException cuando el lote no existe', function () {
        expect(fn () => app(TrazabilidadLoteHaciaAdelante::class)->ejecutar(
            loteId: 99999
        ))->toThrow(ModelNotFoundException::class);
    });

    it('retorna movimientos vacios cuando el lote no tiene movimientos', function () {
        $loteSinMov = Lote::create([
            'codigo_lote' => 'LOT-SIN-MOV',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(TrazabilidadLoteHaciaAdelante::class)->ejecutar(
            loteId: $loteSinMov->id
        );

        expect($resultado['lote']->id)->toBe($loteSinMov->id);
        expect($resultado['movimientos'])->toBeEmpty();
    });

    it('incluye ubicaciones de origen y destino en los movimientos', function () {
        $otraUbicacion = Ubicacion::create([
            'nombre' => 'Bodega Piso 2',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        MovimientoStock::create([
            'tipo' => 'TRASLADO',
            'lote_id' => $this->lote->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'ubicacion_destino_id' => $otraUbicacion->id,
            'created_at' => now(),
        ]);

        $resultado = app(TrazabilidadLoteHaciaAdelante::class)->ejecutar(
            loteId: $this->lote->id
        );

        expect($resultado['movimientos'])->toHaveCount(3);
        $traslado = $resultado['movimientos']->last();
        expect($traslado->ubicacionOrigen)->not->toBeNull();
        expect($traslado->ubicacionDestino)->not->toBeNull();
        expect($traslado->ubicacionDestino->nombre)->toBe('Bodega Piso 2');
    });
});
