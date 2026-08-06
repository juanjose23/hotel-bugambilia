<?php

declare(strict_types=1);

use App\Enums\Restaurante\EstadoItemPedido;
use App\Interactors\Restaurante\Cocina\AutorizarSustitucionIngrediente;
use App\Interactors\Restaurante\Cocina\ConsumirIngredientesPedido;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Interactors\Restaurante\Pedidos\ResolverFaltanteStockPedido;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Stock;
use App\Repository\Queries\Restaurante\Cocina\AnalizarFaltantesPedidoCocina;

test('el error de stock insuficiente muestra producto y variante', function (): void {
    $tipo = CatalogoTipo::query()->create([
        'nombre' => 'Tipo producto test',
        'codigo' => 'tipo-producto-test',
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => 'Ingredientes test',
        'codigo' => 'ingredientes-test',
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => 'Unidad test',
        'codigo' => 'unidad-test',
        'estado' => 1,
    ]);

    $receta = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => 'Receta arroz test',
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $producto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => 'Arroz',
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id,
        'codigo' => 'ARR-250G-TEST',
        'nombre_variante' => '250g',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    ProductoKit::query()->create([
        'producto_padre_id' => $receta->id,
        'producto_variante_id' => $variante->id,
        'cantidad' => 2,
    ]);

    $plato = Plato::query()->create([
        'codigo' => 'PLATO-STOCK-TEST',
        'nombre' => 'Arroz test',
        'producto_receta_id' => $receta->id,
        'estado' => 1,
    ]);
    $pedido = Pedido::query()->create([
        'codigo' => 'PED-STOCK-TEST',
        'estado' => 1,
        'subtotal' => 0,
        'abierto_en' => now(),
    ]);
    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => $plato->id,
        'cantidad' => 1,
        'precio_unitario' => 0,
        'subtotal' => 0,
        'estado' => 1,
    ]);
    $cocina = Ubicacion::query()->create([
        'nombre' => 'Cocina',
        'tipo' => 'cocina',
        'orden' => 9876,
        'estado' => 1,
    ]);
    Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $variante->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 0,
    ]);

    app(ConsumirIngredientesPedido::class)->ejecutar($item);
})->throws(DomainException::class, 'Stock insuficiente para Arroz - 250g');

test('consume variante sustituta autorizada cuando falta el ingrediente original', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo producto {$sufijo}",
        'codigo' => "tipo-producto-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Ingredientes {$sufijo}",
        'codigo' => "ingredientes-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Unidad {$sufijo}",
        'codigo' => "unidad-{$sufijo}",
        'estado' => 1,
    ]);

    $receta = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Receta {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $productoOriginal = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Arroz {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $productoSustituto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Pasta {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $varianteOriginal = ProductoVariante::query()->create([
        'producto_id' => $productoOriginal->id,
        'codigo' => "ARR-{$sufijo}",
        'nombre_variante' => '250g',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $varianteSustituta = ProductoVariante::query()->create([
        'producto_id' => $productoSustituto->id,
        'codigo' => "PAS-{$sufijo}",
        'nombre_variante' => '500g',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    ProductoKit::query()->create([
        'producto_padre_id' => $receta->id,
        'producto_variante_id' => $varianteOriginal->id,
        'cantidad' => 2,
    ]);

    $plato = Plato::query()->create([
        'codigo' => "PLATO-SUST-{$sufijo}",
        'nombre' => "Plato sustitucion {$sufijo}",
        'producto_receta_id' => $receta->id,
        'estado' => 1,
    ]);
    $pedido = Pedido::query()->create([
        'codigo' => "PED-SUST-{$sufijo}",
        'estado' => 1,
        'subtotal' => 0,
        'abierto_en' => now(),
    ]);
    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => $plato->id,
        'cantidad' => 1,
        'precio_unitario' => 0,
        'subtotal' => 0,
        'estado' => 1,
    ]);
    $cocina = Ubicacion::query()->where('nombre', 'Cocina')->first()
        ?? Ubicacion::query()->create([
            'nombre' => 'Cocina',
            'tipo' => 'cocina',
            'orden' => random_int(10000, 99999),
            'estado' => 1,
        ]);
    $stockOriginal = Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $varianteOriginal->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 0,
    ]);
    $stockSustituto = Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $varianteSustituta->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 5,
    ]);

    app(AutorizarSustitucionIngrediente::class)->ejecutar(
        item: $item,
        varianteOriginalId: (int) $varianteOriginal->id,
        varianteSustitutaId: (int) $varianteSustituta->id,
        cantidadRequerida: 2,
        cantidadUsada: 2,
        motivo: 'Sustitución temporal por falta de stock',
    );

    app(ConsumirIngredientesPedido::class)->ejecutar($item);

    expect((float) $stockOriginal->refresh()->cantidad_actual)->toBe(0.0)
        ->and((float) $stockSustituto->refresh()->cantidad_actual)->toBe(3.0);
});

