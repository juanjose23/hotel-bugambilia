<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KitSeeder extends Seeder
{
    public function run(): void
    {
        $variantes = DB::table('producto_variantes')
            ->join('productos', 'producto_variantes.producto_id', '=', 'productos.id')
            ->select('producto_variantes.id', 'producto_variantes.codigo', 'productos.nombre as producto_nombre')
            ->get()
            ->keyBy('codigo');

        $find = function (string $codigo) use ($variantes): int {
            if (! $variantes->has($codigo)) {
                $this->command->warn("Variante {$codigo} no encontrada, se omite.");

                return 0;
            }

            return $variantes[$codigo]->id;
        };

        // =====================================================================
        // 1. PACK DE BLANCOS KING
        // =====================================================================
        $packBlancos = DB::table('productos')->insertGetId([
            'nombre' => 'Pack de Blancos King',
            'descripcion' => 'Sábanas, fundas y toallas para cama King Size',
            'categoria_id' => DB::table('catalogos')->where('codigo', 'CAT_PRO_BLAN_SABANAS')->value('id'),
            'unidad_medida_id' => DB::table('catalogos')->where('codigo', 'UNI_UD')->value('id'),
            'tipo' => 2,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $itemsBlancos = [
            ['codigo' => 'SB-KING-BCO', 'cantidad' => 1, 'talla' => 'King'],
            ['codigo' => 'SE-KING-BCO', 'cantidad' => 1, 'talla' => 'King'],
            ['codigo' => 'FA-50-70-BCO', 'cantidad' => 4, 'talla' => '50x70'],
        ];

        foreach ($itemsBlancos as $item) {
            $varianteId = $find($item['codigo']);
            if ($varianteId) {
                DB::table('producto_kit')->insert([
                    'producto_padre_id' => $packBlancos,
                    'producto_variante_id' => $varianteId,
                    'cantidad' => $item['cantidad'],
                    'talla' => $item['talla'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // =====================================================================
        // 2. PACK DE TOALLAS
        // =====================================================================
        $packToallas = DB::table('productos')->insertGetId([
            'nombre' => 'Pack de Toallas',
            'descripcion' => 'Juego de toalla de baño, manos y piso',
            'categoria_id' => DB::table('catalogos')->where('codigo', 'CAT_PRO_BLAN_TOALLAS')->value('id'),
            'unidad_medida_id' => DB::table('catalogos')->where('codigo', 'UNI_UD')->value('id'),
            'tipo' => 2,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $itemsToallas = [
            ['codigo' => 'T-BANIO-BCO', 'cantidad' => 2],
            ['codigo' => 'T-MANOS-BCO', 'cantidad' => 2],
            ['codigo' => 'T-PISO-BCO', 'cantidad' => 1],
        ];

        foreach ($itemsToallas as $item) {
            $varianteId = $find($item['codigo']);
            if ($varianteId) {
                DB::table('producto_kit')->insert([
                    'producto_padre_id' => $packToallas,
                    'producto_variante_id' => $varianteId,
                    'cantidad' => $item['cantidad'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // =====================================================================
        // 3. PACK DE AMENITIES BAÑO
        // =====================================================================
        $packAmenities = DB::table('productos')->insertGetId([
            'nombre' => 'Pack de Amenidades Baño',
            'descripcion' => 'Shampoo, acondicionador, jabón, gorro y kit dental',
            'categoria_id' => DB::table('catalogos')->where('codigo', 'CAT_PRO_AMEN_BANIO')->value('id'),
            'unidad_medida_id' => DB::table('catalogos')->where('codigo', 'UNI_UD')->value('id'),
            'tipo' => 2,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $itemsAmenities = [
            ['codigo' => 'SH-030-S', 'cantidad' => 2],
            ['codigo' => 'AC-030-S', 'cantidad' => 2],
            ['codigo' => 'JB-015-P', 'cantidad' => 2],
            ['codigo' => 'GB-001-BCO', 'cantidad' => 1],
            ['codigo' => 'KD-001-EST', 'cantidad' => 1],
        ];

        foreach ($itemsAmenities as $item) {
            $varianteId = $find($item['codigo']);
            if ($varianteId) {
                DB::table('producto_kit')->insert([
                    'producto_padre_id' => $packAmenities,
                    'producto_variante_id' => $varianteId,
                    'cantidad' => $item['cantidad'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // =====================================================================
        // 4. PACK DE PAPELERÍA HABITACIÓN
        // =====================================================================
        $packPapeleria = DB::table('productos')->insertGetId([
            'nombre' => 'Pack de Papelería Habitación',
            'descripcion' => 'Bolígrafo, bloc de notas y kit de costura',
            'categoria_id' => DB::table('catalogos')->where('codigo', 'CAT_PRO_AMEN_HABIT')->value('id'),
            'unidad_medida_id' => DB::table('catalogos')->where('codigo', 'UNI_UD')->value('id'),
            'tipo' => 2,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $itemsPapeleria = [
            ['codigo' => 'BOL-HAB-AZUL', 'cantidad' => 2],
            ['codigo' => 'BLOC-NOT-BCO', 'cantidad' => 1],
            ['codigo' => 'KC-001-BASICO', 'cantidad' => 1],
        ];

        foreach ($itemsPapeleria as $item) {
            $varianteId = $find($item['codigo']);
            if ($varianteId) {
                DB::table('producto_kit')->insert([
                    'producto_padre_id' => $packPapeleria,
                    'producto_variante_id' => $varianteId,
                    'cantidad' => $item['cantidad'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Packs creados: Blancos King, Toallas, Amenidades Baño, Papelería Habitación.');
    }
}
