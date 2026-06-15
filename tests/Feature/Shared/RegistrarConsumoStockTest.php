<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Shared\Stock;
use App\Models\User;
use App\UseCases\Shared\Mutations\RegistrarConsumoStock;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Database\Seeders\UbicacionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
        UbicacionSeeder::class,
    ]);

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->variante = ProductoVariante::create([
        'producto_id' => $this->producto->id,
        'codigo' => 'VAR-TEST-001',
        'nombre_variante' => 'Variante de prueba',
        'precio_compra' => 10.0,
        'precio_venta' => 15.0,
        'estado' => 1,
    ]);

    $this->ubicacion = Ubicacion::where('nombre', 'Ala Norte')->firstOrFail();
    $this->lote = Lote::create([
        'codigo_lote' => 'LOT-TEST-001',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $this->categoria = Catalogo::where('codigo', 'CAT_HAB_ESTANDAR')->firstOrFail();
    $this->habitacion = Habitacion::create([
        'codigo' => 'HAB-0301',
        'numero' => 301,
        'slug' => 'habitacion-301',
        'nombre' => 'Habitación 301',
        'categoria_id' => $this->categoria->id,
        'ubicacion_id' => $this->ubicacion->id,
        'estado' => EstadoHabitacion::Activa,
    ]);
});

it('puede consumir stock de un recurso compartido de habitacion', function () {
    $stock = Stock::create([
        'stockable_type' => Habitacion::class,
        'stockable_id' => $this->habitacion->id,
        'producto_variante_id' => $this->variante->id,
        'lote_id' => $this->lote->id,
        'cantidad_ideal' => 10.0,
        'cantidad_actual' => 10.0,
    ]);

    $useCase = app(RegistrarConsumoStock::class);
    $useCase->execute(
        stockId: $stock->id,
        cantidad: 4.0,
        motivo: 'Consumo por huésped',
        creadoPorId: $this->user->id
    );

    // Verify stock is decremented
    expect($stock->fresh()->cantidad_actual)->toBe('6.0000');

    // Verify MovimientoStock is logged
    $movimiento = MovimientoStock::where('documento_id', $stock->id)
        ->where('documento_tipo', 'consumo_stock')
        ->first();

    expect($movimiento)->not->toBeNull()
        ->and($movimiento->cantidad)->toBe(-4.0)
        ->and($movimiento->producto_id)->toBe($this->producto->id)
        ->and($movimiento->lote_id)->toBe($this->lote->id);
});

it('lanza excepcion si la cantidad es insuficiente', function () {
    $stock = Stock::create([
        'stockable_type' => Habitacion::class,
        'stockable_id' => $this->habitacion->id,
        'producto_variante_id' => $this->variante->id,
        'lote_id' => $this->lote->id,
        'cantidad_ideal' => 10.0,
        'cantidad_actual' => 3.0,
    ]);

    $useCase = app(RegistrarConsumoStock::class);

    expect(fn () => $useCase->execute($stock->id, 5.0, 'Consumo', $this->user->id))
        ->toThrow(\RuntimeException::class, 'Stock insuficiente');
});

it('lanza excepcion si la cantidad a consumir es menor o igual a cero', function () {
    $stock = Stock::create([
        'stockable_type' => Habitacion::class,
        'stockable_id' => $this->habitacion->id,
        'producto_variante_id' => $this->variante->id,
        'lote_id' => $this->lote->id,
        'cantidad_ideal' => 10.0,
        'cantidad_actual' => 10.0,
    ]);

    $useCase = app(RegistrarConsumoStock::class);

    expect(fn () => $useCase->execute($stock->id, 0.0, 'Consumo', $this->user->id))
        ->toThrow(\InvalidArgumentException::class, 'La cantidad a consumir debe ser mayor a cero.');

    expect(fn () => $useCase->execute($stock->id, -1.0, 'Consumo', $this->user->id))
        ->toThrow(\InvalidArgumentException::class, 'La cantidad a consumir debe ser mayor a cero.');
});
