<?php

declare(strict_types=1);

namespace Tests\Feature\Limpieza;

use App\Enums\Catalogos\CatalogoTipo as CatalogoTipoEnum;
use App\Enums\Inventario\EstadoLote;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Pages\Limpieza\ControlLavanderia;
use App\Interactors\Limpieza\Lavanderia\RegistrarEntradaInsumosLavanderia;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Ubicacion::query()->firstOrCreate(
        ['tipo' => 'lavanderia'],
        ['nombre' => 'Lavandería Central', 'estado' => 1]
    );
});

function obtenerUnidadMedidaEntradaInsumosTest(): Catalogo
{
    $tipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::UNIDAD_MEDIDA->value],
        ['nombre' => 'Unidad de Medida']
    );

    return Catalogo::query()->firstOrCreate(
        ['codigo' => 'UNI_LITROS_EI'],
        ['nombre' => 'Litros', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $tipo->id]
    );
}

it('registra ingreso directo de insumos a lavanderia correctamente', function (): void {
    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaEntradaInsumosTest();

    $catTipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::CATEGORIA_PRODUCTO->value],
        ['nombre' => 'Categorías de Producto']
    );
    $cat = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_DET_EI'],
        ['nombre' => 'Químicos', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prod = Producto::query()->create([
        'codigo' => 'PROD-EI-'.uniqid(),
        'nombre' => 'Detergente Industrial 20L',
        'categoria_id' => $cat->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $var = ProductoVariante::query()->create([
        'producto_id' => $prod->id,
        'codigo' => 'VAR-EI-'.uniqid(),
        'nombre_variante' => 'Galón',
        'sku' => 'SKU-EI-'.uniqid(),
    ]);

    $interactor = app(RegistrarEntradaInsumosLavanderia::class);
    $resultado = $interactor->execute(
        tipoOrigen: 'compra',
        items: [
            [
                'producto_variante_id' => $var->id,
                'cantidad' => 10.0,
                'codigo_lote' => 'LOTE-DIRECT-01',
                'costo_unitario' => 25.00,
            ],
        ],
        ubicacionLavanderiaId: $lavanderia->id,
        documentoReferencia: 'FACT-1234',
    );

    expect($resultado['total_items'])->toBe(1);
    expect($resultado['total_cantidad'])->toBe(10.0);

    $stock = Stock::query()
        ->where('ubicacion_id', $lavanderia->id)
        ->where('producto_variante_id', $var->id)
        ->first();

    expect($stock)->not->toBeNull();
    expect((float) $stock?->cantidad)->toBe(10.0);

    $mov = MovimientoStock::query()
        ->where('tipo', 'ENTRADA_STOCK')
        ->where('producto_id', $prod->id)
        ->first();

    expect($mov)->not->toBeNull();
    expect($mov?->referencia)->toContain('FACT-1234');
});

it('registra traslado de insumos desde bodega central a lavanderia', function (): void {
    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $bodega = Ubicacion::query()->create([
        'codigo' => 'BOD-CENTRAL-'.uniqid(),
        'nombre' => 'Bodega Central Químicos',
        'tipo' => 'almacen',
        'estado' => EstadoGeneral::Activo,
    ]);

    $unidad = obtenerUnidadMedidaEntradaInsumosTest();
    $catTipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::CATEGORIA_PRODUCTO->value],
        ['nombre' => 'Categorías de Producto']
    );
    $cat = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_SUAV_EI'],
        ['nombre' => 'Suavizantes', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prod = Producto::query()->create([
        'codigo' => 'PROD-SUAV-'.uniqid(),
        'nombre' => 'Suavizante Concentrado',
        'categoria_id' => $cat->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $var = ProductoVariante::query()->create([
        'producto_id' => $prod->id,
        'codigo' => 'VAR-SUAV-'.uniqid(),
        'nombre_variante' => 'Garrafa 10L',
        'sku' => 'SKU-SUAV-'.uniqid(),
    ]);

    $lote = Lote::query()->create([
        'codigo_lote' => 'LOTE-BOD-001',
        'producto_id' => $prod->id,
        'producto_variante_id' => $var->id,
        'ubicacion_id' => $bodega->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $stockBodega = Stock::query()->create([
        'ubicacion_id' => $bodega->id,
        'producto_id' => $prod->id,
        'producto_variante_id' => $var->id,
        'lote_id' => $lote->id,
        'cantidad' => 30.0,
    ]);

    $interactor = app(RegistrarEntradaInsumosLavanderia::class);
    $interactor->execute(
        tipoOrigen: 'bodega',
        items: [
            [
                'producto_variante_id' => $var->id,
                'lote_id' => $lote->id,
                'cantidad' => 12.0,
            ],
        ],
        ubicacionLavanderiaId: $lavanderia->id,
        bodegaOrigenId: $bodega->id,
        documentoReferencia: 'TRAS-888',
    );

    $stockBodega->refresh();
    expect((float) $stockBodega->cantidad)->toBe(18.0);

    $stockLav = Stock::query()
        ->where('ubicacion_id', $lavanderia->id)
        ->where('producto_variante_id', $var->id)
        ->where('lote_id', $lote->id)
        ->first();

    expect($stockLav)->not->toBeNull();
    expect((float) $stockLav?->cantidad)->toBe(12.0);
});

it('permite registrar entrada de insumos desde livewire en ControlLavanderia', function (): void {
    $rol = Role::firstOrCreate([
        'name' => config('filament-shield.super_admin.name', 'super_admin'),
        'guard_name' => 'web',
    ]);
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole($rol);

    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaEntradaInsumosTest();
    $catTipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::CATEGORIA_PRODUCTO->value],
        ['nombre' => 'Categorías de Producto']
    );
    $cat = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_CLORO_EI'],
        ['nombre' => 'Cloro', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prod = Producto::query()->create([
        'codigo' => 'PROD-CL-'.uniqid(),
        'nombre' => 'Cloro Industrial',
        'categoria_id' => $cat->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $var = ProductoVariante::query()->create([
        'producto_id' => $prod->id,
        'codigo' => 'VAR-CL-'.uniqid(),
        'nombre_variante' => 'Botella 1L',
        'sku' => 'SKU-CL-'.uniqid(),
    ]);

    Livewire::actingAs($user)
        ->test(ControlLavanderia::class)
        ->set('activeTab', 'entrada_insumos')
        ->set('entradaInsumosData.tipo_origen', 'compra')
        ->set('entradaInsumosData.documento_referencia', 'REM-456')
        ->set('entradaInsumosData.items', [
            [
                'producto_variante_id' => $var->id,
                'codigo_lote' => 'LOTE-REM-01',
                'costo_unitario' => 18.00,
                'cantidad' => 6.0,
            ],
        ])
        ->call('submitEntradaInsumos')
        ->assertHasNoErrors()
        ->assertNotified('Insumos Ingresados');

    $stock = Stock::query()
        ->where('ubicacion_id', $lavanderia->id)
        ->where('producto_variante_id', $var->id)
        ->first();

    expect($stock)->not->toBeNull();
    expect((float) $stock?->cantidad)->toBe(6.0);
});
