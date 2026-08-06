<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\Stock as SharedStock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

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
        $ubicacionesCocina = $this->ubicacionesCocina();

        // Ingredientes como ProductoVariante con Stock Inicial en Cocina
        $fileteRes = $this->variante('Filete de res', '300g', $defaultCatId, $ubicacionesCocina);
        $pechuga = $this->variante('Pechuga de pollo', '200g', $defaultCatId, $ubicacionesCocina);
        $lomoCerdo = $this->variante('Medallon lomo cerdo', '250g', $defaultCatId, $ubicacionesCocina);
        $camarones = $this->variante('Camarones', '200g', $defaultCatId, $ubicacionesCocina);
        $pescado = $this->variante('Filete de pescado', '250g', $defaultCatId, $ubicacionesCocina);
        $arroz = $this->variante('Arroz blanco porcion', '1 porcion', $defaultCatId, $ubicacionesCocina);
        $frijoles = $this->variante('Frijoles molidos porcion', '1 porcion', $defaultCatId, $ubicacionesCocina);
        $papa = $this->variante('Papas fritas porcion', '1 porcion', $defaultCatId, $ubicacionesCocina);
        $maduro = $this->variante('Tajadas de maduro', '1 porcion', $defaultCatId, $ubicacionesCocina);
        $ensalada = $this->variante('Ensalada fresca porcion', '1 porcion', $defaultCatId, $ubicacionesCocina);
        $mantequilla = $this->variante('Mantequilla', '15g', $defaultCatId, $ubicacionesCocina);
        $aceite = $this->variante('Aceite vegetal', '30ml', $defaultCatId, $ubicacionesCocina);
        $salPimienta = $this->variante('Sal y pimienta mix', '5g', $defaultCatId, $ubicacionesCocina);
        $tomate = $this->variante('Tomate', '150g', $defaultCatId, $ubicacionesCocina);
        $cebolla = $this->variante('Cebolla', '100g', $defaultCatId, $ubicacionesCocina);
        $chile = $this->variante('Chile', '50g', $defaultCatId, $ubicacionesCocina);
        $tortilla = $this->variante('Tortilla de maiz', '2 unid', $defaultCatId, $ubicacionesCocina);
        $queso = $this->variante('Queso', '50g', $defaultCatId, $ubicacionesCocina);
        $crema = $this->variante('Crema', '30ml', $defaultCatId, $ubicacionesCocina);
        $aguacate = $this->variante('Aguacate', '1/2 unid', $defaultCatId, $ubicacionesCocina);
        $leche = $this->variante('Leche', '200ml', $defaultCatId, $ubicacionesCocina);
        $huevo = $this->variante('Huevo', '2 unid', $defaultCatId, $ubicacionesCocina);

        // Ingredientes Italianos y Porciones Procesadas
        $fettuccine = $this->variante('Fettuccine pasta', '200g porcion', $defaultCatId, $ubicacionesCocina);
        $parmesano = $this->variante('Queso Parmesano Rallado', '50g porcion', $defaultCatId, $ubicacionesCocina);
        $mozzarella = $this->variante('Queso Mozzarella Rallado', '100g porcion', $defaultCatId, $ubicacionesCocina);
        $pomodoro = $this->variante('Salsa Pomodoro Casera', '150ml porcion', $defaultCatId, $ubicacionesCocina);
        $masaPizza = $this->variante('Masa para Pizza Artesanal', '1 disco 30cm', $defaultCatId, $ubicacionesCocina);
        $carneMolida = $this->variante('Carne Molida Especial', '200g porcion', $defaultCatId, $ubicacionesCocina);
        $pepperoni = $this->variante('Pepperoni en Rodajas', '60g porcion', $defaultCatId, $ubicacionesCocina);
        $albahaca = $this->variante('Albahaca Fresca', '15g porcion', $defaultCatId, $ubicacionesCocina);
        $arrozArborio = $this->variante('Arroz Arborio', '150g porcion', $defaultCatId, $ubicacionesCocina);
        $hongosPorcini = $this->variante('Hongos Porcini', '50g porcion', $defaultCatId, $ubicacionesCocina);

        $platos = [
            [
                'nombre' => 'Fettuccine Alfredo con Pollo', 'categoria' => $catPlatos, 'precio_nio' => 380,
                'descripcion' => 'Pasta Fettuccine artesanal en cremosa salsa Alfredo con mantequilla, parmesano y tiras de pechuga de pollo.',
                'rendimiento_porciones' => 1,
                'receta' => [[$fettuccine, 1], [$pechuga, 1], [$parmesano, 1], [$mantequilla, 1], [$crema, 1], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Lasagna Bolognese Tradicional', 'categoria' => $catPlatos, 'precio_nio' => 410,
                'descripcion' => 'Capas de pasta artesanal con ragú bolognese de carne molida especial, salsa pomodoro y queso mozzarella gratinado.',
                'rendimiento_porciones' => 1,
                'receta' => [[$carneMolida, 1], [$pomodoro, 1], [$mozzarella, 1], [$parmesano, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Pizza Margherita Gourmet', 'categoria' => $catPlatos, 'precio_nio' => 360,
                'descripcion' => 'Pizza italiana artesanal con salsa pomodoro casera, queso mozzarella de búfala y albahaca fresca.',
                'rendimiento_porciones' => 1,
                'receta' => [[$masaPizza, 1], [$pomodoro, 1], [$mozzarella, 1], [$albahaca, 1]],
            ],
            [
                'nombre' => 'Pizza Pepperoni Tradicional', 'categoria' => $catPlatos, 'precio_nio' => 390,
                'descripcion' => 'Pizza crocante al horno con abundante queso mozzarella gratinado y rodajas de pepperoni curado.',
                'rendimiento_porciones' => 1,
                'receta' => [[$masaPizza, 1], [$pomodoro, 1], [$mozzarella, 1], [$pepperoni, 1]],
            ],
            [
                'nombre' => 'Risotto de Hongos Porcini', 'categoria' => $catPlatos, 'precio_nio' => 440,
                'descripcion' => 'Cremoso risotto preparado con arroz arborio, hongos porcini, mantequilla y abundante queso parmesano.',
                'rendimiento_porciones' => 1,
                'receta' => [[$arrozArborio, 1], [$hongosPorcini, 1], [$parmesano, 1], [$mantequilla, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Lomo de cerdo a la plancha', 'categoria' => $catPlatos, 'precio_nio' => 350,
                'descripcion' => 'Medallon de lomo de cerdo a la plancha con salsa criolla, arroz, tajadas y ensalada fresca.',
                'rendimiento_porciones' => 1,
                'receta' => [[$lomoCerdo, 1], [$arroz, 1], [$maduro, 1], [$ensalada, 1], [$tomate, 1], [$cebolla, 0.5], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Filete de res termino medio', 'categoria' => $catPlatos, 'precio_nio' => 420,
                'descripcion' => 'Filete de res jugoso con papas fritas, vegetales salteados y mantequilla de hierbas.',
                'rendimiento_porciones' => 1,
                'receta' => [[$fileteRes, 1], [$papa, 1], [$mantequilla, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Pollo a la parrilla', 'categoria' => $catPlatos, 'precio_nio' => 280,
                'descripcion' => 'Pechuga de pollo marinada a la parrilla con arroz, frijoles molidos y ensalada.',
                'rendimiento_porciones' => 1,
                'receta' => [[$pechuga, 1], [$arroz, 1], [$frijoles, 1], [$ensalada, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Camarones al ajillo', 'categoria' => $catPlatos, 'precio_nio' => 380,
                'descripcion' => 'Camarones salteados al ajillo con arroz blanco, vegetales y tajadas.',
                'rendimiento_porciones' => 1,
                'receta' => [[$camarones, 1], [$arroz, 1], [$maduro, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Pescado frito Esteli', 'categoria' => $catPlatos, 'precio_nio' => 320,
                'descripcion' => 'Filete de pescado fresco empanizado, con arroz, ensalada y tortillas.',
                'rendimiento_porciones' => 1,
                'receta' => [[$pescado, 1], [$arroz, 1], [$ensalada, 1], [$tortilla, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Nachos supreme', 'categoria' => $catEntradas, 'precio_nio' => 220,
                'descripcion' => 'Tortilla chips con queso gratinado, crema, frijoles y guacamole.',
                'rendimiento_porciones' => 2,
                'receta' => [[$tortilla, 2], [$queso, 1], [$crema, 1], [$frijoles, 1], [$tomate, 0.5], [$aguacate, 1]],
            ],
            [
                'nombre' => 'Ceviche de camaron', 'categoria' => $catEntradas, 'precio_nio' => 260,
                'descripcion' => 'Camarones frescos marinados en limon con cebolla morada, tomate y cilantro.',
                'rendimiento_porciones' => 2,
                'receta' => [[$camarones, 1], [$cebolla, 0.5], [$tomate, 0.5], [$chile, 0.5]],
            ],
            [
                'nombre' => 'Tres leches', 'categoria' => $catPostres, 'precio_nio' => 150,
                'descripcion' => 'Pastel tradicional nicaraguense de tres leches, suave y esponjoso.',
                'rendimiento_porciones' => 6,
                'receta' => [[$leche, 1], [$huevo, 1], [$crema, 0.5]],
            ],
        ];

        $unidadMedidaId = $this->unidadMedidaId();
        $usaRendimientoPorciones = Schema::hasColumn('productos', 'rendimiento_porciones');

        foreach ($platos as $p) {
            $datosProductoReceta = ['categoria_id' => $defaultCatId, 'unidad_medida_id' => $unidadMedidaId, 'estado' => 1];
            if ($usaRendimientoPorciones) {
                $datosProductoReceta['rendimiento_porciones'] = $p['rendimiento_porciones'];
            }

            $productoPadre = Producto::firstOrCreate(
                ['nombre' => 'Receta: '.$p['nombre']],
                $datosProductoReceta
            );
            $datosActualizarReceta = ['estado' => 1];
            if ($usaRendimientoPorciones) {
                $datosActualizarReceta['rendimiento_porciones'] = $p['rendimiento_porciones'];
            }

            $productoPadre->forceFill($datosActualizarReceta)->save();

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

    private function unidadMedidaId(): int
    {
        $id = Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'UNIDAD_MEDIDA'))
            ->where('codigo', 'UNI_UD')
            ->value('id');

        if (is_numeric($id)) {
            return (int) $id;
        }

        $fallback = Catalogo::query()->value('id');

        return is_numeric($fallback) ? (int) $fallback : 1;
    }

    /** @param Collection<int, Ubicacion> $ubicacionesCocina */
    private function variante(string $nombreProducto, string $nombreVariante, int $categoriaId, mixed $ubicacionesCocina): ProductoVariante
    {
        $producto = Producto::firstOrCreate(
            ['nombre' => $nombreProducto],
            ['categoria_id' => $categoriaId, 'unidad_medida_id' => $this->unidadMedidaId(), 'estado' => 1]
        );

        $variante = ProductoVariante::firstOrCreate(
            ['producto_id' => $producto->id, 'nombre_variante' => $nombreVariante],
            ['codigo' => 'VAR-'.strtoupper(substr(md5($nombreProducto.$nombreVariante), 0, 6))]
        );

        // Costos basados en precio de mercado por unidad base (kg, L, unidad)
        // y la cantidad real indicada en la variante (ej: 300g, 30ml, 5g, 2 unid)
        $preciosMercado = [
            'Filete de res' => ['precio' => 400, 'base' => 'kg', 'cantidad_variante' => 0.3],
            'Pechuga de pollo' => ['precio' => 200, 'base' => 'kg', 'cantidad_variante' => 0.2],
            'Medallon lomo cerdo' => ['precio' => 280, 'base' => 'kg', 'cantidad_variante' => 0.25],
            'Camarones' => ['precio' => 400, 'base' => 'kg', 'cantidad_variante' => 0.2],
            'Filete de pescado' => ['precio' => 260, 'base' => 'kg', 'cantidad_variante' => 0.25],
            'Arroz blanco porcion' => ['precio' => 40, 'base' => 'kg', 'cantidad_variante' => 0.25],
            'Frijoles molidos porcion' => ['precio' => 35, 'base' => 'kg', 'cantidad_variante' => 0.2],
            'Papas fritas porcion' => ['precio' => 30, 'base' => 'kg', 'cantidad_variante' => 0.2],
            'Tajadas de maduro' => ['precio' => 25, 'base' => 'kg', 'cantidad_variante' => 0.15],
            'Ensalada fresca porcion' => ['precio' => 40, 'base' => 'kg', 'cantidad_variante' => 0.2],
            'Mantequilla' => ['precio' => 200, 'base' => 'kg', 'cantidad_variante' => 0.015],
            'Aceite vegetal' => ['precio' => 100, 'base' => 'L', 'cantidad_variante' => 0.03],
            'Sal y pimienta mix' => ['precio' => 60, 'base' => 'kg', 'cantidad_variante' => 0.005],
            'Tomate' => ['precio' => 50, 'base' => 'kg', 'cantidad_variante' => 0.15],
            'Cebolla' => ['precio' => 40, 'base' => 'kg', 'cantidad_variante' => 0.1],
            'Chile' => ['precio' => 80, 'base' => 'kg', 'cantidad_variante' => 0.05],
            'Tortilla de maiz' => ['precio' => 3, 'base' => 'unidad', 'cantidad_variante' => 2],
            'Queso' => ['precio' => 240, 'base' => 'kg', 'cantidad_variante' => 0.05],
            'Crema' => ['precio' => 160, 'base' => 'L', 'cantidad_variante' => 0.03],
            'Aguacate' => ['precio' => 20, 'base' => 'unidad', 'cantidad_variante' => 0.5],
            'Leche' => ['precio' => 45, 'base' => 'L', 'cantidad_variante' => 0.2],
            'Huevo' => ['precio' => 3, 'base' => 'unidad', 'cantidad_variante' => 2],
        ];

        $data = $preciosMercado[$nombreProducto] ?? ['precio' => 15, 'base' => 'kg', 'cantidad_variante' => 1];
        $costoUnitario = round($data['precio'] * $data['cantidad_variante'], 2);

        foreach ($ubicacionesCocina as $cocina) {
            // Stock moderno (inv_lotes + inv_stock) — usado por costoRealUnitario y ObtenerStockParaConsumo
            $lote = Lote::firstOrCreate(
                [
                    'producto_id' => $producto->id,
                    'codigo_lote' => 'LOTE-INICIAL-'.strtoupper(substr(md5($nombreProducto.$cocina->id), 0, 8)),
                ],
                [
                    'producto_variante_id' => $variante->id,
                    'ubicacion_id' => $cocina->id,
                    'cantidad_inicial' => 500.0,
                    'cantidad_disponible' => 500.0,
                    'costo_unitario' => $costoUnitario,
                    'costo_total' => $costoUnitario * 500.0,
                    'estado' => EstadoLote::Disponible,
                    'fecha_recepcion' => now()->toDateString(),
                ]
            );

            // Stock legacy (tabla `stocks`) — usado por ConsumirIngredientesPedido y MateriaPrimaCocina
            SharedStock::updateOrCreate(
                [
                    'stockable_type' => Ubicacion::class,
                    'stockable_id' => $cocina->id,
                    'producto_variante_id' => $variante->id,
                ],
                [
                    'lote_id' => $lote->id,
                    'cantidad_actual' => 500.0,
                    'cantidad_ideal' => 500.0,
                ]
            );

            Stock::updateOrCreate(
                [
                    'producto_id' => $producto->id,
                    'lote_id' => $lote->id,
                    'ubicacion_id' => $cocina->id,
                ],
                [
                    'producto_variante_id' => $variante->id,
                    'cantidad' => 500.0,
                ]
            );
        }

        return $variante;
    }

    /** @return Collection<int, Ubicacion> */
    private function ubicacionesCocina()
    {
        return Ubicacion::query()
            ->where('nombre', 'Cocina Restaurante')
            ->orWhere('nombre', 'Cocina')
            ->orWhere('nombre', 'like', '%Cocina%')
            ->get();
    }
}
