<?php

declare(strict_types=1);

namespace Tests\Feature\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\HabitacionesEspacios\EstadoStock;
use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\ProductoKit;
use App\Models\Inventario\Stock as InventarioStock;
use App\Models\Shared\Stock as SharedStock;
use App\Models\User;
use App\UseCases\Habitaciones\Mutations\AsignarPackAHabitacion;
use App\UseCases\Habitaciones\Queries\VerificarDiscrepanciasHabitacion;
use App\UseCases\Shared\Mutations\RegistrarConsumoStock;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Helpers ─────────────────────────────────────────────────────────────────

function crearHabitacionBase(): Habitacion
{
    $categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();

    return Habitacion::create([
        'codigo' => 'HAB-9999',
        'numero' => 9999,
        'slug' => 'habitacion-9999',
        'nombre' => 'Habitación Tests',
        'categoria_id' => $categoria->id,
        'ubicacion_id' => $ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);
}

function crearProductoConVariante(string $codigoVariante = 'VAR-PACK-001'): array
{
    $producto = Producto::factory()->create();
    $variante = ProductoVariante::create([
        'producto_id' => $producto->id,
        'codigo' => $codigoVariante,
        'nombre_variante' => 'Variante '.$codigoVariante,
        'precio_compra' => 5.0,
        'precio_venta' => 10.0,
        'estado' => 1,
    ]);

    return compact('producto', 'variante');
}

function crearStockEnBodega(int $productoId, int $ubicacionId, int $varianteId, float $cantidad = 50.0): InventarioStock
{
    return InventarioStock::create([
        'producto_id' => $productoId,
        'ubicacion_id' => $ubicacionId,
        'producto_variante_id' => $varianteId,
        'cantidad' => $cantidad,
    ]);
}

// ─── Setup ───────────────────────────────────────────────────────────────────

beforeEach(function (): void {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);

    $this->user = User::factory()->create();
    $this->ubicacionBodega = Ubicacion::where('nombre', 'Almacén General')->firstOrFail();
    $this->habitacion = crearHabitacionBase();
});

// ─── AsignarPackAHabitacion ──────────────────────────────────────────────────

describe('AsignarPackAHabitacion', function () {

    it('asigna un pack a la habitación descontando stock de la bodega', function () {
        $pack = Producto::factory()->create(['nombre' => 'Pack Bienvenida']);

        $item1 = crearProductoConVariante('VAR-PACK-A1');
        $item2 = crearProductoConVariante('VAR-PACK-A2');

        ProductoKit::create([
            'producto_padre_id' => $pack->id,
            'producto_variante_id' => $item1['variante']->id,
            'cantidad' => 2.0,
        ]);
        ProductoKit::create([
            'producto_padre_id' => $pack->id,
            'producto_variante_id' => $item2['variante']->id,
            'cantidad' => 1.0,
        ]);

        crearStockEnBodega($item1['producto']->id, $this->ubicacionBodega->id, $item1['variante']->id, 100.0);
        crearStockEnBodega($item2['producto']->id, $this->ubicacionBodega->id, $item2['variante']->id, 50.0);

        $resultado = app(AsignarPackAHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
            productoPackId: $pack->id,
            bodegaOrigenId: $this->ubicacionBodega->id,
            cantidadPacks: 1.0,
            creadoPorId: $this->user->id,
        );

        expect($resultado)->toHaveCount(2);
        expect($resultado[0])->toHaveKeys(['variante_id', 'cantidad_asignada', 'stock_id', 'lote_id']);
        expect($resultado[0]['cantidad_asignada'])->toBe(2.0);
        expect($resultado[1]['cantidad_asignada'])->toBe(1.0);

        $stockBodega1 = $item1['variante']->fresh();
        expect(InventarioStock::find($resultado[0]['stock_id']))->not->toBeNull();

        $shared = SharedStock::where('stockable_type', Habitacion::class)
            ->where('stockable_id', $this->habitacion->id)
            ->get();
        expect($shared)->toHaveCount(2);
    });

    it('lanza InvalidArgumentException si cantidadPacks es cero o negativo', function () {
        expect(fn () => app(AsignarPackAHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
            productoPackId: 1,
            bodegaOrigenId: 1,
            cantidadPacks: 0,
        ))->toThrow(\InvalidArgumentException::class, 'mayor a cero');

        expect(fn () => app(AsignarPackAHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
            productoPackId: 1,
            bodegaOrigenId: 1,
            cantidadPacks: -1,
        ))->toThrow(\InvalidArgumentException::class, 'mayor a cero');
    });

    it('lanza RuntimeException si el stock es insuficiente', function () {
        $pack = Producto::factory()->create();
        $item = crearProductoConVariante('VAR-PACK-B1');

        ProductoKit::create([
            'producto_padre_id' => $pack->id,
            'producto_variante_id' => $item['variante']->id,
            'cantidad' => 10.0,
        ]);

        crearStockEnBodega($item['producto']->id, $this->ubicacionBodega->id, $item['variante']->id, 1.0);

        expect(fn () => app(AsignarPackAHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
            productoPackId: $pack->id,
            bodegaOrigenId: $this->ubicacionBodega->id,
            cantidadPacks: 1.0,
        ))->toThrow(\RuntimeException::class, 'Stock insuficiente');
    });

    it('acumula cantidad en SharedStock existente si ya hay un registro', function () {
        $pack = Producto::factory()->create();
        $item = crearProductoConVariante('VAR-PACK-C1');

        ProductoKit::create([
            'producto_padre_id' => $pack->id,
            'producto_variante_id' => $item['variante']->id,
            'cantidad' => 3.0,
        ]);

        crearStockEnBodega($item['producto']->id, $this->ubicacionBodega->id, $item['variante']->id, 100.0);

        SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $this->habitacion->id,
            'producto_variante_id' => $item['variante']->id,
            'cantidad_ideal' => 5.0,
            'cantidad_actual' => 5.0,
        ]);

        app(AsignarPackAHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
            productoPackId: $pack->id,
            bodegaOrigenId: $this->ubicacionBodega->id,
            cantidadPacks: 1.0,
        );

        $shared = SharedStock::where('stockable_type', Habitacion::class)
            ->where('stockable_id', $this->habitacion->id)
            ->where('producto_variante_id', $item['variante']->id)
            ->first();

        expect((float) $shared->cantidad_actual)->toBe(8.0);
        expect((float) $shared->cantidad_ideal)->toBe(8.0);
    });

    it('lanza ModelNotFoundException si la habitación no existe', function () {
        expect(fn () => app(AsignarPackAHabitacion::class)->execute(
            habitacionId: 999999,
            productoPackId: 1,
            bodegaOrigenId: 1,
        ))->toThrow(ModelNotFoundException::class);
    });
});

