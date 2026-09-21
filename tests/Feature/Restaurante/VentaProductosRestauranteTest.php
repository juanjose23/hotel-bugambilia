<?php

declare(strict_types=1);

namespace Tests\Feature\Restaurante;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Restaurante\Pedidos\AbrirPedidoMesa;
use App\Interactors\Restaurante\Pedidos\DescontarStockProductoPedido;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Stock;
use App\Repository\Queries\Restaurante\Stock\ObtenerStockDisponibleRestauranteQuery;

beforeEach(function (): void {
    if (Moneda::query()->where('codigo', 'NIO')->doesntExist()) {
        Moneda::query()->create([
            'codigo' => 'NIO',
            'nombre' => 'Córdoba Nicaragüense',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]);
    }
});

test('puede registrar stock polimórfico stockable para producto simple sin variante y con variante', function (): void {
    $tipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => 'tipo-prod-test'],
        ['nombre' => 'Tipo test', 'estado' => 1]
    );

    $categoria = Catalogo::query()->firstOrCreate(
        ['nombre' => 'Snacks y Bebidas', 'catalogo_tipo_id' => $tipo->id],
        ['codigo' => 'CAT-SNK', 'estado' => 1]
    );

    $unidad = Catalogo::query()->firstOrCreate(
        ['nombre' => 'Unidad', 'catalogo_tipo_id' => $tipo->id],
        ['codigo' => 'UND', 'estado' => 1]
    );

    $productoSimple = Producto::query()->create([
        'codigo' => 'PRD-SNK-01',
        'nombre' => 'Papas Pringles Original',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => EstadoGeneral::Activo,
    ]);

    $restaurante = Espacio::query()->create([
        'nombre' => 'Restaurante Central',
        'codigo' => 'ESP-REST-01',
        'tipo' => TipoEspacio::RESTAURANTE,
        'capacidad_personas' => 50,
        'estado' => 1,
    ]);

    // Stock polimórfico stockable sin variante (producto simple)
    $stockSimple = Stock::query()->create([
        'stockable_type' => Espacio::class,
        'stockable_id' => $restaurante->id,
        'producto_id' => $productoSimple->id,
        'producto_variante_id' => null,
        'cantidad_ideal' => 20,
        'cantidad_actual' => 15,
    ]);

    expect($stockSimple->esProductoSimple())->toBeTrue()
        ->and($stockSimple->tieneVariante())->toBeFalse()
        ->and($stockSimple->producto_id)->toBe($productoSimple->id)
        ->and($stockSimple->cantidad_actual)->toBe(15.0);

    // Producto con variante
    $productoConVariante = Producto::query()->create([
        'codigo' => 'PRD-BEB-01',
        'nombre' => 'Cerveza Nacional',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => EstadoGeneral::Activo,
    ]);

    $varianteLata = ProductoVariante::query()->create([
        'producto_id' => $productoConVariante->id,
        'codigo' => 'VAR-TO-LAT',
        'nombre_variante' => 'Lata 12oz',
        'estado' => 1,
    ]);

    $stockVariante = Stock::query()->create([
        'stockable_type' => Espacio::class,
        'stockable_id' => $restaurante->id,
        'producto_id' => $productoConVariante->id,
        'producto_variante_id' => $varianteLata->id,
        'cantidad_ideal' => 48,
        'cantidad_actual' => 24,
    ]);

    expect($stockVariante->esProductoSimple())->toBeFalse()
        ->and($stockVariante->tieneVariante())->toBeTrue()
        ->and($stockVariante->producto_variante_id)->toBe($varianteLata->id)
        ->and($stockVariante->cantidad_actual)->toBe(24.0);
});

test('query ObtenerStockDisponibleRestauranteQuery devuelve stock correcto para productos simples y variantes', function (): void {
    $tipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => 'tipo-prod-test'],
        ['nombre' => 'Tipo test', 'estado' => 1]
    );

    $categoria = Catalogo::query()->firstOrCreate(
        ['nombre' => 'Snacks', 'catalogo_tipo_id' => $tipo->id],
        ['codigo' => 'CAT-SNK-2', 'estado' => 1]
    );

    $unidad = Catalogo::query()->firstOrCreate(
        ['nombre' => 'Unidad', 'catalogo_tipo_id' => $tipo->id],
        ['codigo' => 'UND-2', 'estado' => 1]
    );

    $producto = Producto::query()->create([
        'codigo' => 'PRD-AGUA-01',
        'nombre' => 'Agua Mineral 500ml',
        'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => EstadoGeneral::Activo,
    ]);

    $bodegaBar = Ubicacion::query()->create([
        'nombre' => 'Bodega Barra Restaurante',
        'codigo' => 'BOD-BAR-01',
        'tipo' => 'almacen',
        'estado' => 1,
    ]);

    Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $bodegaBar->id,
        'producto_id' => $producto->id,
        'producto_variante_id' => null,
        'cantidad_ideal' => 50,
        'cantidad_actual' => 30,
    ]);

    $query = app(ObtenerStockDisponibleRestauranteQuery::class);
    $resultado = $query->ejecutar(productoId: $producto->id, varianteId: null);

    expect($resultado['tiene_stock'])->toBeTrue()
        ->and($resultado['disponible'])->toBe(30.0);
});

