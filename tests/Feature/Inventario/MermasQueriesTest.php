<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\UseCases\Inventario\Queries\Mermas\ObtenerLotesMerma;
use App\UseCases\Inventario\Queries\Mermas\ObtenerMermasTotales;

// ──────────────────────────────────────────────
// ObtenerLotesMerma
// ──────────────────────────────────────────────

describe('ObtenerLotesMerma', function () {
    beforeEach(function () {
        $this->producto = Producto::factory()->create();
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Zona de Merma',
            'tipo' => 'zona',
            'estado' => 1,
        ]);
    });

    it('retorna lotes en estado vencido', function () {
        $lote = Lote::create([
            'codigo_lote' => 'LOT-VENC-MERMA',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonths(2)->toDateString(),
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ]);

        $resultado = app(ObtenerLotesMerma::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($lote->id);
    });

    it('retorna lotes en estado rechazado', function () {
        $lote = Lote::create([
            'codigo_lote' => 'LOT-RECHAZADO',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Rechazado,
            'cantidad_disponible' => 5.0,
            'cantidad_inicial' => 5.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subDays(5)->toDateString(),
        ]);

        $resultado = app(ObtenerLotesMerma::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($lote->id);
        expect($resultado->first()->estado)->toBe(EstadoLote::Rechazado);
    });

    it('incluye tanto vencidos como rechazados', function () {
        Lote::create([
            'codigo_lote' => 'LOT-VENC',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonth()->toDateString(),
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ]);

        Lote::create([
            'codigo_lote' => 'LOT-RECH',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Rechazado,
            'cantidad_disponible' => 8.0,
            'cantidad_inicial' => 8.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subDays(3)->toDateString(),
        ]);

        $resultado = app(ObtenerLotesMerma::class)->ejecutar();

        expect($resultado)->toHaveCount(2);
    });

    it('excluye lotes en estado disponible o cuarentena', function () {
        Lote::create([
            'codigo_lote' => 'LOT-DISP',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 100.0,
            'cantidad_inicial' => 100.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        Lote::create([
            'codigo_lote' => 'LOT-CUAR',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Cuarentena,
            'cantidad_disponible' => 50.0,
            'cantidad_inicial' => 50.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerLotesMerma::class)->ejecutar();

        expect($resultado)->toBeEmpty();
    });

    it('filtra por periodo_desde', function () {
        $loteViejo = Lote::create([
            'codigo_lote' => 'LOT-VIEJO',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonths(3)->toDateString(),
            'fecha_vencimiento' => now()->subMonths(2)->toDateString(),
            'updated_at' => now()->subMonths(2),
        ]);
        $loteViejo->update(['updated_at' => now()->subMonths(2)]);

        $loteReciente = Lote::create([
            'codigo_lote' => 'LOT-RECIENTE',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Rechazado,
            'cantidad_disponible' => 5.0,
            'cantidad_inicial' => 5.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerLotesMerma::class)->ejecutar(
            filtros: ['periodo_desde' => now()->subMonth()]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($loteReciente->id);
    });

    it('filtra por periodo_hasta', function () {
        $loteViejo = Lote::create([
            'codigo_lote' => 'LOT-VIEJO',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonths(3)->toDateString(),
            'fecha_vencimiento' => now()->subMonths(2)->toDateString(),
        ]);
        $loteViejo->update(['updated_at' => now()->subMonths(2)]);

        $resultado = app(ObtenerLotesMerma::class)->ejecutar(
            filtros: ['periodo_hasta' => now()->subMonth()]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($loteViejo->id);
    });

    it('filtra por motivo caducidad (solo vencidos)', function () {
        Lote::create([
            'codigo_lote' => 'LOT-RECH',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Rechazado,
            'cantidad_disponible' => 8.0,
            'cantidad_inicial' => 8.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subDays(3)->toDateString(),
        ]);

        Lote::create([
            'codigo_lote' => 'LOT-VENC',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonth()->toDateString(),
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ]);

        $resultado = app(ObtenerLotesMerma::class)->ejecutar(
            filtros: ['motivo' => 'caducidad']
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->estado)->toBe(EstadoLote::Vencido);
    });

    it('filtra por motivo calidad (solo rechazados)', function () {
        Lote::create([
            'codigo_lote' => 'LOT-VENC',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonth()->toDateString(),
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ]);

        Lote::create([
            'codigo_lote' => 'LOT-RECH',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Rechazado,
            'cantidad_disponible' => 5.0,
            'cantidad_inicial' => 5.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subDays(3)->toDateString(),
        ]);

        $resultado = app(ObtenerLotesMerma::class)->ejecutar(
            filtros: ['motivo' => 'calidad']
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->estado)->toBe(EstadoLote::Rechazado);
    });
});

// ──────────────────────────────────────────────
// ObtenerMermasTotales
// ──────────────────────────────────────────────

describe('ObtenerMermasTotales', function () {
    beforeEach(function () {
        $this->producto = Producto::factory()->create();
    });

    it('retorna perdidas agregadas a partir de movimientos MOV_AJUSTE', function () {
        $orden = OrdenCompra::factory()->create();
        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10.0,
            'precio_unitario' => 25.0,
            'subtotal' => 250.0,
        ]);
        $recepcion = RecepcionCompra::factory()->create(['orden_compra_id' => $orden->id]);
        $recepcionItem = RecepcionItem::factory()->create([
            'recepcion_id' => $recepcion->id,
            'orden_item_id' => $ordenItem->id,
            'producto_id' => $this->producto->id,
            'cantidad_recibida' => 10.0,
        ]);
        $recepcionItem->update(['producto_id' => $this->producto->id]);

        MovimientoStock::create([
            'tipo' => 'MOV_AJUSTE',
            'producto_id' => $this->producto->id,
            'cantidad' => 5.0,
            'documento_tipo' => 'recepcion_item',
            'documento_id' => $recepcionItem->id,
            'referencia' => 'Ajuste por vencimiento',
            'created_at' => now(),
        ]);

        $resultado = app(ObtenerMermasTotales::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        $row = $resultado->first();
        expect($row->producto)->toBe($this->producto->nombre);
        expect((float) $row->cantidad_perdida)->toBe(5.0);
        expect((float) $row->costo_unitario)->toBe(25.0);
        expect((float) $row->perdida_total)->toBe(125.0);
    });

    it('clasifica como Caducidad cuando la referencia contiene vencimiento', function () {
        $orden = OrdenCompra::factory()->create();
        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10.0,
            'precio_unitario' => 10.0,
            'subtotal' => 100.0,
        ]);
        $recepcion = RecepcionCompra::factory()->create(['orden_compra_id' => $orden->id]);
        $recepcionItem = RecepcionItem::factory()->create([
            'recepcion_id' => $recepcion->id,
            'orden_item_id' => $ordenItem->id,
            'producto_id' => $this->producto->id,
            'cantidad_recibida' => 10.0,
        ]);
        $recepcionItem->update(['producto_id' => $this->producto->id]);

        MovimientoStock::create([
            'tipo' => 'MOV_AJUSTE',
            'producto_id' => $this->producto->id,
            'cantidad' => 3.0,
            'documento_tipo' => 'recepcion_item',
            'documento_id' => $recepcionItem->id,
            'referencia' => 'Baja por vencimiento',
            'created_at' => now(),
        ]);

        $resultado = app(ObtenerMermasTotales::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->categoria)->toBe('Caducidad');
    });

    it('clasifica como Calidad cuando la referencia contiene rechazo', function () {
        $orden = OrdenCompra::factory()->create();
        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10.0,
            'precio_unitario' => 10.0,
            'subtotal' => 100.0,
        ]);
        $recepcion = RecepcionCompra::factory()->create(['orden_compra_id' => $orden->id]);
        $recepcionItem = RecepcionItem::factory()->create([
            'recepcion_id' => $recepcion->id,
            'orden_item_id' => $ordenItem->id,
            'producto_id' => $this->producto->id,
            'cantidad_recibida' => 10.0,
        ]);
        $recepcionItem->update(['producto_id' => $this->producto->id]);

        MovimientoStock::create([
            'tipo' => 'MOV_AJUSTE',
            'producto_id' => $this->producto->id,
            'cantidad' => 2.0,
            'documento_tipo' => 'recepcion_item',
            'documento_id' => $recepcionItem->id,
            'referencia' => 'Rechazo por calidad',
            'created_at' => now(),
        ]);

        $resultado = app(ObtenerMermasTotales::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->categoria)->toBe('Calidad / Rechazo');
    });

    it('clasifica como Ajuste Manual cuando la referencia no es especifica', function () {
        $orden = OrdenCompra::factory()->create();
        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'producto_id' => $this->producto->id,
            'precio_unitario' => 10.0,
        ]);
        $recepcion = RecepcionCompra::factory()->create(['orden_compra_id' => $orden->id]);
        $recepcionItem = RecepcionItem::factory()->create([
            'recepcion_id' => $recepcion->id,
            'orden_item_id' => $ordenItem->id,
            'producto_id' => $this->producto->id,
            'cantidad_recibida' => 10.0,
        ]);
        $recepcionItem->update(['producto_id' => $this->producto->id]);

        MovimientoStock::create([
            'tipo' => 'MOV_AJUSTE',
            'producto_id' => $this->producto->id,
            'cantidad' => 1.0,
            'documento_tipo' => 'recepcion_item',
            'documento_id' => $recepcionItem->id,
            'referencia' => 'Ajuste manual de inventario',
            'created_at' => now(),
        ]);

        $resultado = app(ObtenerMermasTotales::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->categoria)->toBe('Ajuste Manual');
    });

    it('filtra por periodo_desde y periodo_hasta', function () {
        $orden = OrdenCompra::factory()->create();
        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'producto_id' => $this->producto->id,
            'precio_unitario' => 10.0,
        ]);
        $recepcion = RecepcionCompra::factory()->create(['orden_compra_id' => $orden->id]);
        $recepcionItem = RecepcionItem::factory()->create([
            'recepcion_id' => $recepcion->id,
            'orden_item_id' => $ordenItem->id,
            'producto_id' => $this->producto->id,
            'cantidad_recibida' => 10.0,
        ]);
        $recepcionItem->update(['producto_id' => $this->producto->id]);

        MovimientoStock::create([
            'tipo' => 'MOV_AJUSTE',
            'producto_id' => $this->producto->id,
            'cantidad' => 4.0,
            'documento_tipo' => 'recepcion_item',
            'documento_id' => $recepcionItem->id,
            'referencia' => 'Perdida',
            'created_at' => now()->subMonths(2),
        ]);

        $resultado = app(ObtenerMermasTotales::class)->ejecutar(
            filtros: [
                'periodo_desde' => now()->subMonth(),
                'periodo_hasta' => now()->addDay(),
            ]
        );

        expect($resultado)->toBeEmpty();
    });

    it('totalPerdidas retorna la suma de perdida_total', function () {
        $orden = OrdenCompra::factory()->create();
        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10.0,
            'precio_unitario' => 20.0,
            'subtotal' => 200.0,
        ]);
        $recepcion = RecepcionCompra::factory()->create(['orden_compra_id' => $orden->id]);
        $recepcionItem = RecepcionItem::factory()->create([
            'recepcion_id' => $recepcion->id,
            'orden_item_id' => $ordenItem->id,
            'producto_id' => $this->producto->id,
            'cantidad_recibida' => 10.0,
        ]);
        $recepcionItem->update(['producto_id' => $this->producto->id]);

        MovimientoStock::create([
            'tipo' => 'MOV_AJUSTE',
            'producto_id' => $this->producto->id,
            'cantidad' => 3.0,
            'documento_tipo' => 'recepcion_item',
            'documento_id' => $recepcionItem->id,
            'referencia' => 'Baja',
            'created_at' => now(),
        ]);

        $total = app(ObtenerMermasTotales::class)->totalPerdidas();

        expect($total)->toBe(60.0);
    });

    it('retorna coleccion vacia cuando no hay movimientos de ajuste', function () {
        $resultado = app(ObtenerMermasTotales::class)->ejecutar();

        expect($resultado)->toBeEmpty();
    });
});
