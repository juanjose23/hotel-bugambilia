<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Shared\Stock;
use Database\Seeders\Restaurante\Stock\RestauranteStockBebidasProductosSeeder;

test('la siembra de bebidas reutiliza el stock de variante creado por espacios sin duplicarlo', function (): void {
    $tipo = CatalogoTipo::query()->create([
        'codigo' => 'stock-bebidas-test', 'nombre' => 'Prueba de stock', 'estado' => 1,
    ]);
    $categoria = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id, 'codigo' => 'CAT_PRO_ABARROTES',
        'nombre' => 'Abarrotes', 'estado' => 1,
    ]);
    $unidad = Catalogo::query()->create([
        'catalogo_tipo_id' => $tipo->id, 'codigo' => 'UNI_UD',
        'nombre' => 'Unidad', 'estado' => 1,
    ]);
    $producto = Producto::query()->create([
        'nombre' => 'Agua de prueba', 'categoria_id' => $categoria->id,
        'unidad_medida_id' => $unidad->id, 'tipo' => 1, 'estado' => 1,
    ]);
    $variante = ProductoVariante::query()->create([
        'producto_id' => $producto->id, 'codigo' => 'AG-GAS-500ML',
        'nombre_variante' => '500 ml', 'unidad_medida_id' => $unidad->id, 'estado' => 1,
    ]);
    $restaurante = Espacio::query()->create([
        'codigo' => 'REST-001', 'nombre' => 'Restaurante de prueba',
        'tipo' => TipoEspacio::RESTAURANTE, 'estado' => 1,
    ]);
    $stock = Stock::query()->create([
        'stockable_type' => Espacio::class, 'stockable_id' => $restaurante->id,
        'producto_variante_id' => $variante->id,
        'cantidad_ideal' => 5, 'cantidad_actual' => 3,
    ]);

    $this->seed(RestauranteStockBebidasProductosSeeder::class);
    $this->seed(RestauranteStockBebidasProductosSeeder::class);

    expect(Stock::query()->where('stockable_type', Espacio::class)
        ->where('stockable_id', $restaurante->id)
        ->where('producto_variante_id', $variante->id)->count())->toBe(1)
        ->and($stock->fresh()?->producto_id)->toBe($producto->id)
        ->and($stock->fresh()?->cantidad_actual)->toBe(95.0);
});
