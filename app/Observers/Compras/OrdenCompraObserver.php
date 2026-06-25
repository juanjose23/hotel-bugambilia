<?php

namespace App\Observers\Compras;

use App\Enums\Compras\EstadoCotizacion;
use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;

class OrdenCompraObserver
{
    public function created(OrdenCompra $orden): void
    {
        $this->actualizarFlujo($orden);
    }

    public function updated(OrdenCompra $orden): void
    {
        if ($orden->isDirty('cotizacion_id') || $orden->isDirty('solicitud_id')) {
            $this->actualizarFlujo($orden);
        }
    }

    public function updating(OrdenCompra $orden): void
    {
        if ($orden->isDirty('estado') && $orden->estado === EstadoOrdenCompra::Recibida) {
            $subtotalRaw = $orden->getRawOriginal('subtotal') ?? 0;
            $originalSubtotal = is_numeric($subtotalRaw) ? (float) $subtotalRaw : 0.0;
            $impuestosRaw = $orden->getRawOriginal('impuestos') ?? 0;
            $originalImpuestos = is_numeric($impuestosRaw) ? (float) $impuestosRaw : 0.0;

            $subtotal = 0.0;
            foreach ($orden->items as $item) {
                $cantRecibida = (float) $item->recepcionItems()
                    ->whereHas('recepcion', fn ($q) => $q->whereIn('estado', [
                        EstadoRecepcion::Completa,
                        EstadoRecepcion::Parcial,
                    ]))
                    ->sum('cantidad_recibida');

                $item->cantidad = $cantRecibida;
                $item->subtotal = round($cantRecibida * (float) $item->precio_unitario, 2);
                $item->saveQuietly();

                $subtotal += $item->subtotal;
            }

            $impuestos = $originalSubtotal > 0.0
                ? round(($subtotal / $originalSubtotal) * $originalImpuestos, 2)
                : 0.0;

            $orden->subtotal = $subtotal;
            $orden->impuestos = $impuestos;
            $orden->total = $subtotal + $impuestos;
        }
    }

    private function actualizarFlujo(OrdenCompra $orden): void
    {
        if ($orden->cotizacion_id) {
            Cotizacion::where('id', $orden->cotizacion_id)
                ->update(['estado' => EstadoCotizacion::Aceptada]);

            if ($orden->solicitud_id) {
                Cotizacion::where('solicitud_id', $orden->solicitud_id)
                    ->where('id', '!=', $orden->cotizacion_id)
                    ->where('estado', EstadoCotizacion::Activa)
                    ->update(['estado' => EstadoCotizacion::Rechazada]);
            }
        }
    }
}
