<?php

declare(strict_types=1);

use App\Interactors\Restaurante\Cocina\TransformarMateriaPrimaCocina;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Restaurante\TransformacionMateriaPrima;
use App\Repository\Models\Shared\Stock;

test('transforma producto en materia prima y registra merma en inventario', function (): void {
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

    $transformacion = app(TransformarMateriaPrimaCocina::class)->ejecutar([
        'producto_origen_id' => $productoOrigen->id,
        'variante_origen_id' => $varianteOrigen->id,
        'ubicacion_origen_id' => $cocina->id,
        'cantidad_procesada' => 4,
        'items' => [
            [
                'producto_destino_id' => $productoDestino->id,
                'variante_destino_id' => $varianteDestino->id,
                'ubicacion_destino_id' => $cocina->id,
                'cantidad' => 3.5,
                'costo_asignado' => 80,
                'es_merma' => false,
            ],
            [
                'producto_destino_id' => $productoMerma->id,
                'variante_destino_id' => $varianteMerma->id,
                'ubicacion_destino_id' => $cocina->id,
                'cantidad' => 0.5,
                'costo_asignado' => 10,
                'es_merma' => true,
            ],
        ],
    ]);

    $stockDestino = Stock::query()
        ->where('stockable_type', Ubicacion::class)
        ->where('stockable_id', $cocina->id)
        ->where('producto_variante_id', $varianteDestino->id)
        ->firstOrFail();

    expect($transformacion)->toBeInstanceOf(TransformacionMateriaPrima::class)
        ->and((float) $stockOrigen->refresh()->cantidad_actual)->toBe(6.0)
        ->and((float) $stockDestino->cantidad_actual)->toBe(3.5)
        ->and(MovimientoStock::query()->where('documento_tipo', 'transformacion_materia_prima')->where('documento_id', $transformacion->id)->where('tipo', 'TRANSFORMACION_SALIDA')->exists())->toBeTrue()
        ->and(MovimientoStock::query()->where('documento_tipo', 'transformacion_materia_prima')->where('documento_id', $transformacion->id)->where('tipo', 'TRANSFORMACION_ENTRADA')->exists())->toBeTrue()
        ->and(MovimientoStock::query()->where('documento_tipo', 'transformacion_materia_prima')->where('documento_id', $transformacion->id)->where('tipo', 'MERMA_COCINA')->exists())->toBeTrue();
});
