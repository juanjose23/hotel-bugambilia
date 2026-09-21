<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante\Stock;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Inventario\EstadoLote;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\Stock as InvStock;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Seeder;

/**
 * Seeder de existencias (Stock) en Restaurante y Bar para Bebidas, Licores, Cafetería y Botanas.
 *
 * Registra stock polimórfico en el espacio insignia del Restaurante y el Bar,
 * soportando tanto productos con variantes como productos simples sin variantes.
 */
final class RestauranteStockBebidasProductosSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     codigo_variante: string,
     *     ideal: float,
     *     actual: float,
     *     costo: float,
     *     precio_nio: float,
     *     precio_usd: float
     * }>
     */
    private array $bebidasConVariantes = [
        // Aguas y Refrescos
        ['codigo_variante' => 'AG-GAS-500ML', 'ideal' => 120.0, 'actual' => 95.0, 'costo' => 18.0, 'precio_nio' => 40.0, 'precio_usd' => 1.10],
        ['codigo_variante' => 'AG-GAS-1L',     'ideal' => 60.0,  'actual' => 48.0, 'costo' => 28.0, 'precio_nio' => 65.0, 'precio_usd' => 1.80],
        ['codigo_variante' => 'COLA-355ML',    'ideal' => 200.0, 'actual' => 165.0, 'costo' => 20.0, 'precio_nio' => 45.0, 'precio_usd' => 1.25],
        ['codigo_variante' => 'COLA-500ML',    'ideal' => 120.0, 'actual' => 90.0, 'costo' => 25.0, 'precio_nio' => 55.0, 'precio_usd' => 1.50],
        ['codigo_variante' => 'COLA-1L',       'ideal' => 60.0,  'actual' => 45.0, 'costo' => 38.0, 'precio_nio' => 80.0, 'precio_usd' => 2.20],
        ['codigo_variante' => 'NAR-355ML',     'ideal' => 90.0,  'actual' => 70.0, 'costo' => 20.0, 'precio_nio' => 45.0, 'precio_usd' => 1.25],
        ['codigo_variante' => 'NAR-500ML',     'ideal' => 60.0,  'actual' => 45.0, 'costo' => 25.0, 'precio_nio' => 55.0, 'precio_usd' => 1.50],
        ['codigo_variante' => 'LIM-355ML',     'ideal' => 90.0,  'actual' => 75.0, 'costo' => 20.0, 'precio_nio' => 45.0, 'precio_usd' => 1.25],
        ['codigo_variante' => 'COCO-330ML',    'ideal' => 60.0,  'actual' => 45.0, 'costo' => 30.0, 'precio_nio' => 60.0, 'precio_usd' => 1.65],

        // Jugos Naturales Concentrados
        ['codigo_variante' => 'JUG-NAR-1L',    'ideal' => 40.0,  'actual' => 35.0, 'costo' => 45.0, 'precio_nio' => 90.0, 'precio_usd' => 2.50],
        ['codigo_variante' => 'JUG-PIN-1L',    'ideal' => 40.0,  'actual' => 30.0, 'costo' => 45.0, 'precio_nio' => 90.0, 'precio_usd' => 2.50],
        ['codigo_variante' => 'JUG-GUA-1L',    'ideal' => 30.0,  'actual' => 24.0, 'costo' => 45.0, 'precio_nio' => 90.0, 'precio_usd' => 2.50],

        // Cervezas y Licores de Bar
        ['codigo_variante' => 'CER-NAC-355',   'ideal' => 240.0, 'actual' => 190.0, 'costo' => 35.0, 'precio_nio' => 70.0, 'precio_usd' => 1.90],
        ['codigo_variante' => 'CER-NAC-940',   'ideal' => 80.0,  'actual' => 65.0, 'costo' => 75.0, 'precio_nio' => 150.0, 'precio_usd' => 4.10],
        ['codigo_variante' => 'VTINTO-750ML',  'ideal' => 36.0,  'actual' => 28.0, 'costo' => 350.0, 'precio_nio' => 750.0, 'precio_usd' => 20.50],
        ['codigo_variante' => 'VTINTO-1L',     'ideal' => 24.0,  'actual' => 18.0, 'costo' => 300.0, 'precio_nio' => 600.0, 'precio_usd' => 16.40],
        ['codigo_variante' => 'VBLAN-750ML',   'ideal' => 30.0,  'actual' => 22.0, 'costo' => 340.0, 'precio_nio' => 720.0, 'precio_usd' => 19.70],
        ['codigo_variante' => 'RON-BLAN-750',  'ideal' => 24.0,  'actual' => 19.0, 'costo' => 320.0, 'precio_nio' => 650.0, 'precio_usd' => 17.80],
        ['codigo_variante' => 'RON-ANEJO-750', 'ideal' => 30.0,  'actual' => 25.0, 'costo' => 420.0, 'precio_nio' => 850.0, 'precio_usd' => 23.30],
        ['codigo_variante' => 'WHI-STD-750',   'ideal' => 20.0,  'actual' => 15.0, 'costo' => 550.0, 'precio_nio' => 1100.0, 'precio_usd' => 30.00],
        ['codigo_variante' => 'VOD-STD-750',   'ideal' => 20.0,  'actual' => 16.0, 'costo' => 400.0, 'precio_nio' => 800.0, 'precio_usd' => 21.90],

        // Cafetería y Lácteos
        ['codigo_variante' => 'BAR-EXPRESO',   'ideal' => 150.0, 'actual' => 130.0, 'costo' => 15.0, 'precio_nio' => 50.0, 'precio_usd' => 1.40],
        ['codigo_variante' => 'BAR-CAPPUCCINO', 'ideal' => 120.0, 'actual' => 95.0, 'costo' => 28.0, 'precio_nio' => 85.0, 'precio_usd' => 2.30],
        ['codigo_variante' => 'LD-CHOC-200',   'ideal' => 60.0,  'actual' => 48.0, 'costo' => 22.0, 'precio_nio' => 45.0, 'precio_usd' => 1.25],
        ['codigo_variante' => 'LD-VAIN-200',   'ideal' => 60.0,  'actual' => 42.0, 'costo' => 22.0, 'precio_nio' => 45.0, 'precio_usd' => 1.25],
    ];

    /**
     * @var array<int, array{
     *     codigo: string,
     *     nombre: string,
     *     descripcion: string,
     *     ideal: float,
     *     actual: float,
     *     costo: float,
     *     precio_nio: float,
     *     precio_usd: float
     * }>
     */
    private array $snacksProductosSimples = [
        [
            'codigo' => 'SNACK-PAPA-150',
            'nombre' => 'Papas Fritas Gourmet 150g',
            'descripcion' => 'Papas crujientes artesanales con sal marina para picar en mesa o barra.',
            'ideal' => 80.0,
            'actual' => 65.0,
            'costo' => 40.0,
            'precio_nio' => 95.0,
            'precio_usd' => 2.60,
        ],
        [
            'codigo' => 'SNACK-NUEZ-100',
            'nombre' => 'Mix de Nueces y Frutos Secos 100g',
            'descripcion' => 'Selección de almendras, marañones, nueces y pasas tostadas.',
            'ideal' => 60.0,
            'actual' => 48.0,
            'costo' => 55.0,
            'precio_nio' => 120.0,
            'precio_usd' => 3.30,
        ],
        [
            'codigo' => 'SNACK-NACHOS-DIP',
            'nombre' => 'Nachos con Queso Cheddar Dip',
            'descripcion' => 'Porción de totopos crujientes acompañados de dip caliente de queso cheddar.',
            'ideal' => 50.0,
            'actual' => 38.0,
            'costo' => 60.0,
            'precio_nio' => 140.0,
            'precio_usd' => 3.80,
        ],
        [
            'codigo' => 'SNACK-ACEIT-BOT',
            'nombre' => 'Aceitunas Rellenas para Botana',
            'descripcion' => 'Aceitunas verdes rellenas de pimiento en aceite de oliva y especias.',
            'ideal' => 40.0,
            'actual' => 32.0,
            'costo' => 50.0,
            'precio_nio' => 110.0,
            'precio_usd' => 3.00,
        ],
        [
            'codigo' => 'SNACK-CHOC-GOURMET',
            'nombre' => 'Chocolates Artesanales Bugambilias (4 pzs)',
            'descripcion' => 'Bombones de chocolate fino rellenos de frutas tropicales y café local.',
            'ideal' => 50.0,
            'actual' => 40.0,
            'costo' => 35.0,
            'precio_nio' => 80.0,
            'precio_usd' => 2.20,
        ],
    ];

    public function run(): void
    {
        // 1. Resolver el Espacio del Restaurante
        $restaurante = Espacio::query()
            ->where('codigo', 'REST-001')
            ->orWhere('tipo', TipoEspacio::RESTAURANTE)
            ->first();

        if (! $restaurante instanceof Espacio) {
            $this->command->warn('No se encontró el espacio Restaurante Bugambilias. Ejecute EspacioDefinicionSeeder primero.');

            return;
        }

        // 2. Ubicación física asociada para lotes de inventario
        $ubicacionRestaurante = null;
        if (is_numeric($restaurante->ubicacion_id)) {
            $ubicacionRestaurante = Ubicacion::query()->find($restaurante->ubicacion_id);
        }
        if (! $ubicacionRestaurante instanceof Ubicacion) {
            $ubicacionRestaurante = Ubicacion::query()
                ->where('nombre', 'like', '%Restaurante%')
                ->orWhere('nombre', 'like', '%Cocina%')
                ->first() ?? Ubicacion::query()->first();
        }

        // 3. Monedas para precios de venta
        $monedaNio = Moneda::query()->where('codigo', 'NIO')->first();
        $monedaUsd = Moneda::query()->where('codigo', 'USD')->first();

        $contadorBebidas = 0;
        $contadorSnacks = 0;

        // ─── 4. SEMBRAR BEBIDAS (PRODUCTOS CON VARIANTES) ───
        foreach ($this->bebidasConVariantes as $item) {
            $variante = ProductoVariante::query()
                ->where('codigo', $item['codigo_variante'])
                ->with('producto')
                ->first();

            if (! $variante instanceof ProductoVariante || ! $variante->producto instanceof Producto) {
                continue;
            }

            $producto = $variante->producto;

            // a) Stock polimórfico en la tabla `stocks` (utilizado en el formulario de pedidos del restaurante)
            Stock::updateOrCreate(
                [
                    'stockable_type' => Espacio::class,
                    'stockable_id' => $restaurante->id,
                    'producto_id' => $producto->id,
                    'producto_variante_id' => $variante->id,
                ],
                [
                    'cantidad_ideal' => $item['ideal'],
                    'cantidad_actual' => $item['actual'],
                    'estado' => 'disponible',
                    'ultima_verificacion' => now(),
                ]
            );

            // b) Si hay ubicación física, registrar lote e inventario general
            if ($ubicacionRestaurante instanceof Ubicacion) {
                $codigoLote = 'LOTE-BEB-'.strtoupper(substr(md5($variante->codigo.$ubicacionRestaurante->id), 0, 8));
                $lote = Lote::updateOrCreate(
                    [
                        'codigo_lote' => $codigoLote,
                        'producto_id' => $producto->id,
                        'producto_variante_id' => $variante->id,
                        'ubicacion_id' => $ubicacionRestaurante->id,
                    ],
                    [
                        'cantidad_inicial' => $item['ideal'],
                        'cantidad_disponible' => $item['actual'],
                        'costo_unitario' => $item['costo'],
                        'costo_total' => $item['costo'] * $item['actual'],
                        'estado' => EstadoLote::Disponible,
                        'fecha_recepcion' => now()->toDateString(),
                        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
                    ]
                );

                InvStock::updateOrCreate(
                    [
                        'producto_id' => $producto->id,
                        'producto_variante_id' => $variante->id,
                        'ubicacion_id' => $ubicacionRestaurante->id,
                        'lote_id' => $lote->id,
                    ],
                    [
                        'cantidad' => $item['actual'],
                    ]
                );
            }

            // c) Registrar Precio de venta sugerido
            if ($monedaNio instanceof Moneda) {
                Precio::updateOrCreate(
                    [
                        'priceable_type' => ProductoVariante::class,
                        'priceable_id' => $variante->id,
                        'moneda_id' => $monedaNio->id,
                    ],
                    [
                        'precio' => $item['precio_nio'],
                        'fecha_inicio' => now()->subMonths(1)->toDateString(),
                        'estado' => EstadoGeneral::Activo,
                    ]
                );
            }

            if ($monedaUsd instanceof Moneda) {
                Precio::updateOrCreate(
                    [
                        'priceable_type' => ProductoVariante::class,
                        'priceable_id' => $variante->id,
                        'moneda_id' => $monedaUsd->id,
                    ],
                    [
                        'precio' => $item['precio_usd'],
                        'fecha_inicio' => now()->subMonths(1)->toDateString(),
                        'estado' => EstadoGeneral::Activo,
                    ]
                );
            }

            $contadorBebidas++;
        }

        // ─── 5. SEMBRAR SNACKS Y BOTANAS (PRODUCTOS SIMPLES SIN VARIANTES) ───
        $categoriaAbarrotes = Catalogo::query()
            ->where('codigo', 'CAT_PRO_ABARROTES')
            ->first()
            ?? Catalogo::query()->where('codigo', 'CAT_PRO_BEBIDAS')->first();

        $unidadUnidad = Catalogo::query()
            ->where('codigo', 'UNI_UND')
            ->orWhere('codigo', 'like', '%UND%')
            ->first();

        $catId = $categoriaAbarrotes instanceof Catalogo ? $categoriaAbarrotes->id : 1;
        $undId = $unidadUnidad instanceof Catalogo ? $unidadUnidad->id : 1;

        foreach ($this->snacksProductosSimples as $snack) {
            $producto = Producto::firstOrCreate(
                [
                    'nombre' => $snack['nombre'],
                    'categoria_id' => $catId,
                ],
                [
                    'descripcion' => $snack['descripcion'],
                    'unidad_medida_id' => $undId,
                    'tipo' => 1,
                    'estado' => EstadoGeneral::Activo,
                    'rendimiento_porciones' => 1.0,
                ]
            );

            // Stock polimórfico en `stocks` para producto simple (producto_variante_id = null)
            Stock::updateOrCreate(
                [
                    'stockable_type' => Espacio::class,
                    'stockable_id' => $restaurante->id,
                    'producto_id' => $producto->id,
                    'producto_variante_id' => null,
                ],
                [
                    'cantidad_ideal' => $snack['ideal'],
                    'cantidad_actual' => $snack['actual'],
                    'estado' => 'disponible',
                    'ultima_verificacion' => now(),
                ]
            );

            // Lote e inventario físico
            if ($ubicacionRestaurante instanceof Ubicacion) {
                $codigoLote = 'LOTE-SNK-'.strtoupper(substr(md5($snack['codigo'].$ubicacionRestaurante->id), 0, 8));
                $lote = Lote::updateOrCreate(
                    [
                        'codigo_lote' => $codigoLote,
                        'producto_id' => $producto->id,
                        'producto_variante_id' => null,
                        'ubicacion_id' => $ubicacionRestaurante->id,
                    ],
                    [
                        'cantidad_inicial' => $snack['ideal'],
                        'cantidad_disponible' => $snack['actual'],
                        'costo_unitario' => $snack['costo'],
                        'costo_total' => $snack['costo'] * $snack['actual'],
                        'estado' => EstadoLote::Disponible,
                        'fecha_recepcion' => now()->toDateString(),
                        'fecha_vencimiento' => now()->addMonths(4)->toDateString(),
                    ]
                );

                InvStock::updateOrCreate(
                    [
                        'producto_id' => $producto->id,
                        'producto_variante_id' => null,
                        'ubicacion_id' => $ubicacionRestaurante->id,
                        'lote_id' => $lote->id,
                    ],
                    [
                        'cantidad' => $snack['actual'],
                    ]
                );
            }

            // Precios de venta sugeridos
            if ($monedaNio instanceof Moneda) {
                Precio::updateOrCreate(
                    [
                        'priceable_type' => Producto::class,
                        'priceable_id' => $producto->id,
                        'moneda_id' => $monedaNio->id,
                    ],
                    [
                        'precio' => $snack['precio_nio'],
                        'fecha_inicio' => now()->subMonths(1)->toDateString(),
                        'estado' => EstadoGeneral::Activo,
                    ]
                );
            }

            if ($monedaUsd instanceof Moneda) {
                Precio::updateOrCreate(
                    [
                        'priceable_type' => Producto::class,
                        'priceable_id' => $producto->id,
                        'moneda_id' => $monedaUsd->id,
                    ],
                    [
                        'precio' => $snack['precio_usd'],
                        'fecha_inicio' => now()->subMonths(1)->toDateString(),
                        'estado' => EstadoGeneral::Activo,
                    ]
                );
            }

            $contadorSnacks++;
        }

        $this->command->info("Restaurante Bugambilias: Se sembraron existencias para {$contadorBebidas} bebidas y {$contadorSnacks} snacks/productos simples.");
    }
}
