<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Inventario\EstadoLote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockInicialPackSeeder extends Seeder
{
    public function run(): void
    {
        $bodegaId = DB::table('ubicaciones')->where('tipo', 'almacen')->where('estado', 1)->value('id');

        if (! $bodegaId) {
            $this->command->warn('No se encontró ninguna bodega activa. Ejecuta UbicacionSeeder primero.');

            return;
        }

        $items = [
            ['codigo' => 'SH-030-S',      'producto_id' => 1,  'variante_id' => 1,  'cantidad' => 200],
            ['codigo' => 'AC-030-S',      'producto_id' => 2,  'variante_id' => 4,  'cantidad' => 200],
            ['codigo' => 'JB-015-P',      'producto_id' => 3,  'variante_id' => 7,  'cantidad' => 300],
            ['codigo' => 'GB-001-BCO',    'producto_id' => 4,  'variante_id' => 10, 'cantidad' => 100],
            ['codigo' => 'KD-001-EST',    'producto_id' => 5,  'variante_id' => 13, 'cantidad' => 100],
            ['codigo' => 'BOL-HAB-AZUL',  'producto_id' => 6,  'variante_id' => 16, 'cantidad' => 150],
            ['codigo' => 'BLOC-NOT-BCO',  'producto_id' => 7,  'variante_id' => 19, 'cantidad' => 100],
            ['codigo' => 'KC-001-BASICO', 'producto_id' => 8,  'variante_id' => 22, 'cantidad' => 80],
            ['codigo' => 'SB-KING-BCO',   'producto_id' => 30, 'variante_id' => 88, 'cantidad' => 50],
            ['codigo' => 'SE-KING-BCO',   'producto_id' => 31, 'variante_id' => 91, 'cantidad' => 50],
            ['codigo' => 'FA-50-70-BCO',  'producto_id' => 32, 'variante_id' => 94, 'cantidad' => 100],
            ['codigo' => 'T-BANIO-BCO',   'producto_id' => 33, 'variante_id' => 97, 'cantidad' => 80],
            ['codigo' => 'T-MANOS-BCO',   'producto_id' => 34, 'variante_id' => 100, 'cantidad' => 80],
            ['codigo' => 'T-PISO-BCO',    'producto_id' => 35, 'variante_id' => 103, 'cantidad' => 40],
        ];

        $now = now();
        $contador = 0;

        foreach ($items as $item) {
            $exists = DB::table('inv_lotes')
                ->where('producto_id', $item['producto_id'])
                ->where('producto_variante_id', $item['variante_id'])
                ->exists();

            if ($exists) {
                continue;
            }

            $codigoLote = 'LOTE-PACK-'.strtoupper(substr(md5($item['codigo'].$now->timestamp), 0, 8));

            $loteId = DB::table('inv_lotes')->insertGetId([
                'codigo_lote' => $codigoLote,
                'producto_id' => $item['producto_id'],
                'producto_variante_id' => $item['variante_id'],
                'estado' => EstadoLote::Disponible->value,
                'cantidad_disponible' => $item['cantidad'],
                'cantidad_inicial' => $item['cantidad'],
                'ubicacion_id' => $bodegaId,
                'fecha_vencimiento' => now()->addMonths(12),
                'fecha_recepcion' => $now->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $stockExists = DB::table('inv_stock')
                ->where('producto_id', $item['producto_id'])
                ->where('producto_variante_id', $item['variante_id'])
                ->where('ubicacion_id', $bodegaId)
                ->exists();

            if (! $stockExists) {
                DB::table('inv_stock')->insert([
                    'producto_id' => $item['producto_id'],
                    'producto_variante_id' => $item['variante_id'],
                    'lote_id' => $loteId,
                    'ubicacion_id' => $bodegaId,
                    'cantidad' => $item['cantidad'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $contador++;
        }

        $this->command->info("Stock inicial creado para {$contador} productos de packs en bodega ID {$bodegaId}.");
    }
}
