<?php

declare(strict_types=1);

namespace Database\Seeders\Compras\Recepciones;

use App\Enums\Compras\EstadoRecepcion;
use App\Interactors\Inventario\Recepciones\RegistrarEntradaRecepcion;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\User;
use Database\Seeders\Compras\Cotizaciones\CotizacionesSeeder;
use Database\Seeders\Compras\OrdenesCompra\OrdenesCompraSeeder;
use Database\Seeders\Compras\Solicitudes\SolicitudesSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Registra la recepción de la orden de compra del flujo completo y genera
 * el inventario correspondiente vía el interactor RegistrarEntradaRecepcion.
 *
 * @phpstan-import-type ContextoCompras from SolicitudesSeeder
 */
class RecepcionesSeeder extends Seeder
{
    public function run(): void
    {
        $ctx = app(SolicitudesSeeder::class)->ejecutar();
        $ctx = app(CotizacionesSeeder::class)->ejecutar($ctx);
        $ctx = app(OrdenesCompraSeeder::class)->ejecutar($ctx);
        $this->ejecutar($ctx);
    }

    /**
     * @param  ContextoCompras  $ctx
     */
    public function ejecutar(array $ctx): void
    {
        $admin = User::where('email', 'admin@hotel.com')->first() ?? User::first();
        if (! $admin) {
            return;
        }

        $orden = OrdenCompra::where('codigo', 'OC-2026-WIN')->first();
        if (! $orden) {
            return;
        }

        if (RecepcionCompra::where('codigo', 'RC-WIN-001')->exists()) {
            return;
        }

        DB::transaction(function () use ($orden, $admin) {
            $recepcion = RecepcionCompra::create([
                'codigo' => 'RC-WIN-001',
                'orden_compra_id' => $orden->id,
                'fecha_recepcion' => now()->subDays(5),
                'recibido_por_id' => $admin->id,
                'estado' => EstadoRecepcion::Completa,
                'notas' => 'Entrega perfecta según contrato.',
            ]);

            $loteProveedor = 'PROV-LOT-'.Str::upper(Str::random(5));

            $originalItems = collect($orden->items)->all();
            $itemsData = [];

            foreach ($originalItems as $oi) {
                $item = $recepcion->items()->create([
                    'orden_item_id' => $oi->id,
                    'producto_id' => $oi->producto_id,
                    'producto_variante_id' => $oi->producto_variante_id,
                    'cantidad_recibida' => $oi->cantidad,
                    'cantidad_rechazada' => 0.0,
                    'lote_proveedor' => $loteProveedor,
                    'fecha_vencimiento' => now()->addMonths(12)->format('Y-m-d'),
                ]);
                $createdIds[] = $item->id;
                $itemsData[] = [
                    'id' => $item->id,
                    'producto_id' => (int) $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id !== null ? (int) $item->producto_variante_id : null,
                    'cantidad_recibida' => (float) $item->cantidad_recibida,
                    'lote_proveedor' => $item->lote_proveedor,
                    'fecha_vencimiento' => $item->fecha_vencimiento?->format('Y-m-d'),
                ];
            }

            app(RegistrarEntradaRecepcion::class)->ejecutar(
                nuevoEstado: 'Completa',
                items: $itemsData,
                proveedorId: $orden->proveedor_id,
                creadoPorId: $admin->id,
            );
        });
    }
}
