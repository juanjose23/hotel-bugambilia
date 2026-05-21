<?php

namespace Database\Seeders;

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\Proveedor;
use App\Models\Espacios\Area;
use App\Models\Espacios\Habitacion;
use App\Models\Espacios\InventarioFijo;
use App\Models\Espacios\PlantillaDotacion;
use App\Models\Espacios\PlantillaItem;
use App\Models\Espacios\TipoHabitacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\Inventario\ParStock;
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

        // 1. Crear Lotes Disponibles y su Stock en Bodega
        foreach ($productos->take(5) as $prod) {
            $lote = Lote::create([
                'codigo_lote' => 'LOT-DISP-'.Str::random(4),
                'producto_id' => $prod->id,
                'estado' => EstadoLote::Disponible,
                'cantidad_disponible' => 100.0,
                'cantidad_inicial' => 100.0,
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
                'ubicacion_destino_id' => $almacen->id,
                'creado_por_id' => $admin?->id,
                'referencia' => 'Entrada Inicial',
                'notas' => 'Stock inicial de sistema',
                'created_at' => now()->subDays(5),
            ]);
        }

        // 2. Crear Lotes en Cuarentena y su Stock en Bodega
        foreach ($productos->skip(5)->take(3) as $prod) {
            $lote = Lote::create([
                'codigo_lote' => 'LOT-CUAR-'.Str::random(4),
                'producto_id' => $prod->id,
                'estado' => EstadoLote::Cuarentena,
                'cantidad_disponible' => 50.0,
                'cantidad_inicial' => 50.0,
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
                'ubicacion_destino_id' => $almacen->id,
                'creado_por_id' => $admin?->id,
                'referencia' => 'Entrada en Cuarentena',
                'notas' => 'Lote requiere inspección',
                'created_at' => now()->subDays(2),
            ]);
        }

        // 3. Crear Lotes Vencidos
        foreach ($productos->skip(8)->take(2) as $prod) {
            $lote = Lote::create([
                'codigo_lote' => 'LOT-VENC-'.Str::random(4),
                'producto_id' => $prod->id,
                'estado' => EstadoLote::Vencido,
                'cantidad_disponible' => 0.0,
                'cantidad_inicial' => 50.0,
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
                'ubicacion_origen_id' => $almacen->id,
                'creado_por_id' => $admin?->id,
                'referencia' => 'Consumo Completo / Vencido',
                'created_at' => now()->subMonths(1),
            ]);
        }

        // 4. Crear Tipos de Habitación
        $tipoSimple = TipoHabitacion::create([
            'codigo' => 'STD-SMP',
            'nombre' => 'Estándar Simple',
            'capacidad_max' => 1,
            'descripcion' => 'Habitación estándar con una cama individual',
        ]);

        $tipoDoble = TipoHabitacion::create([
            'codigo' => 'STD-DBL',
            'nombre' => 'Estándar Doble',
            'capacidad_max' => 2,
            'descripcion' => 'Habitación estándar con dos camas matrimoniales',
        ]);

        $tipoSuite = TipoHabitacion::create([
            'codigo' => 'STE-PREM',
            'nombre' => 'Suite Premium',
            'capacidad_max' => 4,
            'descripcion' => 'Suite ejecutiva premium con vista al jardín',
        ]);

        // 5. Crear Habitaciones
        $hab101 = Habitacion::create([
            'numero' => '101',
            'tipo_id' => $tipoSimple->id,
            'piso' => 1,
            'estado' => 'disponible',
            'activa' => true,
        ]);

        $hab102 = Habitacion::create([
            'numero' => '102',
            'tipo_id' => $tipoDoble->id,
            'piso' => 1,
            'estado' => 'disponible',
            'activa' => true,
        ]);

        $hab201 = Habitacion::create([
            'numero' => '201',
            'tipo_id' => $tipoSuite->id,
            'piso' => 2,
            'estado' => 'disponible',
            'activa' => true,
        ]);

        // 6. Crear Áreas Comunes
        $restaurante = Area::create([
            'codigo' => 'REST-BUG',
            'nombre' => 'Restaurante Bugambilias',
            'tipo' => 'restaurante',
            'capacidad' => 120,
            'activa' => true,
        ]);

        $spa = Area::create([
            'codigo' => 'SPA-GYM',
            'nombre' => 'Spa & Gimnasio',
            'tipo' => 'area_comun',
            'capacidad' => 30,
            'activa' => true,
        ]);

        // 7. Crear Plantilla de Dotación
        $plantillaSimple = PlantillaDotacion::create([
            'nombre' => 'Dotación Básica Simple',
            'espacio_tipo' => 'habitacion',
            'tipo_id' => $tipoSimple->id,
            'activa' => true,
            'notas' => 'Dotación básica para habitaciones individuales',
        ]);

        // Asignar algunos consumibles a la plantilla
        foreach ($productos->take(3) as $prod) {
            PlantillaItem::create([
                'plantilla_id' => $plantillaSimple->id,
                'producto_id' => $prod->id,
                'cantidad' => 2.0,
                'es_reposicion_diaria' => true,
            ]);
        }

        // 8. Crear Inventario Fijo (Activos Fijos) en Habitación 101 y Restaurante
        $tvActivo = Producto::where('nombre', 'like', '%televisor%')->orWhere('nombre', 'like', '%TV%')->first();
        if (! $tvActivo) {
            $tvActivo = $productos->first();
        }

        InventarioFijo::create([
            'espacio_tipo' => 'habitacion',
            'espacio_id' => $hab101->id,
            'producto_id' => $tvActivo->id,
            'cantidad' => 1.0,
            'estado' => 'operativo',
            'notas' => 'Smart TV 43 pulgadas',
        ]);

        InventarioFijo::create([
            'espacio_tipo' => 'area',
            'espacio_id' => $restaurante->id,
            'producto_id' => $tvActivo->id,
            'cantidad' => 2.0,
            'estado' => 'operativo',
            'notas' => 'Smart TV 55 pulgadas para área de comensales',
        ]);

        // 9. Crear Bodegas secundarias para los pisos y Par Stock
        $bodegaPiso1 = Ubicacion::create([
            'tipo' => 'almacen',
            'nombre' => 'Bodega Piso 1',
            'descripcion' => 'Bodega secundaria para dotación de habitaciones de planta baja',
            'orden' => 5,
            'estado' => 1,
        ]);

        // Crear Par Stock para Bodega Piso 1
        foreach ($productos->take(3) as $prod) {
            ParStock::create([
                'producto_id' => $prod->id,
                'ubicacion_id' => $bodegaPiso1->id,
                'stock_minimo' => 10.0,
                'stock_objetivo' => 50.0,
            ]);
        }
    }
}
