<?php

declare(strict_types=1);

namespace Tests\Feature\Limpieza;

use App\Enums\Catalogos\CatalogoTipo as CatalogoTipoEnum;
use App\Enums\Inventario\EstadoLote;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Pages\Limpieza\ControlLavanderia;
use App\Interactors\Limpieza\Lavanderia\RegistrarConsumoJornadaLavanderia;
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

function obtenerUnidadMedidaJornadaTest(): Catalogo
{
    $tipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::UNIDAD_MEDIDA->value],
        ['nombre' => 'Unidad de Medida']
    );

    return Catalogo::query()->firstOrCreate(
        ['codigo' => 'UNI_LITROS'],
        ['nombre' => 'Litros', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $tipo->id]
    );
}

it('registra consumo de insumos por jornada de lavado correctamente', function (): void {
    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaJornadaTest();

    $catTipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::CATEGORIA_PRODUCTO->value],
        ['nombre' => 'Categorías de Producto']
    );
    $catQuimicos = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_QUIMICOS'],
        ['nombre' => 'Químicos de Lavado', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    // 1. Detergente Líquido
    $prodDet = Producto::query()->create([
        'codigo' => 'QUIM-DET-'.uniqid(),
        'nombre' => 'Detergente Industrial',
        'categoria_id' => $catQuimicos->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $varDet = ProductoVariante::query()->create([
        'producto_id' => $prodDet->id,
        'codigo' => 'VAR-DET-'.uniqid(),
        'nombre_variante' => 'Galón 5L',
        'sku' => 'SKU-DET-'.uniqid(),
    ]);

    $loteDet = Lote::query()->create([
        'codigo_lote' => 'LOTE-DET-001',
        'producto_id' => $prodDet->id,
        'producto_variante_id' => $varDet->id,
        'ubicacion_id' => $lavanderia->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 20.0,
        'cantidad_inicial' => 20.0,
        'costo_unitario' => 15.50,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $stockDet = Stock::query()->create([
        'ubicacion_id' => $lavanderia->id,
        'producto_id' => $prodDet->id,
        'producto_variante_id' => $varDet->id,
        'lote_id' => $loteDet->id,
        'cantidad' => 20.0,
    ]);

    // 2. Suavizante
    $prodSuav = Producto::query()->create([
        'codigo' => 'QUIM-SUAV-'.uniqid(),
        'nombre' => 'Suavizante Textil',
        'categoria_id' => $catQuimicos->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $varSuav = ProductoVariante::query()->create([
        'producto_id' => $prodSuav->id,
        'codigo' => 'VAR-SUAV-'.uniqid(),
        'nombre_variante' => 'Galón 5L',
        'sku' => 'SKU-SUAV-'.uniqid(),
    ]);

    $loteSuav = Lote::query()->create([
        'codigo_lote' => 'LOTE-SUAV-001',
        'producto_id' => $prodSuav->id,
        'producto_variante_id' => $varSuav->id,
        'ubicacion_id' => $lavanderia->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 15.0,
        'cantidad_inicial' => 15.0,
        'costo_unitario' => 12.00,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $stockSuav = Stock::query()->create([
        'ubicacion_id' => $lavanderia->id,
        'producto_id' => $prodSuav->id,
        'producto_variante_id' => $varSuav->id,
        'lote_id' => $loteSuav->id,
        'cantidad' => 15.0,
    ]);

    $interactor = app(RegistrarConsumoJornadaLavanderia::class);
    $resultado = $interactor->execute(
        ubicacionLavanderiaId: $lavanderia->id,
        fechaJornada: '2026-08-20',
        turno: 'manana',
        insumos: [
            ['stock_id' => $stockDet->id, 'cantidad' => 4.0, 'notas' => '4 Litros para toallas'],
            ['stock_id' => $stockSuav->id, 'cantidad' => 2.5, 'notas' => '2.5 Litros para sábanas'],
        ],
        operadorNombre: 'María Gómez',
        kilosLavados: 120.0,
        observacionesGenerales: 'Lavado intensivo de turno matutino',
    );

    expect($resultado['total_insumos'])->toBe(2);
    expect($resultado['total_cantidad'])->toBe(6.5);

    $stockDet->refresh();
    expect((float) $stockDet->cantidad)->toBe(16.0);

    $stockSuav->refresh();
    expect((float) $stockSuav->cantidad)->toBe(12.5);

    $movimientos = MovimientoStock::query()
        ->where('tipo', 'CONSUMO_LAVANDERIA')
        ->where('documento_tipo', 'jornada_lavanderia')
        ->get();

    expect($movimientos)->toHaveCount(2);
    expect($movimientos->first()?->referencia)->toContain('Consumo Jornada Manana');
    expect($movimientos->first()?->referencia)->toContain('María Gómez');
    expect($movimientos->first()?->referencia)->toContain('120 kg');
});

it('lanza excepcion si se intenta consumir mas insumos de los disponibles en lavanderia', function (): void {
    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaJornadaTest();

    $catTipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::CATEGORIA_PRODUCTO->value],
        ['nombre' => 'Categorías de Producto']
    );
    $catQuimicos = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_QUIMICOS'],
        ['nombre' => 'Químicos', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prod = Producto::query()->create([
        'codigo' => 'QUIM-CLORO-'.uniqid(),
        'nombre' => 'Cloro al 6%',
        'categoria_id' => $catQuimicos->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $var = ProductoVariante::query()->create([
        'producto_id' => $prod->id,
        'codigo' => 'VAR-CLORO-'.uniqid(),
        'nombre_variante' => 'Litro',
        'sku' => 'SKU-CLORO-'.uniqid(),
    ]);

    $stock = Stock::query()->create([
        'ubicacion_id' => $lavanderia->id,
        'producto_id' => $prod->id,
        'producto_variante_id' => $var->id,
        'cantidad' => 3.0,
    ]);

    $interactor = app(RegistrarConsumoJornadaLavanderia::class);
    $interactor->execute(
        ubicacionLavanderiaId: $lavanderia->id,
        fechaJornada: '2026-08-20',
        turno: 'tarde',
        insumos: [
            ['stock_id' => $stock->id, 'cantidad' => 5.0],
        ],
    );
})->throws(\RuntimeException::class, 'Stock insuficiente');

it('permite registrar consumo de jornada desde el componente livewire de ControlLavanderia', function (): void {
    $rol = Role::firstOrCreate([
        'name' => config('filament-shield.super_admin.name', 'super_admin'),
        'guard_name' => 'web',
    ]);
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole($rol);

    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaJornadaTest();

    $catTipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::CATEGORIA_PRODUCTO->value],
        ['nombre' => 'Categorías de Producto']
    );
    $catQuimicos = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_QUIMICOS'],
        ['nombre' => 'Químicos', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prod = Producto::query()->create([
        'codigo' => 'QUIM-DESM-'.uniqid(),
        'nombre' => 'Desmanchador de Blancos',
        'categoria_id' => $catQuimicos->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $var = ProductoVariante::query()->create([
        'producto_id' => $prod->id,
        'codigo' => 'VAR-DESM-'.uniqid(),
        'nombre_variante' => 'Frasco 1L',
        'sku' => 'SKU-DESM-'.uniqid(),
    ]);

    $lote = Lote::query()->create([
        'codigo_lote' => 'LOTE-DESM-001',
        'producto_id' => $prod->id,
        'producto_variante_id' => $var->id,
        'ubicacion_id' => $lavanderia->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10.0,
        'cantidad_inicial' => 10.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $stock = Stock::query()->create([
        'ubicacion_id' => $lavanderia->id,
        'producto_id' => $prod->id,
        'producto_variante_id' => $var->id,
        'lote_id' => $lote->id,
        'cantidad' => 10.0,
    ]);

    Livewire::actingAs($user)
        ->test(ControlLavanderia::class)
        ->set('activeTab', 'jornada')
        ->set('jornadaData.fecha', '2026-08-20')
        ->set('jornadaData.turno', 'manana')
        ->set('jornadaData.operador_nombre', 'Carlos Lavandero')
        ->set('jornadaData.kilos_lavados', 90.0)
        ->set('jornadaData.insumos', [
            [
                'stock_id' => $stock->id,
                'cantidad' => 2.0,
                'notas' => 'Uso en toallas de spa',
            ],
        ])
        ->call('submitJornada')
        ->assertHasNoErrors()
        ->assertNotified('Jornada de Lavado Registrada');

    $stock->refresh();
    expect((float) $stock->cantidad)->toBe(8.0);
});

it('registra consumo de insumos y mermas de ropa blanca en la misma jornada', function (): void {
    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaJornadaTest();

    $catTipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::CATEGORIA_PRODUCTO->value],
        ['nombre' => 'Categorías de Producto']
    );
    $cat = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_MIX_JRN'],
        ['nombre' => 'Blancos e Insumos', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    // Químico
    $prodDet = Producto::query()->create([
        'codigo' => 'QUIM-MJ-'.uniqid(),
        'nombre' => 'Detergente Industrial',
        'categoria_id' => $cat->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);
    $varDet = ProductoVariante::query()->create([
        'producto_id' => $prodDet->id,
        'codigo' => 'VAR-MJ-'.uniqid(),
        'nombre_variante' => '5L',
        'sku' => 'SKU-MJ-'.uniqid(),
    ]);
    $stockDet = Stock::query()->create([
        'ubicacion_id' => $lavanderia->id,
        'producto_id' => $prodDet->id,
        'producto_variante_id' => $varDet->id,
        'cantidad' => 10.0,
    ]);

    // Sábana dañada (merma)
    $prodSab = Producto::query()->create([
        'codigo' => 'SAB-MJ-'.uniqid(),
        'nombre' => 'Sábana King',
        'categoria_id' => $cat->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);
    $varSab = ProductoVariante::query()->create([
        'producto_id' => $prodSab->id,
        'codigo' => 'VAR-SAB-MJ-'.uniqid(),
        'nombre_variante' => 'Blanca',
        'sku' => 'SKU-SAB-MJ-'.uniqid(),
    ]);
    $stockSab = Stock::query()->create([
        'ubicacion_id' => $lavanderia->id,
        'producto_id' => $prodSab->id,
        'producto_variante_id' => $varSab->id,
        'cantidad' => 15.0,
    ]);

    $interactor = app(RegistrarConsumoJornadaLavanderia::class);
    $resultado = $interactor->execute(
        ubicacionLavanderiaId: $lavanderia->id,
        fechaJornada: '2026-08-20',
        turno: 'tarde',
        insumos: [
            ['stock_id' => $stockDet->id, 'cantidad' => 3.0],
        ],
        mermas: [
            ['stock_id' => $stockSab->id, 'cantidad' => 2.0, 'notas' => 'Rotura en tambor'],
        ],
        operadorNombre: 'Pedro Supervisor',
        kilosLavados: 140.0,
    );

    expect($resultado['total_insumos'])->toBe(1);
    expect($resultado['total_mermas'])->toBe(2);

    $stockDet->refresh();
    expect((float) $stockDet->cantidad)->toBe(7.0);

    $stockSab->refresh();
    expect((float) $stockSab->cantidad)->toBe(13.0);

    $movimientos = MovimientoStock::query()
        ->where('tipo', 'CONSUMO_LAVANDERIA')
        ->get();

    expect($movimientos->where('documento_tipo', 'jornada_lavanderia'))->not->toBeEmpty();
    expect($movimientos->where('documento_tipo', 'merma_jornada_lavanderia'))->not->toBeEmpty();
});
