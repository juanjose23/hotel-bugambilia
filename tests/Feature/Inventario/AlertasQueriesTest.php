<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesCuarentena;
use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesProximosVencer;
use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesVencidos;

// ──────────────────────────────────────────────
// ObtenerLotesCuarentena
// ──────────────────────────────────────────────

describe('ObtenerLotesCuarentena', function () {
    beforeEach(function () {
        $this->producto = Producto::factory()->create();
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Zona de Cuarentena',
            'tipo' => 'zona',
            'estado' => 1,
        ]);

        $this->loteCuarentena = Lote::create([
            'codigo_lote' => 'LOT-CUAR-1',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Cuarentena,
            'cantidad_disponible' => 50.0,
            'cantidad_inicial' => 50.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subDays(5)->toDateString(),
        ]);
    });

    it('retorna lotes en estado cuarentena', function () {
        $resultado = app(ObtenerLotesCuarentena::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($this->loteCuarentena->id);
        expect($resultado->first()->estado)->toBe(EstadoLote::Cuarentena);
    });

    it('filtra por producto_id', function () {
        $otroProducto = Producto::factory()->create();
        $otroLote = Lote::create([
            'codigo_lote' => 'LOT-CUAR-2',
            'producto_id' => $otroProducto->id,
            'estado' => EstadoLote::Cuarentena,
            'cantidad_disponible' => 30.0,
            'cantidad_inicial' => 30.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subDays(3)->toDateString(),
        ]);

        $resultado = app(ObtenerLotesCuarentena::class)->ejecutar(
            filtros: ['producto_id' => $this->producto->id]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($this->loteCuarentena->id);
    });

    it('filtra por fecha_desde', function () {
        // Lote mas reciente
        $loteReciente = Lote::create([
            'codigo_lote' => 'LOT-CUAR-3',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Cuarentena,
            'cantidad_disponible' => 20.0,
            'cantidad_inicial' => 20.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subDay()->toDateString(),
        ]);

        // Forzar updated_at
        $loteReciente->update(['updated_at' => now()->subDay()]);
        $this->loteCuarentena->update(['updated_at' => now()->subDays(10)]);

        $resultado = app(ObtenerLotesCuarentena::class)->ejecutar(
            filtros: ['fecha_desde' => now()->subDays(5)]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($loteReciente->id);
    });

    it('retorna vacio cuando no hay lotes en cuarentena', function () {
        Lote::where('estado', EstadoLote::Cuarentena)->update(['estado' => EstadoLote::Disponible]);

        $resultado = app(ObtenerLotesCuarentena::class)->ejecutar();

        expect($resultado)->toBeEmpty();
    });

    it('ignora lotes en otros estados', function () {
        Lote::create([
            'codigo_lote' => 'LOT-DISP',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
        ]);

        $resultado = app(ObtenerLotesCuarentena::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
    });
});

// ──────────────────────────────────────────────
// ObtenerLotesProximosVencer
// ──────────────────────────────────────────────

describe('ObtenerLotesProximosVencer', function () {
    beforeEach(function () {
        $this->producto = Producto::factory()->create();
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Almacén General',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        $this->loteProximo = Lote::create([
            'codigo_lote' => 'LOT-PROX-1',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 30.0,
            'cantidad_inicial' => 30.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
        ]);
    });

    it('retorna lotes que vencen dentro de los proximos 30 dias por defecto', function () {
        $resultado = app(ObtenerLotesProximosVencer::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($this->loteProximo->id);
    });

    it('retorna lotes que vencen dentro de un numero personalizado de dias', function () {
        // Lote que vence en 5 dias (dentro del rango de 10)
        $loteCincoDias = Lote::create([
            'codigo_lote' => 'LOT-PROX-5D',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 15.0,
            'cantidad_inicial' => 15.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(5)->toDateString(),
        ]);

        $resultado = app(ObtenerLotesProximosVencer::class)->ejecutar(
            filtros: ['dias' => 10]
        );

        // Solo el lote de 5 dias, el de 15 dias (beforeEach) queda fuera
        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($loteCincoDias->id);

        // Lote que vence en 25 dias (fuera del rango de 20)
        $loteLejano = Lote::create([
            'codigo_lote' => 'LOT-PROX-25D',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 20.0,
            'cantidad_inicial' => 20.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(25)->toDateString(),
        ]);

        $resultado2 = app(ObtenerLotesProximosVencer::class)->ejecutar(
            filtros: ['dias' => 30]
        );

        // Deberia incluir los 3 lotes (15d, 5d, 25d)
        expect($resultado2)->toHaveCount(3);
    });

    it('excluye lotes ya vencidos', function () {
        Lote::create([
            'codigo_lote' => 'LOT-VENC',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonths(2)->toDateString(),
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ]);

        $resultado = app(ObtenerLotesProximosVencer::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
    });

    it('excluye lotes con vencimiento muy lejano', function () {
        Lote::create([
            'codigo_lote' => 'LOT-LEJ-1',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 50.0,
            'cantidad_inicial' => 50.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
        ]);

        $resultado = app(ObtenerLotesProximosVencer::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
    });

    it('filtra por producto_id', function () {
        $otroProducto = Producto::factory()->create();
        Lote::create([
            'codigo_lote' => 'LOT-PROX-OTRO',
            'producto_id' => $otroProducto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 10.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(10)->toDateString(),
        ]);

        $resultado = app(ObtenerLotesProximosVencer::class)->ejecutar(
            filtros: ['producto_id' => $this->producto->id]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->producto_id)->toBe($this->producto->id);
    });

    it('retorna vacio cuando no hay lotes proximos a vencer', function () {
        Lote::where('id', $this->loteProximo->id)->update([
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
        ]);

        $resultado = app(ObtenerLotesProximosVencer::class)->ejecutar();

        expect($resultado)->toBeEmpty();
    });
});

// ──────────────────────────────────────────────
// ObtenerLotesVencidos
// ──────────────────────────────────────────────

describe('ObtenerLotesVencidos', function () {
    beforeEach(function () {
        $this->producto = Producto::factory()->create();
        $this->ubicacion = Ubicacion::create([
            'nombre' => 'Almacén General',
            'tipo' => 'almacen',
            'estado' => 1,
        ]);

        $this->loteVencido = Lote::create([
            'codigo_lote' => 'LOT-VENC-1',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 20.0,
            'cantidad_inicial' => 20.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonths(3)->toDateString(),
            'fecha_vencimiento' => now()->subDays(10)->toDateString(),
        ]);
    });

    it('retorna lotes marcados como vencidos', function () {
        $resultado = app(ObtenerLotesVencidos::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->id)->toBe($this->loteVencido->id);
    });

    it('retorna lotes con fecha de vencimiento pasada y stock disponible', function () {
        Lote::create([
            'codigo_lote' => 'LOT-VENC-2',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 15.0,
            'cantidad_inicial' => 15.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonths(4)->toDateString(),
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ]);

        $resultado = app(ObtenerLotesVencidos::class)->ejecutar();

        expect($resultado)->toHaveCount(2);
    });

    it('excluye lotes con fecha vencida pero sin stock disponible', function () {
        Lote::create([
            'codigo_lote' => 'LOT-VENC-AGOT',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 0.0,
            'cantidad_inicial' => 10.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonths(4)->toDateString(),
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ]);

        $resultado = app(ObtenerLotesVencidos::class)->ejecutar();

        expect($resultado)->toHaveCount(1);
    });

    it('filtra por producto_id', function () {
        $otroProducto = Producto::factory()->create();
        Lote::create([
            'codigo_lote' => 'LOT-VENC-OTRO',
            'producto_id' => $otroProducto->id,
            'estado' => EstadoLote::Vencido,
            'cantidad_disponible' => 5.0,
            'cantidad_inicial' => 5.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->subMonths(2)->toDateString(),
            'fecha_vencimiento' => now()->subDays(5)->toDateString(),
        ]);

        $resultado = app(ObtenerLotesVencidos::class)->ejecutar(
            filtros: ['producto_id' => $this->producto->id]
        );

        expect($resultado)->toHaveCount(1);
        expect($resultado->first()->producto_id)->toBe($this->producto->id);
    });

    it('retorna vacio cuando no hay lotes vencidos ni vencidos con stock', function () {
        Lote::where('id', $this->loteVencido->id)->update([
            'estado' => EstadoLote::Agotado,
            'cantidad_disponible' => 0.0,
        ]);

        $resultado = app(ObtenerLotesVencidos::class)->ejecutar();

        expect($resultado)->toBeEmpty();
    });
});
