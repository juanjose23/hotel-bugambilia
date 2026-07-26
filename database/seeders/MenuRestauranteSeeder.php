<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Seeder;

class MenuRestauranteSeeder extends Seeder
{
    public function run(): void
    {
        $nio = Moneda::where('codigo', 'NIO')->first();

        $catEntradas = Catalogo::firstOrCreate(['codigo' => 'REST_ENTRADAS', 'catalogo_tipo_id' => $this->tipoId('CATEGORIA_SERVICIO')], ['nombre' => 'Entradas', 'estado' => 1]);
        $catPlatos = Catalogo::firstOrCreate(['codigo' => 'REST_PLATOS', 'catalogo_tipo_id' => $this->tipoId('CATEGORIA_SERVICIO')], ['nombre' => 'Platos Fuertes', 'estado' => 1]);
        $catPostres = Catalogo::firstOrCreate(['codigo' => 'REST_POSTRES', 'catalogo_tipo_id' => $this->tipoId('CATEGORIA_SERVICIO')], ['nombre' => 'Postres', 'estado' => 1]);
        $catBebidas = Catalogo::firstOrCreate(['codigo' => 'REST_BEBIDAS', 'catalogo_tipo_id' => $this->tipoId('CATEGORIA_SERVICIO')], ['nombre' => 'Bebidas', 'estado' => 1]);

        $defaultCatId = (int) Catalogo::first()?->id ?: 2;

        // Ubicación Cocina Restaurante para Stock de Insumos
        $cocina = Ubicacion::firstOrCreate(
            ['nombre' => 'Cocina Restaurante'],
            ['tipo' => 'interna', 'estado' => 1]
        );

        // Ingredientes como ProductoVariante con Stock Inicial en Cocina
        $fileteRes = $this->variante('Filete de res', '300g', $defaultCatId, $cocina);
        $pechuga = $this->variante('Pechuga de pollo', '200g', $defaultCatId, $cocina);
        $lomoCerdo = $this->variante('Medallon lomo cerdo', '250g', $defaultCatId, $cocina);
        $camarones = $this->variante('Camarones', '200g', $defaultCatId, $cocina);
        $pescado = $this->variante('Filete de pescado', '250g', $defaultCatId, $cocina);
        $arroz = $this->variante('Arroz blanco porcion', '1 porcion', $defaultCatId, $cocina);
        $frijoles = $this->variante('Frijoles molidos porcion', '1 porcion', $defaultCatId, $cocina);
        $papa = $this->variante('Papas fritas porcion', '1 porcion', $defaultCatId, $cocina);
        $maduro = $this->variante('Tajadas de maduro', '1 porcion', $defaultCatId, $cocina);
        $ensalada = $this->variante('Ensalada fresca porcion', '1 porcion', $defaultCatId, $cocina);
        $mantequilla = $this->variante('Mantequilla', '15g', $defaultCatId, $cocina);
        $aceite = $this->variante('Aceite vegetal', '30ml', $defaultCatId, $cocina);
        $salPimienta = $this->variante('Sal y pimienta mix', '5g', $defaultCatId, $cocina);
        $tomate = $this->variante('Tomate', '150g', $defaultCatId, $cocina);
        $cebolla = $this->variante('Cebolla', '100g', $defaultCatId, $cocina);
        $chile = $this->variante('Chile', '50g', $defaultCatId, $cocina);
        $tortilla = $this->variante('Tortilla de maiz', '2 unid', $defaultCatId, $cocina);
        $queso = $this->variante('Queso', '50g', $defaultCatId, $cocina);
        $crema = $this->variante('Crema', '30ml', $defaultCatId, $cocina);
        $aguacate = $this->variante('Aguacate', '1/2 unid', $defaultCatId, $cocina);
        $leche = $this->variante('Leche', '200ml', $defaultCatId, $cocina);
        $huevo = $this->variante('Huevo', '2 unid', $defaultCatId, $cocina);

        $platos = [
            [
                'nombre' => 'Lomo de cerdo a la plancha', 'categoria' => $catPlatos, 'precio_nio' => 350,
                'descripcion' => 'Medallon de lomo de cerdo a la plancha con salsa criolla, arroz, tajadas y ensalada fresca.',
                'receta' => [[$lomoCerdo, 1], [$arroz, 1], [$maduro, 1], [$ensalada, 1], [$tomate, 1], [$cebolla, 0.5], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Filete de res termino medio', 'categoria' => $catPlatos, 'precio_nio' => 420,
                'descripcion' => 'Filete de res jugoso con papas fritas, vegetales salteados y mantequilla de hierbas.',
                'receta' => [[$fileteRes, 1], [$papa, 1], [$mantequilla, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Pollo a la parrilla', 'categoria' => $catPlatos, 'precio_nio' => 280,
                'descripcion' => 'Pechuga de pollo marinada a la parrilla con arroz, frijoles molidos y ensalada.',
                'receta' => [[$pechuga, 1], [$arroz, 1], [$frijoles, 1], [$ensalada, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Camarones al ajillo', 'categoria' => $catPlatos, 'precio_nio' => 380,
                'descripcion' => 'Camarones salteados al ajillo con arroz blanco, vegetales y tajadas.',
                'receta' => [[$camarones, 1], [$arroz, 1], [$maduro, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Pescado frito Esteli', 'categoria' => $catPlatos, 'precio_nio' => 320,
                'descripcion' => 'Filete de pescado fresco empanizado, con arroz, ensalada y tortillas.',
                'receta' => [[$pescado, 1], [$arroz, 1], [$ensalada, 1], [$tortilla, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Nachos supreme', 'categoria' => $catEntradas, 'precio_nio' => 220,
                'descripcion' => 'Tortilla chips con queso gratinado, crema, frijoles y guacamole.',
                'receta' => [[$tortilla, 2], [$queso, 1], [$crema, 1], [$frijoles, 1], [$tomate, 0.5], [$aguacate, 1]],
            ],
            [
                'nombre' => 'Ceviche de camaron', 'categoria' => $catEntradas, 'precio_nio' => 260,
                'descripcion' => 'Camarones frescos marinados en limon con cebolla morada, tomate y cilantro.',
                'receta' => [[$camarones, 1], [$cebolla, 0.5], [$tomate, 0.5], [$chile, 0.5]],
            ],
            [
                'nombre' => 'Tres leches', 'categoria' => $catPostres, 'precio_nio' => 150,
                'descripcion' => 'Pastel tradicional nicaraguense de tres leches, suave y esponjoso.',
                'receta' => [[$leche, 1], [$huevo, 1], [$crema, 0.5]],
            ],
        ];

        foreach ($platos as $p) {
            $productoPadre = Producto::firstOrCreate(
                ['nombre' => 'Receta: '.$p['nombre']],
                ['categoria_id' => $defaultCatId, 'unidad_medida_id' => 1, 'estado' => 1]
            );

            foreach ($p['receta'] as [$variante, $cantidad]) {
                ProductoKit::firstOrCreate(
                    ['producto_padre_id' => $productoPadre->id, 'producto_variante_id' => $variante->id],
                    ['cantidad' => $cantidad]
                );
            }

            $plato = Plato::firstOrCreate(
                ['nombre' => $p['nombre']],
                [
                    'codigo' => 'PLT-'.strtoupper(substr(md5($p['nombre']), 0, 6)),
                    'categoria_id' => $p['categoria']->id,
                    'producto_receta_id' => $productoPadre->id,
                    'descripcion' => $p['descripcion'],
                    'estado' => 1,
                    'web' => true,
                ]
            );

            if ($nio) {
                Precio::firstOrCreate(
                    ['priceable_type' => Plato::class, 'priceable_id' => $plato->id, 'moneda_id' => $nio->id],
                    ['precio' => $p['precio_nio'], 'fecha_inicio' => now()->toDateString(), 'estado' => 1]
                );
            }
        }

        $this->command->info('Menu restaurante: Platos, recetas, precios y stock inicial en Cocina Restaurante creados.');
    }

    private function tipoId(string $codigo): int
    {
        return (int) (Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', $codigo))->first()?->catalogo_tipo_id ?: 8);
    }

    private function variante(string $nombreProducto, string $nombreVariante, int $categoriaId, Ubicacion $cocina): ProductoVariante
    {
        $producto = Producto::firstOrCreate(
            ['nombre' => $nombreProducto],
            ['categoria_id' => $categoriaId, 'unidad_medida_id' => 1, 'estado' => 1]
        );

        $variante = ProductoVariante::firstOrCreate(
            ['producto_id' => $producto->id, 'nombre_variante' => $nombreVariante],
            ['codigo' => 'VAR-'.strtoupper(substr(md5($nombreProducto.$nombreVariante), 0, 6))]
        );

        // Generar Stock inicial para la cocina restaurante
        Stock::firstOrCreate(
            [
                'stockable_type' => Ubicacion::class,
                'stockable_id' => $cocina->id,
                'producto_variante_id' => $variante->id,
            ],
            [
                'cantidad_actual' => 500.0,
                'cantidad_ideal' => 500.0,
            ]
        );

        return $variante;
    }
}
