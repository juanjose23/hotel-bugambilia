<?php

namespace App\UseCases\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoSolicitud;
use App\Models\Compras\Cotizacion;
use App\Models\Compras\OrdenCompra;
use Illuminate\Support\Facades\DB;

class GenerarOrdenDesdeCotizacion
{
    public function execute(int $cotizacionId): OrdenCompra
    {
        return DB::transaction(function () use ($cotizacionId) {
            $cotizacion = Cotizacion::with(['items', 'proveedor.persona.personaJuridica', 'solicitud.items'])->findOrFail($cotizacionId);

            $itemsElegidos = $cotizacion->items()->where('es_elegido', true)->get();

            if ($itemsElegidos->isEmpty() && $cotizacion->es_elegida) {
                $itemsElegidos = $cotizacion->items;
            }

            if ($itemsElegidos->isEmpty()) {
                throw new \Exception('Debe seleccionar al menos un ítem para generar la orden.');
            }

            $year = now()->year;
            $count = OrdenCompra::whereYear('fecha_orden', $year)->count() + 1;
            $codigo = "OC-{$year}-".str_pad((string) $count, 3, '0', STR_PAD_LEFT);

            $subtotal = $itemsElegidos->sum('subtotal');
            $impuestos = $subtotal * 0.15;
            $total = $subtotal + $impuestos;

            $proveedorNombre = $cotizacion->proveedor->persona->personaJuridica->razon_social
                ?? $cotizacion->proveedor->persona->primer_nombre;

            // Al generar la orden, la solicitud pasa a estar oficialmente en proceso de compra
            $cotizacion->solicitud->update(['estado' => EstadoSolicitud::Aprobada]);

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
                // Intentar obtener la UM de la solicitud original
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
    }
}
