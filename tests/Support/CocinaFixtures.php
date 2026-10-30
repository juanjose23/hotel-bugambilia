<?php

declare(strict_types=1);

use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Stock;

function fixtureCorreccionesCocina(): array
{
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo proceso {$sufijo}",
        'codigo' => "tipo-proceso-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Materia prima {$sufijo}",
        'codigo' => "materia-prima-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Kilogramo {$sufijo}",
        'codigo' => "kg-{$sufijo}",
        'estado' => 1,
    ]);

    $productoOrigen = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Pollo entero {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $varianteOrigen = ProductoVariante::query()->create([
        'producto_id' => $productoOrigen->id,
        'codigo' => "POLLO-{$sufijo}",
        'nombre_variante' => 'entero',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $productoDestino = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Pollo porcionado {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $varianteDestino = ProductoVariante::query()->create([
        'producto_id' => $productoDestino->id,
        'codigo' => "PORCION-{$sufijo}",
        'nombre_variante' => 'porcionado',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $productoMerma = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Merma pollo {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $varianteMerma = ProductoVariante::query()->create([
        'producto_id' => $productoMerma->id,
        'codigo' => "MERMA-{$sufijo}",
        'nombre_variante' => 'huesos',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $cocina = Ubicacion::query()->where('nombre', 'Cocina')->first()
        ?? Ubicacion::query()->create([
            'nombre' => 'Cocina',
            'tipo' => 'cocina',
            'orden' => random_int(10000, 99999),
            'estado' => 1,
        ]);

    $stockOrigen = Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $varianteOrigen->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 10,
    ]);

    return get_defined_vars();
}

function datosTransformacionCocina(array $f, float $cantidad = 1): array
{
    return ['codigo' => 'REG-'.bin2hex(random_bytes(4)), 'producto_origen_id' => $f['productoOrigen']->id, 'variante_origen_id' => $f['varianteOrigen']->id, 'ubicacion_origen_id' => $f['cocina']->id, 'cantidad_procesada' => 1, 'items' => [['producto_destino_id' => $f['productoDestino']->id, 'variante_destino_id' => $f['varianteDestino']->id, 'ubicacion_destino_id' => $f['cocina']->id, 'cantidad' => $cantidad, 'costo_asignado' => 0, 'es_merma' => false]]];
}
function pedidoCorreccionesCocina(array $f): array
{
    $f['cocina']->update(['nombre' => UbicacionCocina::RESTAURANTE->value]);
    $receta = Producto::create(['categoria_id' => $f['categoria']->id, 'nombre' => 'Receta regresión '.bin2hex(random_bytes(4)), 'unidad_medida_id' => $f['unidad']->id, 'tipo' => 1, 'estado' => 1, 'rendimiento_porciones' => 1]);
    ProductoKit::create(['producto_padre_id' => $receta->id, 'producto_variante_id' => $f['varianteOrigen']->id, 'cantidad' => 1]);
    $plato = Plato::create(['codigo' => 'REG-'.bin2hex(random_bytes(4)), 'nombre' => 'Plato regresión', 'producto_receta_id' => $receta->id, 'estado' => 1]);
    Moneda::firstOrCreate(['codigo' => 'NIO'], ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true]);
    $pedido = Pedido::create(['codigo' => 'REG-'.bin2hex(random_bytes(4)), 'estado' => 1, 'abierto_en' => now()]);
    $item = PedidoItem::create(['pedido_id' => $pedido->id, 'plato_id' => $plato->id, 'cantidad' => 1, 'precio_unitario' => 100, 'estado' => 1]);

    return [$pedido, $item];
}
