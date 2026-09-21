<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante\Menu;

use App\Enums\Inventario\EstadoLote;
use App\Enums\Restaurante\AreaCocina;
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

final class MenuRestauranteSeeder extends Seeder
{
    /**
     * Reutiliza los productos reales del catálogo (ProductoSeeder) como
     * ingredientes del menú siempre que existan en la base; la variante se
     * busca por código y, si el producto/variante no existe, se crea como
     * insumo propio del restaurante (fallback).
     *
     * @var array<string, array{producto: string, variante: string}>
     */
    private const REUSO_CATALOGO = [
        'Filete de res' => ['producto' => 'Carne de res seleccionada', 'variante' => 'RES-1KG'],
        'Pechuga de pollo' => ['producto' => 'Pollo entero', 'variante' => 'POLLO-1KG'],
        'Camarones' => ['producto' => 'Langostinos congelados', 'variante' => 'LANG-1KG'],
        'Filete de pescado' => ['producto' => 'Pescado fresco del día', 'variante' => 'PESC-1KG'],
        'Arroz blanco porcion' => ['producto' => 'Arroz blanco', 'variante' => 'ARR-1KG'],
        'Frijoles molidos porcion' => ['producto' => 'Frijoles rojos', 'variante' => 'FRI-1KG'],
        'Papas fritas porcion' => ['producto' => 'Papas de temporada', 'variante' => 'PAPAS-1KG'],
        'Tajadas de maduro' => ['producto' => 'Plátanos verdes y maduros', 'variante' => 'PLAT-1KG'],
        'Ensalada fresca porcion' => ['producto' => 'Hortalizas surtidas', 'variante' => 'HOT-CAJA-1KG'],
        'Mantequilla' => ['producto' => 'Mantequilla de cocina', 'variante' => 'MANT-COC-1KG'],
        'Aceite vegetal' => ['producto' => 'Aceite de cocina', 'variante' => 'ACE-1L'],
        'Sal y pimienta mix' => ['producto' => 'Sal de mesa', 'variante' => 'SAL-1KG'],
        'Cebolla' => ['producto' => 'Cebollas de cocina', 'variante' => 'CEBOLLA-1KG'],
        'Tortilla de maiz' => ['producto' => 'Tortillas de maíz hecho a mano', 'variante' => 'TMAZ-50-UD'],
        'Queso' => ['producto' => 'Queso fresco', 'variante' => 'QF-500G'],
        'Crema' => ['producto' => 'Crema de leche', 'variante' => 'CREM-LEC-1L'],
        'Leche' => ['producto' => 'Leche fresca', 'variante' => 'LEC-1L'],
        'Huevo' => ['producto' => 'Huevos de granja', 'variante' => 'HUE-CUB-12'],
    ];

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

        // Insumos y Materias Primas para Coctelería y Barra (Bebidas Preparadas)
        $ronBlanco = $this->variante('Ron Blanco Flor de Cana', '1.5oz porcion', $defaultCatId, $ubicacionesCocina);
        $tequila = $this->variante('Tequila Reposado', '1.5oz porcion', $defaultCatId, $ubicacionesCocina);
        $tripleSec = $this->variante('Licor Triple Sec', '1oz porcion', $defaultCatId, $ubicacionesCocina);
        $cremaCoco = $this->variante('Crema de Coco', '2oz porcion', $defaultCatId, $ubicacionesCocina);
        $jugoPina = $this->variante('Jugo de Pina Natural', '4oz porcion', $defaultCatId, $ubicacionesCocina);
        $limonCriollo = $this->variante('Limon Criollo', '2 unid', $defaultCatId, $ubicacionesCocina);
        $hierbaBuena = $this->variante('Hierbabuena / Menta Fresca', '10g porcion', $defaultCatId, $ubicacionesCocina);
        $jarabeGoma = $this->variante('Jarabe de Azucar Simple', '1oz porcion', $defaultCatId, $ubicacionesCocina);
        $aguaMineralSoda = $this->variante('Agua Mineral Gasificada Soda', '150ml porcion', $defaultCatId, $ubicacionesCocina);
        $vinoTintoCasa = $this->variante('Vino Tinto de la Casa', '150ml porcion', $defaultCatId, $ubicacionesCocina);
        $cafeGrano = $this->variante('Cafe Grano Tostado Especial', '18g espresso', $defaultCatId, $ubicacionesCocina);
        $canelaPolvo = $this->variante('Canela en Polvo', '2g porcion', $defaultCatId, $ubicacionesCocina);

