<?php

declare(strict_types=1);

use App\Enums\Catalogos\CatalogoTipo as CatalogoTipoEnum;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Inventario\EstadoLote;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Pages\Limpieza\ControlLavanderia;
use App\Interactors\Limpieza\Lavanderia\RegistrarEntradaDirectaLavanderia;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LavanderiaProceso;
use App\Repository\Models\Shared\Stock as SharedStock;
use App\Repository\Models\User;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerCategoriasBlancosLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesBlancosLavanderia;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Ubicacion::query()->firstOrCreate(
        ['tipo' => 'lavanderia'],
        ['nombre' => 'Lavandería Central', 'estado' => 1]
    );
});

function obtenerTipoCatalogo(string $codigo, string $nombre): CatalogoTipo
{
    return CatalogoTipo::query()->firstOrCreate(
        ['codigo' => $codigo],
        ['nombre' => $nombre]
    );
}

function obtenerUnidadMedidaTest(): Catalogo
{
    $tipo = obtenerTipoCatalogo(CatalogoTipoEnum::UNIDAD_MEDIDA->value, 'Unidad de Medida');

    return Catalogo::query()->firstOrCreate(
        ['codigo' => 'UNI_UD'],
        ['nombre' => 'Unidad', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $tipo->id]
    );
}

it('obtiene categorias activas de productos para lavanderia', function (): void {
    $catTipo = obtenerTipoCatalogo(CatalogoTipoEnum::CATEGORIA_PRODUCTO->value, 'Categorías de Producto');

    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $catTipo->id,
        'codigo' => 'CAT_TEST_BLANCOS_'.uniqid(),
        'nombre' => 'Lencería Test',
        'estado' => EstadoGeneral::Activo,
    ]);

    $unidad = obtenerUnidadMedidaTest();

    $producto = Producto::query()->create([
        'codigo' => 'PROD-TEST-'.uniqid(),
        'nombre' => 'Sábana Test',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id,
        'codigo' => 'VAR-TEST-'.uniqid(),
        'nombre_variante' => 'King',
        'sku' => 'SKU-'.uniqid(),
    ]);

    Lote::query()->create([
        'codigo_lote' => 'LOTE-TEST-01',
        'producto_id' => $producto->id,
        'producto_variante_id' => $variante->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 20.0,
        'cantidad_inicial' => 20.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $categorias = app(ObtenerCategoriasBlancosLavanderia::class)->execute();

    expect($categorias)->toHaveKey($categoria->id);
    expect($categorias[$categoria->id])->toBe('Lencería Test');
});