test('analiza faltantes antes de iniciar preparacion', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo faltantes {$sufijo}",
        'codigo' => "tipo-faltantes-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Ingredientes faltantes {$sufijo}",
        'codigo' => "ingredientes-faltantes-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Unidad faltantes {$sufijo}",
        'codigo' => "unidad-faltantes-{$sufijo}",
        'estado' => 1,
    ]);

    $receta = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Receta faltante {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $producto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Tomate {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id,
        'codigo' => "TOM-{$sufijo}",
        'nombre_variante' => 'kg',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    ProductoKit::query()->create([
        'producto_padre_id' => $receta->id,
        'producto_variante_id' => $variante->id,
        'cantidad' => 3,
    ]);

    $plato = Plato::query()->create([
        'codigo' => "PLATO-FALTANTE-{$sufijo}",
        'nombre' => "Sopa {$sufijo}",
        'producto_receta_id' => $receta->id,
        'estado' => 1,
    ]);
    $pedido = Pedido::query()->create([
        'codigo' => "PED-FALTANTE-{$sufijo}",
        'estado' => 1,
        'subtotal' => 0,
        'abierto_en' => now(),
    ]);
    PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => $plato->id,
        'cantidad' => 2,
        'precio_unitario' => 0,
        'subtotal' => 0,
        'estado' => 1,
    ]);
    $cocina = Ubicacion::query()->where('nombre', 'Cocina')->first()
        ?? Ubicacion::query()->create([
            'nombre' => 'Cocina',
            'tipo' => 'cocina',
            'orden' => random_int(10000, 99999),
            'estado' => 1,
        ]);
    Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $variante->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 1,
    ]);

    $faltantes = app(AnalizarFaltantesPedidoCocina::class)->ejecutar($pedido);

    expect($faltantes)->toHaveCount(1)
        ->and($faltantes[0]['ingrediente'])->toBe("Tomate {$sufijo} - kg")
        ->and($faltantes[0]['requerido'])->toBe(6.0)
        ->and($faltantes[0]['disponible'])->toBe(1.0)
        ->and($faltantes[0]['faltante'])->toBe(5.0);
});

test('consume ingredientes de receta segun rendimiento por porciones', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo porciones {$sufijo}",
        'codigo' => "tipo-porciones-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Ingredientes porciones {$sufijo}",
        'codigo' => "ingredientes-porciones-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Unidad porciones {$sufijo}",
        'codigo' => "unidad-porciones-{$sufijo}",
        'estado' => 1,
    ]);

    $receta = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Receta porcionada {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
        'rendimiento_porciones' => 4,
    ]);
    $producto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Salsa {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id,
        'codigo' => "SAL-{$sufijo}",
        'nombre_variante' => 'litro',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    ProductoKit::query()->create([
        'producto_padre_id' => $receta->id,
        'producto_variante_id' => $variante->id,
        'cantidad' => 8,
    ]);

    $plato = Plato::query()->create([
        'codigo' => "PLATO-PORCION-{$sufijo}",
        'nombre' => "Plato porcionado {$sufijo}",
        'producto_receta_id' => $receta->id,
        'estado' => 1,
    ]);
    $pedido = Pedido::query()->create([
        'codigo' => "PED-PORCION-{$sufijo}",
        'estado' => 1,
        'subtotal' => 0,
        'abierto_en' => now(),
    ]);
    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => $plato->id,
        'cantidad' => 2,
        'precio_unitario' => 0,
        'subtotal' => 0,
        'estado' => 1,
    ]);
    $cocina = Ubicacion::query()->where('nombre', 'Cocina')->first()
        ?? Ubicacion::query()->create([
            'nombre' => 'Cocina',
            'tipo' => 'cocina',
            'orden' => random_int(10000, 99999),
            'estado' => 1,
        ]);
    $stock = Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $variante->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 10,
    ]);

    app(ConsumirIngredientesPedido::class)->ejecutar($item);

    expect((float) $stock->refresh()->cantidad_actual)->toBe(6.0);
});