// ─── RegistrarConsumoStock ────────────────────────────────────────────────────

describe('RegistrarConsumoStock', function () {

    beforeEach(function (): void {
        $this->producto = Producto::factory()->create();
        $this->variante = ProductoVariante::create([
            'producto_id' => $this->producto->id,
            'codigo' => 'VAR-CONSUMO-001',
            'nombre_variante' => 'Variante Consumo',
            'precio_compra' => 5.0,
            'precio_venta' => 10.0,
            'estado' => 1,
        ]);

        $this->lote = Lote::create([
            'codigo_lote' => 'LOT-CONSUMO-001',
            'producto_id' => $this->producto->id,
            'producto_variante_id' => $this->variante->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 50.0,
            'cantidad_inicial' => 50.0,
            'ubicacion_id' => $this->ubicacionBodega->id,
            'fecha_recepcion' => now()->toDateString(),
            'costo_unitario' => 15.50,
            'costo_total' => 775.00,
        ]);

        $this->stock = SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $this->habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'lote_id' => $this->lote->id,
            'cantidad_ideal' => 20.0,
            'cantidad_actual' => 20.0,
        ]);
    });

    it('consume stock y decrementa cantidad_actual', function () {
        app(RegistrarConsumoStock::class)->execute(
            stockId: $this->stock->id,
            cantidad: 5.0,
            motivo: 'consumo_test',
            creadoPorId: $this->user->id,
        );

        expect((float) $this->stock->fresh()->cantidad_actual)->toBe(15.0);
    });

    it('registra un MovimientoStock de tipo CONSUMO con costo', function () {
        app(RegistrarConsumoStock::class)->execute(
            stockId: $this->stock->id,
            cantidad: 3.0,
            motivo: 'consumo_habitacion',
            creadoPorId: $this->user->id,
        );

        $movimiento = MovimientoStock::where('documento_tipo', 'consumo_stock')
            ->where('documento_id', $this->stock->id)
            ->first();

        expect($movimiento)->not->toBeNull()
            ->and($movimiento->tipo)->toBe('CONSUMO')
            ->and((float) $movimiento->cantidad)->toBe(-3.0)
            ->and($movimiento->producto_id)->toBe($this->producto->id);
    });

    it('lanza RuntimeException si el stock es insuficiente', function () {
        expect(fn () => app(RegistrarConsumoStock::class)->execute(
            stockId: $this->stock->id,
            cantidad: 99.0,
            motivo: 'exceso',
        ))->toThrow(\RuntimeException::class, 'Stock insuficiente');
    });

    it('lanza InvalidArgumentException si cantidad es cero o negativo', function () {
        expect(fn () => app(RegistrarConsumoStock::class)->execute(
            stockId: $this->stock->id,
            cantidad: 0,
        ))->toThrow(\InvalidArgumentException::class, 'mayor a cero');

        expect(fn () => app(RegistrarConsumoStock::class)->execute(
            stockId: $this->stock->id,
            cantidad: -1,
        ))->toThrow(\InvalidArgumentException::class, 'mayor a cero');
    });

    it('actualiza ultima_verificacion después del consumo', function () {
        app(RegistrarConsumoStock::class)->execute(
            stockId: $this->stock->id,
            cantidad: 1.0,
            motivo: 'verificacion',
        );

        expect($this->stock->fresh()->ultima_verificacion)->not->toBeNull();
    });
});