it('filtra opciones y genera lista de precarga por categoria', function (): void {
    $catTipo = obtenerTipoCatalogo(CatalogoTipoEnum::CATEGORIA_PRODUCTO->value, 'Categorías de Producto');

    $categoria1 = Catalogo::query()->create([
        'catalogo_tipo_id' => $catTipo->id,
        'codigo' => 'CAT_BLANCOS_'.uniqid(),
        'nombre' => 'Lencería A',
        'estado' => EstadoGeneral::Activo,
    ]);

    $categoria2 = Catalogo::query()->create([
        'catalogo_tipo_id' => $catTipo->id,
        'codigo' => 'CAT_OTROS_'.uniqid(),
        'nombre' => 'Limpieza B',
        'estado' => EstadoGeneral::Activo,
    ]);

    $unidad = obtenerUnidadMedidaTest();

    $prod1 = Producto::query()->create([
        'codigo' => 'PROD-1-'.uniqid(),
        'nombre' => 'Sábana 1',
        'categoria_id' => $categoria1->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $var1 = ProductoVariante::query()->create([
        'producto_id' => $prod1->id,
        'codigo' => 'VAR-1-'.uniqid(),
        'nombre_variante' => 'King',
        'sku' => 'SKU-1-'.uniqid(),
    ]);

    Lote::query()->create([
        'codigo_lote' => 'LOTE-1-01',
        'producto_id' => $prod1->id,
        'producto_variante_id' => $var1->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 15.0,
        'cantidad_inicial' => 15.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $prod2 = Producto::query()->create([
        'codigo' => 'PROD-2-'.uniqid(),
        'nombre' => 'Detergente 2',
        'categoria_id' => $categoria2->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $var2 = ProductoVariante::query()->create([
        'producto_id' => $prod2->id,
        'codigo' => 'VAR-2-'.uniqid(),
        'nombre_variante' => 'Galon',
        'sku' => 'SKU-2-'.uniqid(),
    ]);

    Lote::query()->create([
        'codigo_lote' => 'LOTE-2-01',
        'producto_id' => $prod2->id,
        'producto_variante_id' => $var2->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 5.0,
        'cantidad_inicial' => 5.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $query = app(ObtenerOpcionesBlancosLavanderia::class);

    $opcionesCat1 = $query->execute($categoria1->id);
    expect($opcionesCat1)->toHaveKey($var1->id);
    expect($opcionesCat1)->not->toHaveKey($var2->id);

    $precarga = $query->obtenerVariantesParaPrecarga($categoria1->id);
    expect($precarga)->toHaveCount(1);
    expect($precarga[0]['producto_variante_id'])->toBe($var1->id);
});

it('registra entrada directa por lote en lavanderia correctamente', function (): void {
    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaTest();

    $catTipo = obtenerTipoCatalogo(CatalogoTipoEnum::CATEGORIA_PRODUCTO->value, 'Categorías de Producto');
    $categoria = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_GEN'],
        ['nombre' => 'General', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prodA = Producto::query()->create([
        'codigo' => 'PROD-A-'.uniqid(),
        'nombre' => 'Toalla de Manos',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $varA = ProductoVariante::query()->create([
        'producto_id' => $prodA->id,
        'codigo' => 'VAR-A-'.uniqid(),
        'nombre_variante' => 'Blanca',
        'sku' => 'SKU-A-'.uniqid(),
    ]);

    $prodB = Producto::query()->create([
        'codigo' => 'PROD-B-'.uniqid(),
        'nombre' => 'Sábana Matrimonial',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $varB = ProductoVariante::query()->create([
        'producto_id' => $prodB->id,
        'codigo' => 'VAR-B-'.uniqid(),
        'nombre_variante' => 'Blanca',
        'sku' => 'SKU-B-'.uniqid(),
    ]);

    $interactor = app(RegistrarEntradaDirectaLavanderia::class);

    $resultado = $interactor->ejecutarLote(
        items: [
            ['producto_variante_id' => $varA->id, 'cantidad' => 15.0, 'notas' => 'Toallas manchadas'],
            ['producto_variante_id' => $varB->id, 'cantidad' => 10.0, 'notas' => null],
        ],
        ubicacionLavanderiaId: $lavanderia->id,
        creadoPorId: null,
        notasGenerales: 'Entrada masiva de blancos',
    );

    expect($resultado['total_items'])->toBe(2);
    expect($resultado['total_piezas'])->toBe(25.0);

    $stockA = Stock::query()
        ->where('producto_variante_id', $varA->id)
        ->where('ubicacion_id', $lavanderia->id)
        ->first();
    expect($stockA)->not->toBeNull();
    expect((float) $stockA->cantidad)->toBe(15.0);

    $stockB = Stock::query()
        ->where('producto_variante_id', $varB->id)
        ->where('ubicacion_id', $lavanderia->id)
        ->first();
    expect($stockB)->not->toBeNull();
    expect((float) $stockB->cantidad)->toBe(10.0);

    expect(LavanderiaProceso::query()->where('producto_variante_id', $varA->id)->count())->toBe(1);
    expect(MovimientoStock::query()->where('tipo', 'ENTRADA_LAVANDERIA')->count())->toBe(2);
});

it('permite registrar entrada directa desde el componente livewire de ControlLavanderia', function (): void {
    $rol = Role::firstOrCreate([
        'name' => config('filament-shield.super_admin.name', 'super_admin'),
        'guard_name' => 'web',
    ]);
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole($rol);

    $unidad = obtenerUnidadMedidaTest();
    $catTipo = obtenerTipoCatalogo(CatalogoTipoEnum::CATEGORIA_PRODUCTO->value, 'Categorías de Producto');
    $categoria = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_GEN'],
        ['nombre' => 'General', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prod = Producto::query()->create([
        'codigo' => 'PROD-LW-'.uniqid(),
        'nombre' => 'Funda Almohada',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $variante = ProductoVariante::query()->create([
        'producto_id' => $prod->id,
        'codigo' => 'VAR-LW-'.uniqid(),
        'nombre_variante' => 'Estándar',
        'sku' => 'SKU-LW-'.uniqid(),
    ]);

    $bodega = Ubicacion::query()->create([
        'codigo' => 'BOD-TEST-'.uniqid(),
        'nombre' => 'Bodega Piso 1',
        'tipo' => 'almacen',
        'estado' => EstadoGeneral::Activo,
    ]);

    $lote = Lote::query()->create([
        'codigo_lote' => 'LOTE-LW-001',
        'producto_id' => $prod->id,
        'producto_variante_id' => $variante->id,
        'ubicacion_id' => $bodega->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    Stock::query()->create([
        'ubicacion_id' => $bodega->id,
        'producto_id' => $prod->id,
        'producto_variante_id' => $variante->id,
        'lote_id' => $lote->id,
        'cantidad' => 50.0,
    ]);

    Livewire::actingAs($user)
        ->test(ControlLavanderia::class)
        ->set('activeTab', 'entrada')
        ->set('entradaData.tipo_origen', 'ubicacion')
        ->set('entradaData.origen_id', $bodega->id)
        ->set('entradaData.items', [
            [
                'producto_variante_id' => $variante->id,
                'lote_id' => $lote->id,
                'cantidad' => 20,
                'notas' => 'Prueba Livewire',
            ],
        ])
        ->call('submitEntrada')
        ->assertHasNoErrors()
        ->assertNotified('Entrada registrada');
});

it('registra entrada a lavanderia especificando origen de habitacion o espacio', function (): void {
    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaTest();

    $catTipo = obtenerTipoCatalogo(CatalogoTipoEnum::CATEGORIA_PRODUCTO->value, 'Categorías de Producto');
    $categoria = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_GEN'],
        ['nombre' => 'General', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prod = Producto::query()->create([
        'codigo' => 'PROD-GYM-'.uniqid(),
        'nombre' => 'Toalla Gym',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $variante = ProductoVariante::query()->create([
        'producto_id' => $prod->id,
        'codigo' => 'VAR-GYM-'.uniqid(),
        'nombre_variante' => 'Azul',
        'sku' => 'SKU-GYM-'.uniqid(),
    ]);

    $espacio = Espacio::query()->create([
        'codigo' => 'ESP-GYM-'.uniqid(),
        'nombre' => 'Gimnasio',
        'tipo' => 'gym',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $interactor = app(RegistrarEntradaDirectaLavanderia::class);

    $resultado = $interactor->ejecutarLote(
        items: [
            ['producto_variante_id' => $variante->id, 'cantidad' => 12.0, 'notas' => 'Toallas usadas de gym'],
        ],
        ubicacionLavanderiaId: $lavanderia->id,
        creadoPorId: null,
        notasGenerales: 'Recogida de gimnasio',
        tipoOrigen: 'espacio',
        origenId: $espacio->id,
    );

    expect($resultado['total_items'])->toBe(1);
    expect($resultado['total_piezas'])->toBe(12.0);

    $movimiento = MovimientoStock::query()
        ->where('tipo', 'ENTRADA_LAVANDERIA')
        ->where('producto_id', $prod->id)
        ->latest('id')
        ->first();

    expect($movimiento)->not->toBeNull();
    expect($movimiento->referencia)->toContain("Espacio #{$espacio->id}");
});

it('no muestra productos ni variantes que no tengan lote en inventario', function (): void {
    $catTipo = obtenerTipoCatalogo(CatalogoTipoEnum::CATEGORIA_PRODUCTO->value, 'Categorías de Producto');

    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $catTipo->id,
        'codigo' => 'CAT_SIN_LOTE_'.uniqid(),
        'nombre' => 'Lencería Sin Lote',
        'estado' => EstadoGeneral::Activo,
    ]);

    $unidad = obtenerUnidadMedidaTest();

    $prodSinLote = Producto::query()->create([
        'codigo' => 'PROD-SIN-LOTE-'.uniqid(),
        'nombre' => 'Sábana Sin Lote',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $varSinLote = ProductoVariante::query()->create([
        'producto_id' => $prodSinLote->id,
        'codigo' => 'VAR-SIN-LOTE-'.uniqid(),
        'nombre_variante' => 'Sin Lote',
        'sku' => 'SKU-NL-'.uniqid(),
    ]);

    $query = app(ObtenerOpcionesBlancosLavanderia::class);
    $opciones = $query->execute($categoria->id);

    expect($opciones)->not->toHaveKey($varSinLote->id);
    expect($opciones)->toBeEmpty();
});

it('descuenta stock de SharedStock cuando la entrada proviene de una habitacion o espacio', function (): void {
    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaTest();

    $catTipo = obtenerTipoCatalogo(CatalogoTipoEnum::CATEGORIA_PRODUCTO->value, 'Categorías de Producto');
    $categoria = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_HAB'],
        ['nombre' => 'Lencería Habitación', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prod = Producto::query()->create([
        'codigo' => 'PROD-HAB-'.uniqid(),
        'nombre' => 'Sábana King Size',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $variante = ProductoVariante::query()->create([
        'producto_id' => $prod->id,
        'codigo' => 'VAR-HAB-'.uniqid(),
        'nombre_variante' => 'Blanca',
        'sku' => 'SKU-HAB-'.uniqid(),
    ]);

    $lote = Lote::query()->create([
        'codigo_lote' => 'LOTE-HAB-001',
        'producto_id' => $prod->id,
        'producto_variante_id' => $variante->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $habitacion = Habitacion::factory()->create([
        'codigo' => 'HAB-105',
        'numero' => '105',
        'nombre' => 'Habitación 105',
        'estado' => EstadoEspacio::Disponible,
    ]);

    $stockHab = SharedStock::query()->create([
        'stockable_type' => Habitacion::class,
        'stockable_id' => $habitacion->id,
        'producto_variante_id' => $variante->id,
        'lote_id' => $lote->id,
        'cantidad_actual' => 8.0,
        'cantidad_ideal' => 8.0,
    ]);

    $interactor = app(RegistrarEntradaDirectaLavanderia::class);
    $interactor->execute(
        productoVarianteId: $variante->id,
        cantidad: 3.0,
        ubicacionLavanderiaId: $lavanderia->id,
        creadoPorId: null,
        tipoOrigen: 'habitacion',
        origenId: $habitacion->id,
        loteId: $lote->id,
    );

    $stockHab->refresh();
    expect((float) $stockHab->cantidad_actual)->toBe(5.0);

    $stockLav = Stock::query()
        ->where('ubicacion_id', $lavanderia->id)
        ->where('producto_variante_id', $variante->id)
        ->first();
    expect($stockLav)->not->toBeNull();
    expect((float) $stockLav->cantidad)->toBe(3.0);
});

it('descuenta stock de inv_stock cuando la entrada proviene de un carrito de limpieza', function (): void {
    $lavanderia = Ubicacion::query()->where('tipo', 'lavanderia')->firstOrFail();
    $unidad = obtenerUnidadMedidaTest();

    $catTipo = obtenerTipoCatalogo(CatalogoTipoEnum::CATEGORIA_PRODUCTO->value, 'Categorías de Producto');
    $categoria = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT_CAR'],
        ['nombre' => 'Toallas Carrito', 'estado' => EstadoGeneral::Activo, 'catalogo_tipo_id' => $catTipo->id]
    );

    $prod = Producto::query()->create([
        'codigo' => 'PROD-CAR-'.uniqid(),
        'nombre' => 'Toalla Facial',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $variante = ProductoVariante::query()->create([
        'producto_id' => $prod->id,
        'codigo' => 'VAR-CAR-'.uniqid(),
        'nombre_variante' => 'Azul',
        'sku' => 'SKU-CAR-'.uniqid(),
    ]);

    $lote = Lote::query()->create([
        'codigo_lote' => 'LOTE-CAR-001',
        'producto_id' => $prod->id,
        'producto_variante_id' => $variante->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 30.0,
        'cantidad_inicial' => 30.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $carrito = Ubicacion::query()->create([
        'codigo' => 'CART-01',
        'nombre' => 'Carrito Limpieza 1',
        'tipo' => 'carrito',
        'estado' => EstadoGeneral::Activo,
    ]);

    $stockCar = Stock::query()->create([
        'ubicacion_id' => $carrito->id,
        'producto_id' => $prod->id,
        'producto_variante_id' => $variante->id,
        'lote_id' => $lote->id,
        'cantidad' => 12.0,
    ]);

    $interactor = app(RegistrarEntradaDirectaLavanderia::class);
    $interactor->execute(
        productoVarianteId: $variante->id,
        cantidad: 4.0,
        ubicacionLavanderiaId: $lavanderia->id,
        creadoPorId: null,
        tipoOrigen: 'carrito',
        origenId: $carrito->id,
        loteId: $lote->id,
    );

    $stockCar->refresh();
    expect((float) $stockCar->cantidad)->toBe(8.0);

    $stockLav = Stock::query()
        ->where('ubicacion_id', $lavanderia->id)
        ->where('producto_variante_id', $variante->id)
        ->first();
    expect($stockLav)->not->toBeNull();
    expect((float) $stockLav->cantidad)->toBe(4.0);
});

it('filtra opciones y categorias exclusivamente por el stock presente en el origen', function (): void {
    $unidad = obtenerUnidadMedidaTest();
    $catTipo = obtenerTipoCatalogo(CatalogoTipoEnum::CATEGORIA_PRODUCTO->value, 'Categorías de Producto');

    $cat1 = Catalogo::query()->create([
        'catalogo_tipo_id' => $catTipo->id,
        'codigo' => 'CAT_FLT_1_'.uniqid(),
        'nombre' => 'Categoría Presente',
        'estado' => EstadoGeneral::Activo,
    ]);

    $cat2 = Catalogo::query()->create([
        'catalogo_tipo_id' => $catTipo->id,
        'codigo' => 'CAT_FLT_2_'.uniqid(),
        'nombre' => 'Categoría Ausente',
        'estado' => EstadoGeneral::Activo,
    ]);

    $prod1 = Producto::query()->create([
        'codigo' => 'PROD-F1-'.uniqid(),
        'nombre' => 'Producto en Habitación',
        'categoria_id' => $cat1->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $var1 = ProductoVariante::query()->create([
        'producto_id' => $prod1->id,
        'codigo' => 'VAR-F1-'.uniqid(),
        'nombre_variante' => 'V1',
        'sku' => 'SKU-F1-'.uniqid(),
    ]);

    $lote1 = Lote::query()->create([
        'codigo_lote' => 'LOTE-F1-001',
        'producto_id' => $prod1->id,
        'producto_variante_id' => $var1->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 20.0,
        'cantidad_inicial' => 20.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $prod2 = Producto::query()->create([
        'codigo' => 'PROD-F2-'.uniqid(),
        'nombre' => 'Producto en otra parte',
        'categoria_id' => $cat2->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 2,
        'estado' => EstadoGeneral::Activo,
    ]);

    $var2 = ProductoVariante::query()->create([
        'producto_id' => $prod2->id,
        'codigo' => 'VAR-F2-'.uniqid(),
        'nombre_variante' => 'V2',
        'sku' => 'SKU-F2-'.uniqid(),
    ]);

    Lote::query()->create([
        'codigo_lote' => 'LOTE-F2-001',
        'producto_id' => $prod2->id,
        'producto_variante_id' => $var2->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 10.0,
        'cantidad_inicial' => 10.0,
        'fecha_recepcion' => now()->toDateString(),
    ]);

    $hab = Habitacion::factory()->create([
        'codigo' => 'HAB-201',
        'numero' => '201',
        'nombre' => 'Habitación 201',
        'estado' => EstadoEspacio::Disponible,
    ]);

    SharedStock::query()->create([
        'stockable_type' => Habitacion::class,
        'stockable_id' => $hab->id,
        'producto_variante_id' => $var1->id,
        'lote_id' => $lote1->id,
        'cantidad_actual' => 6.0,
        'cantidad_ideal' => 6.0,
    ]);

    $queryCat = app(ObtenerCategoriasBlancosLavanderia::class);
    $categoriasHab = $queryCat->execute('habitacion', $hab->id);
    expect($categoriasHab)->toHaveKey($cat1->id);
    expect($categoriasHab)->not->toHaveKey($cat2->id);

    $queryBlancos = app(ObtenerOpcionesBlancosLavanderia::class);
    $opcionesHab = $queryBlancos->execute(tipoOrigen: 'habitacion', origenId: $hab->id);
    expect($opcionesHab)->toHaveKey($var1->id);
    expect($opcionesHab)->not->toHaveKey($var2->id);

    $precarga = $queryBlancos->obtenerVariantesParaPrecarga(tipoOrigen: 'habitacion', origenId: $hab->id);
    expect($precarga)->toHaveCount(1);
    expect($precarga[0]['producto_variante_id'])->toBe($var1->id);
    expect($precarga[0]['max_qty'])->toBe(6.0);
});
