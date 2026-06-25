<?php

namespace App\UseCases\Compras\OrdenesCompra\Mutations;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use App\Services\Compras\NotificadorCompras;
use Illuminate\Support\Facades\DB;

class GenerarOrdenDesdeCotizacion
{
    public function execute(int $cotizacionId): OrdenCompra
    {
        $orden = DB::transaction(function () use ($cotizacionId) {
            $cotizacion = Cotizacion::with(['items', 'proveedor.persona.personaJuridica', 'solicitud.items'])->findOrFail($cotizacionId);

            $itemsElegidos = $cotizacion->items->where('es_elegido', true);

            if ($itemsElegidos->isEmpty() && $cotizacion->es_elegida) {
                $itemsElegidos = $cotizacion->items;
            }

            if ($itemsElegidos->isEmpty()) {
                throw new \Exception('Debe seleccionar al menos un ítem para generar la orden.');
            }

            $codigo = app(GenerarCodigoOrdenCompra::class)->execute();

            $rawSubtotal = $itemsElegidos->sum('subtotal') ?? 0;
            $subtotal = is_numeric($rawSubtotal) ? (float) $rawSubtotal : 0.0;
            $impuestos = $subtotal * 0.15;
            $total = $subtotal + $impuestos;

            $proveedorNombre = $cotizacion->proveedor?->persona?->personaJuridica->razon_social
                ?? $cotizacion->proveedor?->persona->primer_nombre
                ?? 'Proveedor #'.$cotizacion->proveedor_id;

            $cotizacion->solicitud?->update(['estado' => EstadoSolicitud::Aprobada]);

            $orden = OrdenCompra::create([
                'codigo' => $codigo,
                'proveedor_id' => $cotizacion->proveedor_id,
                'solicitud_id' => $cotizacion->solicitud_id,
                'cotizacion_id' => $cotizacion->id,
                'fecha_orden' => now(),
                'condicion_pago_id' => $cotizacion->condicion_pago_id,
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total,
                'estado' => EstadoOrdenCompra::Borrador,
                'notas' => "Generada desde Cotización #{$cotizacion->id} de {$proveedorNombre}",
            ]);

            foreach ($itemsElegidos as $item) {
                $solicitudItem = $cotizacion->solicitud?->items
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

        app(NotificadorCompras::class)->ordenCreada($orden);

        if ($orden->solicitud) {
            app(NotificadorCompras::class)->solicitudAprobada($orden->solicitud);
        }

        return $orden;
    }
}
