<?php

declare(strict_types=1);

namespace Database\Seeders\Limpieza\Carrito;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Catalogos\Ubicacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Abastecimiento de los carritos móviles A/B con productos de limpieza.
 *
 * El kit del carrito se compone 100% de insumos de limpieza reales de las
 * categorías CAT_PRO_LIMP_QUIM y CAT_PRO_LIMP_HERR (detergentes, desinfectantes,
 * cloro, bolsas, guantes, franelas, atomizadores, etc.), de modo que el
 * colaborador pueda ejecutar los interactors de carrito (AgregarProductosACarrito,
 * ReabastecerCarrito, TrasladarStockSeleccionadoCarrito) y de stock
 * (RegistrarConsumoInsumoLimpieza) con insumos coherentes.
 */
final class LimpiezaCarritoStockSeeder extends Seeder
{
    /**
     * @var array<int, array{codigo: string, cantidad: float, costo_unitario: float}>
     */
    private array $kitCarrito = [
        ['codigo' => 'DET-5L', 'cantidad' => 6.0, 'costo_unitario' => 22.50],
        ['codigo' => 'DES-5L', 'cantidad' => 6.0, 'costo_unitario' => 28.00],
        ['codigo' => 'CLO-5L', 'cantidad' => 4.0, 'costo_unitario' => 18.00],
        ['codigo' => 'ALC-1L', 'cantidad' => 8.0, 'costo_unitario' => 15.00],
        ['codigo' => 'LV-500-SPRAY', 'cantidad' => 6.0, 'costo_unitario' => 12.50],
        ['codigo' => 'LMP-5L', 'cantidad' => 4.0, 'costo_unitario' => 19.00],
        ['codigo' => 'DG-1L', 'cantidad' => 4.0, 'costo_unitario' => 17.00],
        ['codigo' => 'AMB-360-FRES', 'cantidad' => 8.0, 'costo_unitario' => 10.00],
        ['codigo' => 'JLP-500ML', 'cantidad' => 4.0, 'costo_unitario' => 9.50],
        ['codigo' => 'BB-90X110', 'cantidad' => 10.0, 'costo_unitario' => 1.80],
        ['codigo' => 'FRAN-MF-10', 'cantidad' => 6.0, 'costo_unitario' => 8.00],
        ['codigo' => 'GUANT-M', 'cantidad' => 4.0, 'costo_unitario' => 3.50],
        ['codigo' => 'GUANT-L', 'cantidad' => 4.0, 'costo_unitario' => 3.50],
        ['codigo' => 'ATOM-01', 'cantidad' => 4.0, 'costo_unitario' => 5.00],
    ];

    public function run(): void
    {
        $carritoA = Ubicacion::where('nombre', 'Carrito Limpieza A')->first();
        $carritoB = Ubicacion::where('nombre', 'Carrito Limpieza B')->first();

        $this->seedCarritoStock($carritoA);
        $this->seedCarritoStock($carritoB);
    }

    private function seedCarritoStock(?Ubicacion $carrito): void
    {
        if (! $carrito) {
            return;
        }

        foreach ($this->kitCarrito as $item) {
            $variant = DB::table('producto_variantes')
                ->where('codigo', $item['codigo'])
                ->first();

            if (! $variant) {
                continue;
            }

            $exists = DB::table('inv_stock')
                ->where('producto_id', $variant->producto_id)
                ->where('producto_variante_id', $variant->id)
                ->where('ubicacion_id', $carrito->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $costoTotal = $item['costo_unitario'] * $item['cantidad'];
            $loteId = DB::table('inv_lotes')->insertGetId([
                'codigo_lote' => 'LOTE-CARRITO-'.$carrito->id.'-'.$variant->id,
                'producto_id' => $variant->producto_id,
                'producto_variante_id' => $variant->id,
                'estado' => EstadoLote::Disponible->value,
                'cantidad_disponible' => $item['cantidad'],
                'cantidad_inicial' => $item['cantidad'],
                'costo_unitario' => $item['costo_unitario'],
                'costo_total' => $costoTotal,
                'ubicacion_id' => $carrito->id,
                'fecha_vencimiento' => now()->addMonths(12),
                'fecha_recepcion' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('inv_stock')->insert([
                'producto_id' => $variant->producto_id,
                'producto_variante_id' => $variant->id,
                'lote_id' => $loteId,
                'ubicacion_id' => $carrito->id,
                'cantidad' => $item['cantidad'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