test('bloquea pedido por falta de stock y permite resolver con sustitucion autorizada', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo bloqueo {$sufijo}",
        'codigo' => "tipo-bloqueo-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Ingredientes bloqueo {$sufijo}",
        'codigo' => "ingredientes-bloqueo-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Unidad bloqueo {$sufijo}",
        'codigo' => "unidad-bloqueo-{$sufijo}",
        'estado' => 1,
    ]);
    $receta = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Receta bloqueo {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $productoOriginal = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Queso {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $productoSustituto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Crema {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $varianteOriginal = ProductoVariante::query()->create([
        'producto_id' => $productoOriginal->id,
        'codigo' => "QUE-{$sufijo}",
        'nombre_variante' => '250g',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $varianteSustituta = ProductoVariante::query()->create([
        'producto_id' => $productoSustituto->id,
        'codigo' => "CRE-{$sufijo}",
        'nombre_variante' => '250g',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    ProductoKit::query()->create([
        'producto_padre_id' => $receta->id,
        'producto_variante_id' => $varianteOriginal->id,
        'cantidad' => 2,
    ]);
    $plato = Plato::query()->create([
        'codigo' => "PLATO-BLOQUEO-{$sufijo}",
        'nombre' => "Plato bloqueo {$sufijo}",
        'producto_receta_id' => $receta->id,
        'estado' => 1,
    ]);
    $pedido = Pedido::query()->create([
        'codigo' => "PED-BLOQUEO-{$sufijo}",
        'estado' => 1,
        'subtotal' => 0,
        'abierto_en' => now(),
    ]);
    $item = PedidoItem::query()->create([
        'pedido_id' => $pedido->id,
        'plato_id' => $plato->id,
        'cantidad' => 1,
        'precio_unitario' => 0,
        'subtotal' => 0,
        'estado' => 1,
    ]);
    $cocina = Ubicacion::query()->where('nombre', 'Cocina')->first()
        ?? Ubicacion::query()->create([
            'nombre' => 'Cocina',
            'tipo' => 'cocina',
            'orden' => random_int(10000, 99999),
            'estado' => 1,
        ]);
    Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $varianteOriginal->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 0,
    ]);
    Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $varianteSustituta->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 5,
    ]);

    expect(fn () => app(EnviarPedidoACocina::class)->ejecutar($pedido))
        ->toThrow(DomainException::class, 'Pedido bloqueado por stock insuficiente');

    $item->refresh();
    expect($item->estado)->toBe(EstadoItemPedido::BLOQUEADO_STOCK)
        ->and($item->bloqueo_stock_detalle)->not->toBeNull();

    app(ResolverFaltanteStockPedido::class)->ejecutar(
        pedido: $pedido->refresh(),
        accion: 'sustituir_ingrediente',
        itemId: (int) $item->id,
        sustituciones: [[
            'variante_original_id' => $varianteOriginal->id,
            'variante_sustituta_id' => $varianteSustituta->id,
            'cantidad_usada' => 2,
        ]],
    );

    expect($item->refresh()->estado)->toBe(EstadoItemPedido::PENDIENTE)
        ->and($item->bloqueo_stock_detalle)->toBeNull();
});