test('abrir pedido en mesa con plato y producto simple registra correctamente y descuenta stock', function (): void {
    $tipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => 'tipo-prod-test'],
        ['nombre' => 'Tipo test', 'estado' => 1]
    );

    $categoriaPlato = Catalogo::query()->firstOrCreate(
        ['nombre' => 'Platos Fuertes', 'catalogo_tipo_id' => $tipo->id],
        ['codigo' => 'CAT-PLAT', 'estado' => 1]
    );

    $plato = Plato::query()->create([
        'codigo' => 'PLT-FISH-01',
        'nombre' => 'Filete de Pescado Jalapeño',
        'categoria_id' => $categoriaPlato->id,
        'estado' => 1,
        'tiempo_preparacion' => 15,
    ]);

    $categoriaPrd = Catalogo::query()->firstOrCreate(
        ['nombre' => 'Bebidas', 'catalogo_tipo_id' => $tipo->id],
        ['codigo' => 'CAT-BEB-3', 'estado' => 1]
    );

    $unidad = Catalogo::query()->firstOrCreate(
        ['nombre' => 'Unidad', 'catalogo_tipo_id' => $tipo->id],
        ['codigo' => 'UND-3', 'estado' => 1]
    );

    $producto = Producto::query()->create([
        'codigo' => 'PRD-COCA-01',
        'nombre' => 'Coca Cola Lata 355ml',
        'categoria_id' => $categoriaPrd->id,
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => EstadoGeneral::Activo,
    ]);

    $mesa = Espacio::query()->create([
        'nombre' => 'Mesa Terraza 08',
        'codigo' => 'M-TER-08',
        'tipo' => TipoEspacio::MESA,
        'capacidad_personas' => 4,
        'estado' => EstadoEspacio::Disponible,
        'activo' => true,
    ]);

    $prdMobiliario = Producto::factory()->create();

    $activo = Activo::query()->create([
        'codigo_inventario' => 'AF-TER-08',
        'producto_id' => $prdMobiliario->id,
        'nombre_descriptivo' => 'Mesa Madera Roble',
        'estado' => EstadoActivo::Activo,
        'fecha_adquisicion' => now()->toDateString(),
    ]);

    ActivoAsignacion::query()->create([
        'activo_id' => $activo->id,
        'asignable_type' => Espacio::class,
        'asignable_id' => $mesa->id,
        'fecha_inicio' => now()->toDateString(),
        'estado' => EstadoAsignacion::Vigente,
    ]);

    $stock = Stock::query()->create([
        'stockable_type' => Espacio::class,
        'stockable_id' => $mesa->id,
        'producto_id' => $producto->id,
        'producto_variante_id' => null,
        'cantidad_ideal' => 20,
        'cantidad_actual' => 10,
    ]);

    $interactor = app(AbrirPedidoMesa::class);
    $pedido = $interactor->ejecutar(
        mesa: $mesa,
        meseroId: null,
        clienteId: null,
        notas: 'Cliente VIP',
        items: [
            [
                'tipo_item' => 'plato',
                'plato_id' => $plato->id,
                'cantidad' => 1,
                'precio_unitario' => 250.00,
            ],
            [
                'tipo_item' => 'producto',
                'producto_id' => $producto->id,
                'producto_variante_id' => null,
                'cantidad' => 2,
                'precio_unitario' => 45.00,
            ],
        ]
    );

    expect($pedido->exists)->toBeTrue()
        ->and($pedido->items()->count())->toBe(2);

    $itemPlato = $pedido->items()->where('tipo_item', 'plato')->first();
    $itemProducto = $pedido->items()->where('tipo_item', 'producto')->first();

    expect($itemPlato)->not->toBeNull()
        ->and($itemPlato->esPlato())->toBeTrue()
        ->and($itemPlato->plato_id)->toBe($plato->id)
        ->and($itemProducto)->not->toBeNull()
        ->and($itemProducto->esProducto())->toBeTrue()
        ->and($itemProducto->producto_id)->toBe($producto->id)
        ->and($itemProducto->producto_variante_id)->toBeNull()
        ->and($itemProducto->cantidad)->toEqual(2.0);

    // Descontar stock del producto
    app(DescontarStockProductoPedido::class)->ejecutar($itemProducto);

    $stock->refresh();
    expect($stock->cantidad_actual)->toEqual(8.0); // 10 - 2 = 8
});
