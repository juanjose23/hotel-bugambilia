<?php

declare(strict_types=1);

namespace Database\Seeders\Compras\OrdenesCompra;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\OrdenCompra;
use Database\Seeders\Compras\Cotizaciones\CotizacionesSeeder;
use Database\Seeders\Compras\Solicitudes\SolicitudesSeeder;
use Illuminate\Database\Seeder;

/**
 * Crea las órdenes de compra a partir de la cotización elegida del flujo.
 *
 * @phpstan-import-type ContextoCompras from SolicitudesSeeder
 */
class OrdenesCompraSeeder extends Seeder
{
    public function run(): void
    {
        $ctx = app(SolicitudesSeeder::class)->ejecutar();
        $ctx = app(CotizacionesSeeder::class)->ejecutar($ctx);
        $this->ejecutar($ctx);
    }

    /**
     * @param  ContextoCompras  $ctx
     * @return ContextoCompras
     */
    public function ejecutar(array $ctx): array
    {
        $condicionPago = $ctx['condicionPago'];
        $cotWin = $ctx['cotizaciones']['SOL-WIN'];

        $orden = OrdenCompra::where('codigo', 'OC-2026-WIN')->first();
        if (! $orden && $cotWin) {
            $orden = OrdenCompra::create([
                'codigo' => 'OC-2026-WIN',
                'proveedor_id' => $cotWin->proveedor_id,
                'solicitud_id' => $cotWin->solicitud_id,
                'cotizacion_id' => $cotWin->id,
                'fecha_orden' => now()->subDays(15),
                'condicion_pago_id' => $condicionPago?->id,
                'estado' => EstadoOrdenCompra::Recibida,
                'subtotal' => 600,
                'total' => 690,
            ]);

            foreach ($cotWin->items as $cItem) {
                $orden->items()->create([
                    'producto_id' => $cItem->producto_id,
                    'producto_variante_id' => $cItem->producto_variante_id,
                    'cantidad' => $cItem->cantidad,
                    'precio_unitario' => $cItem->precio_unitario,
                    'subtotal' => $cItem->subtotal,
                    'unidad_medida_id' => $ctx['unidadMedida']?->id,
                ]);
            }
        }

        return $ctx;
    }
}
