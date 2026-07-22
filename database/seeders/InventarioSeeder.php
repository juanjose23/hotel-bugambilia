<?php

namespace Database\Seeders;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\Proveedor;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $almacen = Ubicacion::where('tipo', 'almacen')->first();
        if (! $almacen) {
            return;
        }

        $productos = Producto::limit(10)->get();
        if ($productos->isEmpty()) {
            return;
        }

        $proveedor = Proveedor::first();
        $admin = User::first();

        $costosBase = [12.50, 8.00, 45.00, 3.25, 22.00, 15.00, 60.00, 5.50, 30.00, 18.00];

        // 1. Crear Lotes Disponibles y su Stock en Bodega
        foreach ($productos->take(5)->values() as $i => $prod) {
            $costoUnitario = $costosBase[$i];
            $lote = Lote::create([
                'codigo_lote' => 'LOT-DISP-'.Str::random(4),
                'producto_id' => $prod->id,
                'estado' => EstadoLote::Disponible,
                'cantidad_disponible' => 100.0,
                'cantidad_inicial' => 100.0,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario * 100.0,
                'ubicacion_id' => $almacen->id,
                'fecha_vencimiento' => now()->addMonths(6)->format('Y-m-d'),
                'fecha_recepcion' => now()->subDays(5)->format('Y-m-d'),
                'proveedor_id' => $proveedor?->id,
            ]);

            Stock::create([
                'producto_id' => $prod->id,
                'lote_id' => $lote->id,
                'ubicacion_id' => $almacen->id,
                'cantidad' => 100.0,
            ]);

            MovimientoStock::create([
                'tipo' => 'MOV_ENTRADA',
                'lote_id' => $lote->id,
                'producto_id' => $prod->id,
                'cantidad' => 100.0,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario * 100.0,
                'ubicacion_destino_id' => $almacen->id,
                'creado_por_id' => $admin?->id,
                'referencia' => 'Entrada Inicial',
                'notas' => 'Stock inicial de sistema',
                'created_at' => now()->subDays(5),
            ]);
        }

        // 2. Crear Lotes en Cuarentena y su Stock en Bodega
        foreach ($productos->skip(5)->take(3)->values() as $i => $prod) {
            $costoUnitario = $costosBase[5 + $i];
            $lote = Lote::create([
                'codigo_lote' => 'LOT-CUAR-'.Str::random(4),
                'producto_id' => $prod->id,
                'estado' => EstadoLote::Cuarentena,
                'cantidad_disponible' => 50.0,
                'cantidad_inicial' => 50.0,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario * 50.0,
                'ubicacion_id' => $almacen->id,
                'fecha_vencimiento' => now()->addMonths(12)->format('Y-m-d'),
                'fecha_recepcion' => now()->subDays(2)->format('Y-m-d'),
                'proveedor_id' => $proveedor?->id,
            ]);

            Stock::create([
                'producto_id' => $prod->id,
                'lote_id' => $lote->id,
                'ubicacion_id' => $almacen->id,
                'cantidad' => 50.0,
            ]);

            MovimientoStock::create([
                'tipo' => 'MOV_ENTRADA',
                'lote_id' => $lote->id,
                'producto_id' => $prod->id,
                'cantidad' => 50.0,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario * 50.0,
                'ubicacion_destino_id' => $almacen->id,
                'creado_por_id' => $admin?->id,
                'referencia' => 'Entrada en Cuarentena',
                'notas' => 'Lote requiere inspección',
                'created_at' => now()->subDays(2),
            ]);
        }

        // 3. Crear Lotes Vencidos
        foreach ($productos->skip(8)->take(2)->values() as $i => $prod) {
            $costoUnitario = $costosBase[8 + $i];
            $lote = Lote::create([
                'codigo_lote' => 'LOT-VENC-'.Str::random(4),
                'producto_id' => $prod->id,
                'estado' => EstadoLote::Vencido,
                'cantidad_disponible' => 0.0,
                'cantidad_inicial' => 50.0,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario * 50.0,
                'ubicacion_id' => $almacen->id,
                'fecha_vencimiento' => now()->subDays(10)->format('Y-m-d'),
                'fecha_recepcion' => now()->subMonths(3)->format('Y-m-d'),
                'proveedor_id' => $proveedor?->id,
            ]);

            MovimientoStock::create([
                'tipo' => 'MOV_ENTRADA',
                'lote_id' => $lote->id,
                'producto_id' => $prod->id,
                'cantidad' => 50.0,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario * 50.0,
                'ubicacion_destino_id' => $almacen->id,
                'creado_por_id' => $admin?->id,
                'referencia' => 'Entrada Histórica',
                'created_at' => now()->subMonths(3),
            ]);

            MovimientoStock::create([
                'tipo' => 'MOV_SALIDA',
                'lote_id' => $lote->id,
                'producto_id' => $prod->id,
                'cantidad' => -50.0,
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoUnitario * 50.0,
                'ubicacion_origen_id' => $almacen->id,
                'creado_por_id' => $admin?->id,
                'referencia' => 'Consumo Completo / Vencido',
                'created_at' => now()->subMonths(1),
            ]);
        }
    }
}
