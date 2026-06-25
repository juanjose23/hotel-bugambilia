<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\User;
use App\UseCases\Inventario\Queries\Gestion\ObtenerRotacionInventario;
use App\UseCases\Inventario\Queries\Stock\ObtenerMovimientosInventario;
use App\UseCases\Inventario\Queries\Stock\ObtenerStockPorLote;
use App\UseCases\Inventario\Queries\Stock\ObtenerStockPorProducto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// ──────────────────────────────────────────────
// ObtenerRotacionInventario
// ──────────────────────────────────────────────

describe('ObtenerRotacionInventario', function () {
    beforeEach(function () {
        $this->producto = Producto::factory()->create(['nombre' => 'Producto Alta Rotacion']);
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Almacén General',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);
    });

    it('retorna productos con indice de rotacion y clasificacion', function () {
        // Lote con stock promedio disponible para el producto
        Lote::create([
            'codigo_lote' => 'LOT-ROT-1',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 100.0,
            'cantidad_inicial' => 100.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
        ]);

        // Movimientos de salida en los ultimos meses
        MovimientoStock::create([
            'tipo' => 'MOV_SALIDA',
            'producto_id' => $this->producto->id,
            'lote_id' => null,
            'cantidad' => 300.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'created_at' => now()->subMonth(),
        ]);

        $resultado = app(ObtenerRotacionInventario::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        $row = $resultado->first();
        expect($row->producto)->toBe($this->producto->nombre);
        expect((float) $row->total_salidas)->toBe(300.0);
        expect((float) $row->indice_rotacion)->toBeGreaterThan(0);
    });

    it('clasifica como Alta cuando indice_rotacion >= 2.0', function () {
        Lote::create([
            'codigo_lote' => 'LOT-ROT-2',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        MovimientoStock::create([
            'tipo' => 'MOV_SALIDA',
            'producto_id' => $this->producto->id,
            'lote_id' => null,
            'cantidad' => 100.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'created_at' => now()->subMonth(),
        ]);

        // AVG stock_disponible = 10 / 1 lote = 10
        // total_salidas = 100
        // indice = 100/10 = 10 >= 2.0 => Alta

        $resultado = app(ObtenerRotacionInventario::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->clasificacion)->toBe('Alta');
    });

    it('clasifica como Media cuando indice_rotacion >= 0.5 y < 2.0', function () {
        Lote::create([
            'codigo_lote' => 'LOT-ROT-3',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 100.0,
            'cantidad_inicial' => 100.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        MovimientoStock::create([
            'tipo' => 'MOV_SALIDA',
            'producto_id' => $this->producto->id,
            'lote_id' => null,
            'cantidad' => 60.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'created_at' => now()->subMonth(),
        ]);

        // AVG stock = 100, total_salidas = 60, indice = 0.6 => Media

        $resultado = app(ObtenerRotacionInventario::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->clasificacion)->toBe('Media');
    });

    it('clasifica como Baja cuando indice_rotacion < 0.5', function () {
        Lote::create([
            'codigo_lote' => 'LOT-ROT-4',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 100.0,
            'cantidad_inicial' => 100.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        MovimientoStock::create([
            'tipo' => 'MOV_SALIDA',
            'producto_id' => $this->producto->id,
            'lote_id' => null,
            'cantidad' => 10.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'created_at' => now()->subMonth(),
        ]);

        // AVG stock = 100, total_salidas = 10, indice = 0.1 => Baja

        $resultado = app(ObtenerRotacionInventario::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->clasificacion)->toBe('Baja');
    });

    it('incluye movimientos de tipo MOV_AJUSTE en el calculo', function () {
        Lote::create([
            'codigo_lote' => 'LOT-ROT-5',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 50.0,
            'cantidad_inicial' => 50.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        MovimientoStock::create([
            'tipo' => 'MOV_AJUSTE',
            'producto_id' => $this->producto->id,
            'lote_id' => null,
            'cantidad' => 30.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'created_at' => now()->subMonth(),
        ]);

        $resultado = app(ObtenerRotacionInventario::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect((float) $resultado->first()->total_salidas)->toBe(30.0);
    });

    it('retorna vacio cuando no hay movimientos en el periodo', function () {
        $resultado = app(ObtenerRotacionInventario::class)->ejecutar();

        expect($resultado)->toBeEmpty();
    });

    it('respeta el parametro de meses', function () {
        Lote::create([
            'codigo_lote' => 'LOT-ROT-6',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 100.0,
            'cantidad_inicial' => 100.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        // Movimiento muy antiguo
        MovimientoStock::create([
            'tipo' => 'MOV_SALIDA',
            'producto_id' => $this->producto->id,
            'lote_id' => null,
            'cantidad' => 50.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'created_at' => now()->subMonths(6),
        ]);

        // Solo ultimos 2 meses, este movimiento de 6 meses atras no deberia contar
        $resultado = app(ObtenerRotacionInventario::class)->ejecutar(
            filtros: ['meses' => 2]
        );

        expect($resultado)->toBeEmpty();
    });
});

// ──────────────────────────────────────────────
// ObtenerStockPorLote
// ──────────────────────────────────────────────

describe('ObtenerStockPorLote', function () {
    beforeEach(function () {
        $this->producto = Producto::factory()->create();
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Almacén Norte',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        $this->lote = Lote::create([
            'codigo_lote' => 'LOT-STK-1',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 100.0,
            'cantidad_inicial' => 100.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
        ]);
    });

    it('retorna lotes paginados con relaciones', function () {
        $resultado = app(ObtenerStockPorLote::class)->ejecutar();

        expect($resultado)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($resultado->total())->toBe(1);
        expect($resultado->first()->id)->toBe($this->lote->id);
    });

    it('filtra por estado', function () {
        Lote::create([
            'codigo_lote' => 'LOT-CUAR-STK',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Cuarentena,
            'cantidad_disponible' => 50.0,
            'cantidad_inicial' => 50.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerStockPorLote::class)->ejecutar(
            filtros: ['estado' => EstadoLote::Cuarentena]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->estado)->toBe(EstadoLote::Cuarentena);
    });

    it('filtra por producto_id', function () {
        $otroProducto = Producto::factory()->create();
        Lote::create([
            'codigo_lote' => 'LOT-OTRO',
            'producto_id' => $otroProducto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 30.0,
            'cantidad_inicial' => 30.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerStockPorLote::class)->ejecutar(
            filtros: ['producto_id' => $this->producto->id]
        );

        expect($resultado)->toHaveCount(1);
    });

    it('filtra por ubicacion_id', function () {
        $otraUbicacion = Ubicacion::create([
            'nombre' => 'Bodega Sur',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        Lote::create([
            'codigo_lote' => 'LOT-OTRA-UBI',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 20.0,
            'cantidad_inicial' => 20.0,
            'ubicacion_id' => $otraUbicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerStockPorLote::class)->ejecutar(
            filtros: ['ubicacion_id' => $this->ubicacion->id]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->ubicacion_id)->toBe($this->ubicacion->id);
    });

    it('filtra por solo_proximos (lotes por vencer en 30 dias)', function () {
        // Lote por vencer pronto
        Lote::create([
            'codigo_lote' => 'LOT-PROX-STK',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
        ]);

        $resultado = app(ObtenerStockPorLote::class)->ejecutar(
            filtros: ['solo_proximos' => true]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->codigo_lote)->toBe('LOT-PROX-STK');
    });

    it('excluye lotes sin fecha de vencimiento cuando solo_proximos es true', function () {
        Lote::where('id', $this->lote->id)->update(['fecha_vencimiento' => null]);

        $resultado = app(ObtenerStockPorLote::class)->ejecutar(
            filtros: ['solo_proximos' => true]
        );

        expect($resultado)->toBeEmpty();
    });
});

// ──────────────────────────────────────────────
// ObtenerStockPorProducto
// ──────────────────────────────────────────────

describe('ObtenerStockPorProducto', function () {
    beforeEach(function () {
        $this->categoria = Catalogo::factory()->create(['nombre' => 'Limpieza']);
        $this->producto = Producto::factory()->create([
            'nombre' => 'Jabón Líquido',
            'categoria_id' => $this->categoria->id,
        ]);
        $this->variante = ProductoVariante::create([
            'producto_id' => $this->producto->id,
            'codigo' => 'JAB-1L',
            'nombre_variante' => '1L',
            'estado' => 1,
        ]);
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Almacén Principal',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        $this->lote = Lote::create([
            'codigo_lote' => 'LOT-PROD-1',
            'producto_id' => $this->producto->id,
            'producto_variante_id' => $this->variante->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 50.0,
            'cantidad_inicial' => 50.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);
    });

    it('retorna stock agregado por producto y ubicacion', function () {
        $resultado = app(ObtenerStockPorProducto::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        $row = $resultado->first();
        expect($row->producto)->toBe('Jabón Líquido');
        expect($row->variante)->toBe('1L');
        expect($row->categoria)->toBe('Limpieza');
        expect((float) $row->stock_disponible)->toBe(50.0);
        expect((float) $row->stock_cuarentena)->toBe(0.0);
        expect((float) $row->total_lotes)->toBe(1.0);
    });

    it('separa stock en cuarentena del disponible', function () {
        Lote::create([
            'codigo_lote' => 'LOT-CUAR-PROD',
            'producto_id' => $this->producto->id,
            'producto_variante_id' => $this->variante->id,
            'estado' => EstadoLote::Cuarentena,
            'cantidad_disponible' => 20.0,
            'cantidad_inicial' => 20.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerStockPorProducto::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        $row = $resultado->first();
        expect((float) $row->stock_disponible)->toBe(50.0);
        expect((float) $row->stock_cuarentena)->toBe(20.0);
        expect($row->total_lotes)->toBe(2);
    });

    it('filtra por producto_id', function () {
        $otroProducto = Producto::factory()->create();
        Lote::create([
            'codigo_lote' => 'LOT-OTRO-PROD',
            'producto_id' => $otroProducto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 30.0,
            'cantidad_inicial' => 30.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerStockPorProducto::class)->ejecutar(
            filtros: ['producto_id' => $this->producto->id]
        );

        expect($resultado)->toHaveCount(1);
    });

    it('filtra por ubicacion_id', function () {
        $otraUbicacion = Ubicacion::create([
            'nombre' => 'Bodega Alterna',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        Lote::create([
            'codigo_lote' => 'LOT-OTRA-UBI-2',
            'producto_id' => $this->producto->id,
            'producto_variante_id' => $this->variante->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 15.0,
            'cantidad_inicial' => 15.0,
            'ubicacion_id' => $otraUbicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerStockPorProducto::class)->ejecutar(
            filtros: ['ubicacion_id' => $this->ubicacion->id]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->ubicacion_id)->toBe($this->ubicacion->id);
    });

    it('excluye lotes soft-deleted', function () {
        $this->lote->delete();

        $resultado = app(ObtenerStockPorProducto::class)->ejecutar();

        expect($resultado)->toBeEmpty();
    });

    it('solo suma stock disponible para el disponible, ignora otros estados', function () {
        Lote::create([
            'codigo_lote' => 'LOT-AGOT',
            'producto_id' => $this->producto->id,
            'producto_variante_id' => $this->variante->id,
            'estado' => EstadoLote::Agotado,
            'cantidad_disponible' => 0.0,
            'cantidad_inicial' => 50.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerStockPorProducto::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect((float) $resultado->first()->stock_disponible)->toBe(50.0);
    });
});

// ──────────────────────────────────────────────
// ObtenerMovimientosInventario
// ──────────────────────────────────────────────

describe('ObtenerMovimientosInventario', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->producto = Producto::factory()->create();
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Almacén General',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        $this->movimiento = MovimientoStock::create([
            'tipo' => 'MOV_ENTRADA',
            'producto_id' => $this->producto->id,
            'cantidad' => 100.0,
            'ubicacion_destino_id' => $this->ubicacion->id,
            'created_at' => now()->subDays(5),
        ]);
    });

    it('retorna movimientos paginados ordenados por created_at desc', function () {
        $resultado = app(ObtenerMovimientosInventario::class)->ejecutar();

        expect($resultado)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($resultado->total())->toBeGreaterThanOrEqual(1);
    });

    it('filtra por tipo', function () {
        MovimientoStock::create([
            'tipo' => 'MOV_SALIDA',
            'producto_id' => $this->producto->id,
            'cantidad' => 30.0,
            'ubicacion_origen_id' => $this->ubicacion->id,
            'created_at' => now()->subDay(),
        ]);

        $resultado = app(ObtenerMovimientosInventario::class)->ejecutar(
            filtros: ['tipo' => 'MOV_SALIDA']
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->tipo)->toBe('MOV_SALIDA');
    });

    it('filtra por producto_id', function () {
        $otroProducto = Producto::factory()->create();
        MovimientoStock::create([
            'tipo' => 'MOV_ENTRADA',
            'producto_id' => $otroProducto->id,
            'cantidad' => 50.0,
            'ubicacion_destino_id' => $this->ubicacion->id,
            'created_at' => now(),
        ]);

        $resultado = app(ObtenerMovimientosInventario::class)->ejecutar(
            filtros: ['producto_id' => $this->producto->id]
        );

        expect($resultado)->toHaveCount(1);
    });

    it('filtra por rango de fechas', function () {
        MovimientoStock::create([
            'tipo' => 'MOV_ENTRADA',
            'producto_id' => $this->producto->id,
            'cantidad' => 25.0,
            'ubicacion_destino_id' => $this->ubicacion->id,
            'created_at' => now()->subMonths(2),
        ]);

        $resultado = app(ObtenerMovimientosInventario::class)->ejecutar(
            filtros: [
                'fecha_desde' => now()->subDays(10),
                'fecha_hasta' => now(),
            ]
        );

        expect($resultado)->toHaveCount(1);
    });

    it('respeta el parametro perPage', function () {
        MovimientoStock::create([
            'tipo' => 'MOV_ENTRADA',
            'producto_id' => $this->producto->id,
            'cantidad' => 10.0,
            'ubicacion_destino_id' => $this->ubicacion->id,
            'created_at' => now()->subDays(3),
        ]);
        MovimientoStock::create([
            'tipo' => 'MOV_ENTRADA',
            'producto_id' => $this->producto->id,
            'cantidad' => 20.0,
            'ubicacion_destino_id' => $this->ubicacion->id,
            'created_at' => now()->subDays(1),
        ]);

        $resultado = app(ObtenerMovimientosInventario::class)->ejecutar(
            filtros: [],
            perPage: 2
        );

        expect($resultado->count())->toBeLessThanOrEqual(2);
    });

    it('retorna vacio cuando no hay movimientos que coincidan con los filtros', function () {
        $resultado = app(ObtenerMovimientosInventario::class)->ejecutar(
            filtros: ['tipo' => 'TRASLADO']
        );

        expect($resultado)->toBeEmpty();
    });
});
