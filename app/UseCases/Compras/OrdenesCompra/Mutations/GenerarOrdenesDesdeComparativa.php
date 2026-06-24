<?php

namespace App\UseCases\Compras\OrdenesCompra\Mutations;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\CotizacionItem;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\Solicitud;
use App\Services\Compras\NotificadorCompras;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerarOrdenesDesdeComparativa
{
    public function execute(int $solicitudId): int
    {
        $solicitud = Solicitud::with('items')->findOrFail($solicitudId);

        $cotizacionesConGanadores = Cotizacion::where('solicitud_id', $solicitudId)
            ->with(['items' => fn ($q) => $q->where('es_elegido', true)])
            ->whereHas('items', fn ($q) => $q->where('es_elegido', true))
            ->get();

        if ($cotizacionesConGanadores->isEmpty()) {
            return 0;
        }

        $ordenesCreadas = 0;

        foreach ($cotizacionesConGanadores as $cot) {
            $itemsElegidos = $cot->items;

            if ($itemsElegidos->isEmpty()) {
                continue;
            }

            if (OrdenCompra::where('solicitud_id', $solicitudId)
                ->where('cotizacion_id', $cot->id)
                ->where('estado', '!=', EstadoOrdenCompra::Cancelada)
                ->exists()) {
                continue;
            }

            $this->crearOrden($cot, $itemsElegidos, $solicitud);

            $ordenesCreadas++;
        }

        if ($ordenesCreadas > 0) {
            app(NotificadorCompras::class)->solicitudAprobada($solicitud);
        }

        return $ordenesCreadas;
    }

    /**
     * @param  Collection<int, CotizacionItem>  $itemsElegidos
     */
    private function crearOrden(Cotizacion $cot, Collection $itemsElegidos, Solicitud $solicitud): OrdenCompra
    {
        return DB::transaction(function () use ($cot, $itemsElegidos, $solicitud) {
            $codigo = app(GenerarCodigoOrdenCompra::class)->execute();

            $rawSubtotal = $itemsElegidos->sum('subtotal') ?? 0;
            $subtotal = is_numeric($rawSubtotal) ? (float) $rawSubtotal : 0.0;
            $impuestos = round($subtotal * 0.15, 2);
            $total = $subtotal + $impuestos;

            $orden = OrdenCompra::create([
                'codigo' => $codigo,
                'proveedor_id' => $cot->proveedor_id,
                'solicitud_id' => $cot->solicitud_id,
                'cotizacion_id' => $cot->id,
                'fecha_orden' => now(),
                'condicion_pago_id' => $cot->condicion_pago_id,
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total,
                'estado' => EstadoOrdenCompra::Borrador,
                'notas' => "Generada desde Cotización #{$cot->id} - Comparativa de Precios",
            ]);

            foreach ($itemsElegidos as $item) {
                $solicitudItem = $solicitud->items
                    ->where('producto_id', $item->producto_id)
                    ->where('producto_variante_id', $item->producto_variante_id)
                    ->first();

                $orden->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'unidad_medida_id' => $solicitudItem?->unidad_medida_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                ]);
            }

            return $orden;
        });
    }
}
