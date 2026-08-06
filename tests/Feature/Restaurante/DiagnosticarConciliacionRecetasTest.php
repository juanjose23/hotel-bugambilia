<?php

declare(strict_types=1);

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\RecetaTransformacionMateriaPrima;
use App\Repository\Models\Shared\Stock;
use App\Repository\Queries\Restaurante\Cocina\DiagnosticarConciliacionRecetas;

test('diagnostica que una materia prima faltante puede producirse desde bruto disponible', function (): void {
    $sufijo = str()->random(8);
    $tipo = CatalogoTipo::query()->create([
        'nombre' => "Tipo conciliacion {$sufijo}",
        'codigo' => "tipo-conciliacion-{$sufijo}",
        'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Cocina {$sufijo}",
        'codigo' => "cocina-{$sufijo}",
        'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id,
        'nombre' => "Unidad {$sufijo}",
        'codigo' => "unidad-conciliacion-{$sufijo}",
        'estado' => 1,
    ]);

    $receta = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Receta {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $materia = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Pollo porcionado {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $varianteMateria = ProductoVariante::query()->create([
        'producto_id' => $materia->id,
        'codigo' => "MAT-{$sufijo}",
        'nombre_variante' => 'porcionado',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);
    $bruto = Producto::query()->create([
        'categoria_id' => $categoria->id,
        'nombre' => "Pollo entero {$sufijo}",
        'unidad_medida_id' => $unidad->id,
        'tipo' => 1,
        'estado' => 1,
    ]);
    $varianteBruta = ProductoVariante::query()->create([
        'producto_id' => $bruto->id,
        'codigo' => "BRU-{$sufijo}",
        'nombre_variante' => 'entero',
        'unidad_medida_id' => $unidad->id,
        'estado' => 1,
    ]);

    ProductoKit::query()->create([
        'producto_padre_id' => $receta->id,
        'producto_variante_id' => $varianteMateria->id,
        'cantidad' => 2,
    ]);
    Plato::query()->create([
        'codigo' => "PLATO-CONC-{$sufijo}",
        'nombre' => "Plato conciliacion {$sufijo}",
        'producto_receta_id' => $receta->id,
        'estado' => 1,
    ]);
    RecetaTransformacionMateriaPrima::query()->create([
        'producto_materia_prima_id' => $materia->id,
        'variante_materia_prima_id' => $varianteMateria->id,
        'producto_bruto_id' => $bruto->id,
        'variante_bruta_id' => $varianteBruta->id,
        'cantidad_bruta' => 3,
        'cantidad_resultado' => 2,
        'merma_estimada' => 1,
        'unidad_medida_id' => $unidad->id,
        'estado' => true,
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
        'producto_variante_id' => $varianteMateria->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 0,
    ]);
    Stock::query()->create([
        'stockable_type' => Ubicacion::class,
        'stockable_id' => $cocina->id,
        'producto_variante_id' => $varianteBruta->id,
        'cantidad_ideal' => 10,
        'cantidad_actual' => 3,
    ]);

    $diagnostico = app(DiagnosticarConciliacionRecetas::class)->ejecutar();
    $item = collect($diagnostico['items'])
        ->first(fn (array $item): bool => ($item['ingrediente'] ?? '') === "Pollo porcionado {$sufijo} - porcionado");

    expect($item)->not->toBeNull()
        ->and($item['estado'])->toBe('puede_transformarse')
        ->and($item['bruto'])->toBe("Pollo entero {$sufijo} - entero")
        ->and($item['bruto_necesario'])->toBe(3.0);
});