        $platos = [
            [
                'nombre' => 'Fettuccine Alfredo con Pollo', 'categoria' => $catPlatos, 'precio_nio' => 380,
                'area_cocina' => AreaCocina::COCINA,
                'descripcion' => 'Pasta Fettuccine artesanal en cremosa salsa Alfredo con mantequilla, parmesano y tiras de pechuga de pollo.',
                'rendimiento_porciones' => 1,
                'receta' => [[$fettuccine, 1], [$pechuga, 1], [$parmesano, 1], [$mantequilla, 1], [$crema, 1], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Lasagna Bolognese Tradicional', 'categoria' => $catPlatos, 'precio_nio' => 410,
                'area_cocina' => AreaCocina::COCINA,
                'descripcion' => 'Capas de pasta artesanal con ragú bolognese de carne molida especial, salsa pomodoro y queso mozzarella gratinado.',
                'rendimiento_porciones' => 1,
                'receta' => [[$carneMolida, 1], [$pomodoro, 1], [$mozzarella, 1], [$parmesano, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Pizza Margherita Gourmet', 'categoria' => $catPlatos, 'precio_nio' => 360,
                'area_cocina' => AreaCocina::COCINA,
                'descripcion' => 'Pizza italiana artesanal con salsa pomodoro casera, queso mozzarella de búfala y albahaca fresca.',
                'rendimiento_porciones' => 1,
                'receta' => [[$masaPizza, 1], [$pomodoro, 1], [$mozzarella, 1], [$albahaca, 1]],
            ],
            [
                'nombre' => 'Pizza Pepperoni Tradicional', 'categoria' => $catPlatos, 'precio_nio' => 390,
                'area_cocina' => AreaCocina::COCINA,
                'descripcion' => 'Pizza crocante al horno con abundante queso mozzarella gratinado y rodajas de pepperoni curado.',
                'rendimiento_porciones' => 1,
                'receta' => [[$masaPizza, 1], [$pomodoro, 1], [$mozzarella, 1], [$pepperoni, 1]],
            ],
            [
                'nombre' => 'Risotto de Hongos Porcini', 'categoria' => $catPlatos, 'precio_nio' => 440,
                'area_cocina' => AreaCocina::COCINA,
                'descripcion' => 'Cremoso risotto preparado con arroz arborio, hongos porcini, mantequilla y abundante queso parmesano.',
                'rendimiento_porciones' => 1,
                'receta' => [[$arrozArborio, 1], [$hongosPorcini, 1], [$parmesano, 1], [$mantequilla, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Lomo de cerdo a la plancha', 'categoria' => $catPlatos, 'precio_nio' => 350,
                'area_cocina' => AreaCocina::PARRILLA,
                'descripcion' => 'Medallon de lomo de cerdo a la plancha con salsa criolla, arroz, tajadas y ensalada fresca.',
                'rendimiento_porciones' => 1,
                'receta' => [[$lomoCerdo, 1], [$arroz, 1], [$maduro, 1], [$ensalada, 1], [$tomate, 1], [$cebolla, 0.5], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Filete de res termino medio', 'categoria' => $catPlatos, 'precio_nio' => 420,
                'area_cocina' => AreaCocina::PARRILLA,
                'descripcion' => 'Filete de res jugoso con papas fritas, vegetales salteados y mantequilla de hierbas.',
                'rendimiento_porciones' => 1,
                'receta' => [[$fileteRes, 1], [$papa, 1], [$mantequilla, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Pollo a la parrilla', 'categoria' => $catPlatos, 'precio_nio' => 280,
                'area_cocina' => AreaCocina::PARRILLA,
                'descripcion' => 'Pechuga de pollo marinada a la parrilla con arroz, frijoles molidos y ensalada.',
                'rendimiento_porciones' => 1,
                'receta' => [[$pechuga, 1], [$arroz, 1], [$frijoles, 1], [$ensalada, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Camarones al ajillo', 'categoria' => $catPlatos, 'precio_nio' => 380,
                'area_cocina' => AreaCocina::COCINA,
                'descripcion' => 'Camarones salteados al ajillo con arroz blanco, vegetales y tajadas.',
                'rendimiento_porciones' => 1,
                'receta' => [[$camarones, 1], [$arroz, 1], [$maduro, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Pescado frito Esteli', 'categoria' => $catPlatos, 'precio_nio' => 320,
                'area_cocina' => AreaCocina::COCINA,
                'descripcion' => 'Filete de pescado fresco empanizado, con arroz, ensalada y tortillas.',
                'rendimiento_porciones' => 1,
                'receta' => [[$pescado, 1], [$arroz, 1], [$ensalada, 1], [$tortilla, 1], [$aceite, 0.3], [$salPimienta, 1]],
            ],
            [
                'nombre' => 'Nachos supreme', 'categoria' => $catEntradas, 'precio_nio' => 220,
                'area_cocina' => AreaCocina::COCINA,
                'descripcion' => 'Tortilla chips con queso gratinado, crema, frijoles y guacamole.',
                'rendimiento_porciones' => 2,
                'receta' => [[$tortilla, 2], [$queso, 1], [$crema, 1], [$frijoles, 1], [$tomate, 0.5], [$aguacate, 1]],
            ],
            [
                'nombre' => 'Ceviche de camaron', 'categoria' => $catEntradas, 'precio_nio' => 260,
                'area_cocina' => AreaCocina::COCINA,
                'descripcion' => 'Camarones frescos marinados en limon con cebolla morada, tomate y cilantro.',
                'rendimiento_porciones' => 2,
                'receta' => [[$camarones, 1], [$cebolla, 0.5], [$tomate, 0.5], [$chile, 0.5]],
            ],
            [
                'nombre' => 'Tres leches', 'categoria' => $catPostres, 'precio_nio' => 150,
                'area_cocina' => AreaCocina::POSTRES,
                'descripcion' => 'Pastel tradicional nicaraguense de tres leches, suave y esponjoso.',
                'rendimiento_porciones' => 6,
                'receta' => [[$leche, 1], [$huevo, 1], [$crema, 0.5]],
            ],
            [
                'nombre' => 'Mojito Clásico Cubano',
                'categoria' => $catBebidas,
                'area_cocina' => AreaCocina::BAR,
                'precio_nio' => 180,
                'descripcion' => 'Refrescante coctel preparado con ron blanco, hojas de hierbabuena fresca maceradas, jugo de limón criollo, almíbar y soda gasificada.',
                'rendimiento_porciones' => 1,
                'receta' => [[$ronBlanco, 1], [$hierbaBuena, 1], [$limonCriollo, 1], [$jarabeGoma, 1], [$aguaMineralSoda, 1]],
            ],
            [
                'nombre' => 'Margarita Bugambilia',
                'categoria' => $catBebidas,
                'area_cocina' => AreaCocina::BAR,
                'precio_nio' => 190,
                'descripcion' => 'Clásico coctel mexicano con tequila reposado, licor triple sec, jugo fresco de limón criollo y borde escarchado de sal marina.',
                'rendimiento_porciones' => 1,
                'receta' => [[$tequila, 1], [$tripleSec, 1], [$limonCriollo, 1], [$salPimienta, 0.2]],
            ],
            [
                'nombre' => 'Piña Colada Tropical',
                'categoria' => $catBebidas,
                'area_cocina' => AreaCocina::BAR,
                'precio_nio' => 175,
                'descripcion' => 'Suave y cremosa combinación caribeña de ron blanco, crema de coco batida y jugo de piña fresca natural.',
                'rendimiento_porciones' => 1,
                'receta' => [[$ronBlanco, 1], [$cremaCoco, 1], [$jugoPina, 1]],
            ],
            [
                'nombre' => 'Sangría Bugambilia (Copa)',
                'categoria' => $catBebidas,
                'area_cocina' => AreaCocina::BAR,
                'precio_nio' => 160,
                'descripcion' => 'Copa de vino tinto macerado con frutas cítricas de temporada, toque de licor triple sec y almíbar.',
                'rendimiento_porciones' => 1,
                'receta' => [[$vinoTintoCasa, 1], [$tripleSec, 0.5], [$jarabeGoma, 1]],
            ],
            [
                'nombre' => 'Capuchino Especial de la Casa',
                'categoria' => $catBebidas,
                'area_cocina' => AreaCocina::BAR,
                'precio_nio' => 95,
                'descripcion' => 'Espresso doble de café artesanal segoviano con leche fresca vaporizada y una sutil capa de canela en polvo.',
                'rendimiento_porciones' => 1,
                'receta' => [[$cafeGrano, 1], [$leche, 1], [$canelaPolvo, 1]],
            ],
            [
                'nombre' => 'Limonada con Menta Frappé',
                'categoria' => $catBebidas,
                'area_cocina' => AreaCocina::BAR,
                'precio_nio' => 85,
                'descripcion' => 'Bebida natural refrescante a base de limón criollo recién exprimido con menta fresca granizada al hielo.',
                'rendimiento_porciones' => 1,
                'receta' => [[$limonCriollo, 1], [$hierbaBuena, 1], [$jarabeGoma, 1]],
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
                    'area_cocina' => $p['area_cocina'],
                    'descripcion' => $p['descripcion'],
                    'estado' => 1,
                    'web' => true,
                ]
            );

            $plato->area_cocina = $p['area_cocina'];
            $plato->categoria_id = $p['categoria']->id;
            $plato->save();

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
        $producto = null;
        $variante = null;

        $reuso = self::REUSO_CATALOGO[$nombreProducto] ?? null;
        if (is_array($reuso)) {
            $producto = Producto::query()->where('nombre', $reuso['producto'])->first();

            if ($producto instanceof Producto) {
                $variante = ProductoVariante::query()
                    ->where('producto_id', $producto->id)
                    ->where('codigo', $reuso['variante'])
                    ->first();
            }
        }

        if (! $producto instanceof Producto) {
            $producto = Producto::firstOrCreate(
                ['nombre' => $nombreProducto],
                ['categoria_id' => $categoriaId, 'unidad_medida_id' => $this->unidadMedidaId(), 'estado' => 1]
            );
        }

        if (! $variante instanceof ProductoVariante) {
            $variante = ProductoVariante::query()->where('producto_id', $producto->id)->first();
        }

        if (! $variante instanceof ProductoVariante) {
            $variante = ProductoVariante::firstOrCreate(
                ['producto_id' => $producto->id, 'nombre_variante' => $nombreVariante],
                ['codigo' => 'VAR-'.strtoupper(substr(md5($nombreProducto.$nombreVariante), 0, 6))]
            );
        }

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
            'Ron Blanco Flor de Cana' => ['precio' => 320, 'base' => 'L', 'cantidad_variante' => 0.045],
            'Tequila Reposado' => ['precio' => 450, 'base' => 'L', 'cantidad_variante' => 0.045],
            'Licor Triple Sec' => ['precio' => 280, 'base' => 'L', 'cantidad_variante' => 0.03],
            'Crema de Coco' => ['precio' => 120, 'base' => 'L', 'cantidad_variante' => 0.06],
            'Jugo de Pina Natural' => ['precio' => 50, 'base' => 'L', 'cantidad_variante' => 0.12],
            'Limon Criollo' => ['precio' => 2, 'base' => 'unidad', 'cantidad_variante' => 2],
            'Hierbabuena / Menta Fresca' => ['precio' => 60, 'base' => 'kg', 'cantidad_variante' => 0.01],
            'Jarabe de Azucar Simple' => ['precio' => 30, 'base' => 'L', 'cantidad_variante' => 0.03],
            'Agua Mineral Gasificada Soda' => ['precio' => 25, 'base' => 'L', 'cantidad_variante' => 0.15],
            'Vino Tinto de la Casa' => ['precio' => 250, 'base' => 'L', 'cantidad_variante' => 0.15],
            'Cafe Grano Tostado Especial' => ['precio' => 300, 'base' => 'kg', 'cantidad_variante' => 0.018],
            'Canela en Polvo' => ['precio' => 200, 'base' => 'kg', 'cantidad_variante' => 0.002],
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
            ->orWhere('nombre', 'Bar Principal')
            ->orWhere('nombre', 'Barra de Servicio')
            ->orWhere('nombre', 'like', '%Bar%')
            ->get();
    }
}
