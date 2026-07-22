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
        $bodegaIdVal = DB::table('ubicaciones')->where('tipo', 'almacen')->where('estado', 1)->value('id');

        if (! is_numeric($bodegaIdVal)) {
            $this->command->warn('No se encontró ninguna bodega activa. Ejecuta UbicacionSeeder primero.');

            return;
        }
        $bodegaId = (int) $bodegaIdVal;

        $items = [
            ['codigo' => 'SH-030-S',      'cantidad' => 200, 'costo_unitario' => 8.50],
            ['codigo' => 'AC-030-S',      'cantidad' => 200, 'costo_unitario' => 9.00],
            ['codigo' => 'JB-015-P',      'cantidad' => 300, 'costo_unitario' => 3.75],
            ['codigo' => 'CR-030-S',      'cantidad' => 200, 'costo_unitario' => 15.00],
            ['codigo' => 'PH-ROLLO-STD',  'cantidad' => 200, 'costo_unitario' => 2.50],
            ['codigo' => 'AG-500-BOT',    'cantidad' => 200, 'costo_unitario' => 1.80],
            ['codigo' => 'GB-001-BCO',    'cantidad' => 100, 'costo_unitario' => 22.00],
            ['codigo' => 'KD-001-EST',    'cantidad' => 100, 'costo_unitario' => 35.00],
            ['codigo' => 'BOL-HAB-AZUL',  'cantidad' => 150, 'costo_unitario' => 5.25],
            ['codigo' => 'BLOC-NOT-BCO',  'cantidad' => 100, 'costo_unitario' => 4.00],
            ['codigo' => 'KC-001-BASICO', 'cantidad' => 80,  'costo_unitario' => 18.50],
            ['codigo' => 'SB-KING-BCO',   'cantidad' => 50,  'costo_unitario' => 45.00],
            ['codigo' => 'SE-KING-BCO',   'cantidad' => 50,  'costo_unitario' => 42.00],
            ['codigo' => 'FA-50-70-BCO',  'cantidad' => 100, 'costo_unitario' => 12.00],
            ['codigo' => 'T-BANIO-BCO',   'cantidad' => 80,  'costo_unitario' => 28.00],
            ['codigo' => 'T-MANOS-BCO',   'cantidad' => 80,  'costo_unitario' => 18.00],
            ['codigo' => 'T-PISO-BCO',    'cantidad' => 40,  'costo_unitario' => 35.00],
        ];

        $now = now();
        $contador = 0;

        foreach ($items as $item) {
            $variant = DB::table('producto_variantes')
                ->where('codigo', $item['codigo'])
                ->first();

            if (! $variant) {
                $this->command->warn("No se encontró la variante con código: {$item['codigo']}");

                continue;
            }

            $exists = DB::table('inv_lotes')
                ->where('producto_id', $variant->producto_id)
                ->where('producto_variante_id', $variant->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $codigoLote = 'LOTE-PACK-'.strtoupper(substr(md5($item['codigo'].$now->timestamp), 0, 8));

            $costoTotal = $item['costo_unitario'] * $item['cantidad'];
            $loteId = DB::table('inv_lotes')->insertGetId([
                'codigo_lote' => $codigoLote,
                'producto_id' => $variant->producto_id,
                'producto_variante_id' => $variant->id,
                'estado' => EstadoLote::Disponible->value,
                'cantidad_disponible' => $item['cantidad'],
                'cantidad_inicial' => $item['cantidad'],
                'costo_unitario' => $item['costo_unitario'],
                'costo_total' => $costoTotal,
                'ubicacion_id' => $bodegaId,
                'fecha_vencimiento' => now()->addMonths(12),
                'fecha_recepcion' => $now->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $stockExists = DB::table('inv_stock')
                ->where('producto_id', $variant->producto_id)
                ->where('producto_variante_id', $variant->id)
                ->where('ubicacion_id', $bodegaId)
                ->exists();

            if (! $stockExists) {
                DB::table('inv_stock')->insert([
                    'producto_id' => $variant->producto_id,
                    'producto_variante_id' => $variant->id,
                    'lote_id' => $loteId,
                    'ubicacion_id' => $bodegaId,
                    'cantidad' => $item['cantidad'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $contador++;
        }

        $this->command->info("Stock inicial creado para {$contador} productos de packs en bodega ID ".((int) $bodegaId).'.');
    }
}
