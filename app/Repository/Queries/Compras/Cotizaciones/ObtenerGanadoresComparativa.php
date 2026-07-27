<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Cotizaciones;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\Solicitud;
use Illuminate\Support\Collection;

final class ObtenerGanadoresComparativa
{
    /**
     * @return Collection<int, array{producto: string, variante: string, proveedor: string, cantidad_solicitada: float, precio_unitario: float, subtotal: float, cotizacion_id: int, orden_generada: bool}>
     */
    public function ejecutar(Solicitud $solicitud): Collection
    {
        $solicitud->loadMissing([
            'items.producto',
            'items.variante',
            'cotizaciones.proveedor.persona.personaJuridica',
            'cotizaciones.items',
        ]);

        $winners = collect();

        foreach ($solicitud->items as $sItem) {
            foreach ($solicitud->cotizaciones as $cot) {
                $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();

                if ($cItem?->es_elegido) {
                    $ordenGenerada = OrdenCompra::where('cotizacion_id', $cot->id)
                        ->whereHas('items', fn ($q) => $q->where('producto_id', $sItem->producto_id))
                        ->where('estado', '!=', EstadoOrdenCompra::Cancelada)
                        ->exists();

                    $cantidadAprobada = (float) $sItem->cantidad_aprobada;
                    $cantidadSolicitada = (float) $sItem->cantidad_solicitada;

                    $winners->push([
                        'producto' => $sItem->producto->nombre ?? '',
                        'variante' => $sItem->variante->nombre_variante ?? 'Estándar',
                        'proveedor' => $this->getProveedorNombre($cot),
                        'cantidad_solicitada' => $cantidadAprobada > 0 ? $cantidadAprobada : $cantidadSolicitada,
                        'precio_unitario' => $cItem->precio_unitario,
                        'subtotal' => $cItem->subtotal,
                        'cotizacion_id' => $cot->id,
                        'orden_generada' => $ordenGenerada,
                    ]);
                }
            }
        }

        return $winners;
    }

    private function getProveedorNombre(Cotizacion $cot): string
    {
        $proveedor = $cot->proveedor;
        if ($proveedor === null) {
            return "Proveedor #{$cot->proveedor_id}";
        }

        $persona = $proveedor->persona;
        if ($persona === null) {
            return "Proveedor #{$cot->proveedor_id}";
        }

        return ($persona->personaJuridica ? $persona->personaJuridica->razon_social : null)
            ?? $persona->primer_nombre
            ?? "Proveedor #{$cot->proveedor_id}";
    }
}
