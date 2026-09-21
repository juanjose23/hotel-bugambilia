<?php

declare(strict_types=1);

namespace Database\Seeders\Inventario\Packs;

use App\Enums\Catalogos\TipoProducto;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\ProductoKit;
use Illuminate\Database\Seeder;

/**
 * Seeder de Kits / Packs de Productos para el Hotel Bugambilias.
 *
 * Configura los paquetes operativos utilizados en camarería, lencería,
 * cortesías para suites, amenidades de baño y suministros de limpieza.
 */
final class KitSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cargar unidades y categorías de catálogo
        /** @var array<string, int> $catalogos */
        $catalogos = [];
        foreach (Catalogo::query()->whereIn('codigo', [
            'UNI_UD',
            'UNI_PAQ',
            'CAT_PRO_BLAN_SABANAS',
            'CAT_PRO_BLAN_TOALLAS',
            'CAT_PRO_AMEN_BANIO',
            'CAT_PRO_AMEN_HABIT',
            'CAT_PRO_LIMP_HERR',
            'CAT_PRO_LIMP_QUIM',
        ])->get() as $cat) {
            $catalogos[$cat->codigo] = $cat->id;
        }

        $unidadUdId = $catalogos['UNI_UD'] ?? 1;

        // 2. Cargar todas las variantes disponibles indexadas por código
        /** @var array<string, int> $variantes */
        $variantes = [];
        foreach (ProductoVariante::query()->get() as $var) {
            $variantes[$var->codigo] = $var->id;
        }

        // 3. Definición de Packs con lógica de operación hotelera
        /**
         * @var array<int, array{
         *     nombre: string,
         *     descripcion: string,
         *     categoria_codigo: string,
         *     unidad_codigo?: string,
         *     items: array<int, array{codigo: string, cantidad: float|int, talla?: string}>
         * }> $kitsDefinidos
         */
        $kitsDefinidos = [
            // --- BLANCOS / LENCERÍA ---
            [
                'nombre' => 'Pack de Blancos King',
                'descripcion' => 'Juego completo de sábanas, fundas y protector para cama King Size',
                'categoria_codigo' => 'CAT_PRO_BLAN_SABANAS',
                'items' => [
                    ['codigo' => 'SB-KING-BCO', 'cantidad' => 1, 'talla' => 'King'],
                    ['codigo' => 'SE-KING-BCO', 'cantidad' => 1, 'talla' => 'King'],
                    ['codigo' => 'FA-50-70-BCO', 'cantidad' => 4, 'talla' => '50x70'],
                    ['codigo' => 'PROT-KING-BCO', 'cantidad' => 1, 'talla' => 'King'],
                ],
            ],
            [
                'nombre' => 'Pack de Blancos Queen',
                'descripcion' => 'Juego de sábanas ajustables, encimeras y fundas para cama Queen Size',
                'categoria_codigo' => 'CAT_PRO_BLAN_SABANAS',
                'items' => [
                    ['codigo' => 'SB-QUEEN-BCO', 'cantidad' => 1, 'talla' => 'Queen'],
                    ['codigo' => 'SE-QUEEN-BCO', 'cantidad' => 1, 'talla' => 'Queen'],
                    ['codigo' => 'FA-50-70-BCO', 'cantidad' => 2, 'talla' => '50x70'],
                    ['codigo' => 'PROT-QUEEN-BCO', 'cantidad' => 1, 'talla' => 'Queen'],
                ],
            ],
            [
                'nombre' => 'Pack de Blancos Matrimonial',
                'descripcion' => 'Juego de sábanas y fundas de almohada para cama matrimonial',
                'categoria_codigo' => 'CAT_PRO_BLAN_SABANAS',
                'items' => [
                    ['codigo' => 'SB-MAT-BCO', 'cantidad' => 1, 'talla' => 'Matrimonial'],
                    ['codigo' => 'SE-MAT-BCO', 'cantidad' => 1, 'talla' => 'Matrimonial'],
                    ['codigo' => 'FA-50-70-BCO', 'cantidad' => 2, 'talla' => '50x70'],
                ],
            ],
            [
                'nombre' => 'Pack de Toallas',
                'descripcion' => 'Dotación estándar de toallas de baño, manos y piso para habitación',
                'categoria_codigo' => 'CAT_PRO_BLAN_TOALLAS',
                'items' => [
                    ['codigo' => 'T-BANIO-BCO', 'cantidad' => 2],
                    ['codigo' => 'T-MANOS-BCO', 'cantidad' => 2],
                    ['codigo' => 'T-PISO-BCO', 'cantidad' => 1],
                    ['codigo' => 'T-FACIAL-BCO', 'cantidad' => 2],
                ],
            ],
            [
                'nombre' => 'Pack de Toallas Piscina y Spa',
                'descripcion' => 'Set de toallas de gran formato para áreas de piscina y relajación',
                'categoria_codigo' => 'CAT_PRO_BLAN_TOALLAS',
                'items' => [
                    ['codigo' => 'T-PISC-AZUL', 'cantidad' => 2],
                    ['codigo' => 'T-SPA-BCO', 'cantidad' => 2],
                ],
            ],

            // --- AMENIDADES DE BAÑO ---
            [
                'nombre' => 'Pack de Amenidades Baño',
                'descripcion' => 'Shampoo, acondicionador, jabón, gorro de baño y kit dental estándar',
                'categoria_codigo' => 'CAT_PRO_AMEN_BANIO',
                'items' => [
                    ['codigo' => 'SH-030-S', 'cantidad' => 2],
                    ['codigo' => 'AC-030-S', 'cantidad' => 2],
                    ['codigo' => 'JB-015-P', 'cantidad' => 2],
                    ['codigo' => 'GB-001-BCO', 'cantidad' => 1],
                    ['codigo' => 'KD-001-EST', 'cantidad' => 1],
                    ['codigo' => 'PH-ROLLO-STD', 'cantidad' => 2],
                ],
            ],
            [
                'nombre' => 'Pack de Amenidades Baño VIP Suite',
                'descripcion' => 'Kit de tocador de lujo: lociones, gel de ducha, afeitado y zapatillas',
                'categoria_codigo' => 'CAT_PRO_AMEN_BANIO',
                'items' => [
                    ['codigo' => 'SH-060-S', 'cantidad' => 2],
                    ['codigo' => 'AC-060-S', 'cantidad' => 2],
                    ['codigo' => 'JB-025-P', 'cantidad' => 2],
                    ['codigo' => 'GEL-030-S', 'cantidad' => 2],
                    ['codigo' => 'LOC-060-T', 'cantidad' => 1],
                    ['codigo' => 'GB-001-BCO', 'cantidad' => 2],
                    ['codigo' => 'KD-001-PREMIUM', 'cantidad' => 2],
                    ['codigo' => 'KA-001-PREMIUM', 'cantidad' => 1],
                    ['codigo' => 'ZP-001-BCO', 'cantidad' => 2],
                ],
            ],

            // --- AMENIDADES Y PAPELERÍA DE HABITACIÓN ---
            [
                'nombre' => 'Pack de Papelería Habitación',
                'descripcion' => 'Bolígrafo, bloc de notas, kit de costura y directorio de servicios',
                'categoria_codigo' => 'CAT_PRO_AMEN_HABIT',
                'items' => [
                    ['codigo' => 'BOL-HAB-AZUL', 'cantidad' => 2],
                    ['codigo' => 'BLOC-NOT-BCO', 'cantidad' => 1],
                    ['codigo' => 'KC-001-BASICO', 'cantidad' => 1],
                    ['codigo' => 'TARJ-BVN-STD', 'cantidad' => 1],
                    ['codigo' => 'GUI-HOT-STD', 'cantidad' => 1],
                    ['codigo' => 'BOL-ROPA-STD', 'cantidad' => 2],
                ],
            ],

            // --- SUMINISTROS Y LIMPIEZA DE CAMARERA ---
            [
                'nombre' => 'Kit de Limpieza Camarera Housekeeping',
                'descripcion' => 'Herramientas esenciales de camarera: atomizadores, microfibras, guantes y esponjas',
                'categoria_codigo' => 'CAT_PRO_LIMP_HERR',
                'items' => [
                    ['codigo' => 'ATOM-01', 'cantidad' => 2],
                    ['codigo' => 'FRAN-MF-10', 'cantidad' => 1],
                    ['codigo' => 'GUANT-M', 'cantidad' => 2],
                    ['codigo' => 'ESP-DOBLE-20', 'cantidad' => 1],
                    ['codigo' => 'CIN-01-BCO', 'cantidad' => 1],
                    ['codigo' => 'BOL-BAS-15', 'cantidad' => 5],
                ],
            ],
            [
                'nombre' => 'Kit de Desinfección y Sanitización',
                'descripcion' => 'Químicos concentrados para desinfección de superficies, baños y pisos',
                'categoria_codigo' => 'CAT_PRO_LIMP_QUIM',
                'items' => [
                    ['codigo' => 'DES-500ML', 'cantidad' => 2],
                    ['codigo' => 'LV-500-SPRAY', 'cantidad' => 1],
                    ['codigo' => 'LMP-1L', 'cantidad' => 1],
                    ['codigo' => 'CLO-1L', 'cantidad' => 1],
                    ['codigo' => 'AMB-360-FRES', 'cantidad' => 1],
                ],
            ],
        ];

        $kitsCreados = 0;
        $itemsAsociados = 0;

        foreach ($kitsDefinidos as $kitData) {
            $categoriaId = $catalogos[$kitData['categoria_codigo']] ?? 1;
            $unidadMedidaId = isset($kitData['unidad_codigo'])
                ? ($catalogos[$kitData['unidad_codigo']] ?? $unidadUdId)
                : $unidadUdId;

            // 4. Crear o actualizar el Producto Maestro del Pack
            /** @var Producto $productoPack */
            $productoPack = Producto::query()->updateOrCreate(
                [
                    'nombre' => $kitData['nombre'],
                    'categoria_id' => $categoriaId,
                ],
                [
                    'descripcion' => $kitData['descripcion'],
                    'unidad_medida_id' => $unidadMedidaId,
                    'tipo' => TipoProducto::NoPerecedero->value,
                    'estado' => EstadoGeneral::Activo,
                    'es_transformable' => false,
                ]
            );

            $kitsCreados++;

            // 5. Vincular cada componente / variante en producto_kit
            foreach ($kitData['items'] as $item) {
                if (! isset($variantes[$item['codigo']])) {
                    $this->command->warn("Variante '{$item['codigo']}' no encontrada para el kit '{$kitData['nombre']}', se omite.");

                    continue;
                }

                $varianteId = $variantes[$item['codigo']];

                ProductoKit::query()->updateOrCreate(
                    [
                        'producto_padre_id' => $productoPack->id,
                        'producto_variante_id' => $varianteId,
                    ],
                    [
                        'cantidad' => $item['cantidad'],
                        'talla' => $item['talla'] ?? null,
                    ]
                );

                $itemsAsociados++;
            }
        }

        $this->command->info("KitSeeder ejecutado con éxito: {$kitsCreados} packs configurados con {$itemsAsociados} componentes vinculados.");
    }
}