// ─── VerificarDiscrepanciasHabitacion ────────────────────────────────────────

describe('VerificarDiscrepanciasHabitacion', function () {

    beforeEach(function (): void {
        $this->producto = Producto::factory()->create();
        $this->variante = ProductoVariante::create([
            'producto_id' => $this->producto->id,
            'codigo' => 'VAR-DISC-001',
            'nombre_variante' => 'Variante Discrepancia',
            'precio_compra' => 5.0,
            'precio_venta' => 10.0,
            'estado' => 1,
        ]);
    });

    it('retorna discrepancia Completo cuando actual es igual al ideal', function () {
        SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $this->habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 10.0,
            'cantidad_actual' => 10.0,
        ]);

        $resultados = app(VerificarDiscrepanciasHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
        );

        expect($resultados)->toHaveCount(1);
        expect($resultados[0]['estado'])->toBe(EstadoStock::Completo);
        expect($resultados[0]['diferencia'])->toBe(0.0);
    });

    it('retorna discrepancia Faltante cuando actual es menor al ideal', function () {
        SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $this->habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 10.0,
            'cantidad_actual' => 4.0,
        ]);

        $resultados = app(VerificarDiscrepanciasHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
        );

        expect($resultados[0]['estado'])->toBe(EstadoStock::Faltante);
        expect($resultados[0]['diferencia'])->toBe(-6.0);
    });

    it('retorna discrepancia Sobrante cuando actual es mayor al ideal', function () {
        SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $this->habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 10.0,
            'cantidad_actual' => 15.0,
        ]);

        $resultados = app(VerificarDiscrepanciasHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
        );

        expect($resultados[0]['estado'])->toBe(EstadoStock::Sobrante);
        expect($resultados[0]['diferencia'])->toBe(5.0);
    });

    it('filtra por EstadoStock cuando se especifica', function () {
        $variante2 = ProductoVariante::create([
            'producto_id' => $this->producto->id,
            'codigo' => 'VAR-DISC-002',
            'nombre_variante' => 'Variante 2',
            'precio_compra' => 5.0,
            'precio_venta' => 10.0,
            'estado' => 1,
        ]);

        SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $this->habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 10.0,
            'cantidad_actual' => 10.0,
        ]);

        SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $this->habitacion->id,
            'producto_variante_id' => $variante2->id,
            'cantidad_ideal' => 10.0,
            'cantidad_actual' => 1.0,
        ]);

        $resultados = app(VerificarDiscrepanciasHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
            filtrarPor: EstadoStock::Faltante,
        );

        expect($resultados)->toHaveCount(1)
            ->and($resultados[0]['estado'])->toBe(EstadoStock::Faltante);
    });

    it('retorna colección vacía cuando no hay stocks para la habitación', function () {
        $resultados = app(VerificarDiscrepanciasHabitacion::class)->execute(
            habitacionId: 999999,
        );

        expect($resultados)->toHaveCount(0);
    });

    it('incluye datos descriptivos de la habitación en cada resultado', function () {
        SharedStock::create([
            'stockable_type' => Habitacion::class,
            'stockable_id' => $this->habitacion->id,
            'producto_variante_id' => $this->variante->id,
            'cantidad_ideal' => 5.0,
            'cantidad_actual' => 5.0,
        ]);

        $resultados = app(VerificarDiscrepanciasHabitacion::class)->execute(
            habitacionId: $this->habitacion->id,
        );

        expect($resultados[0]['habitacion_id'])->toBe($this->habitacion->id)
            ->and($resultados[0]['habitacion_codigo'])->toBe($this->habitacion->codigo)
            ->and($resultados[0]['variante_nombre'])->toBe($this->variante->nombre_variante);
    });
});
