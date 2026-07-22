<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Compras\RecepcionItem;
use App\Repository\Queries\Shared\ObtenerNombrePersona;

/**
 * Construye el array de opciones para el Select de RecepcionItem en ActivoForm.
 * Solo incluye ítems de productos tipo=3 (Activo Fijo) que aún no tienen un Activo asignado.
 */
class ObtenerOpcionesRecepcionItems
{
    /**
     * @return array<int, string>
     */
    public static function ejecutar(): array
    {
        $usados = Activo::whereNotNull('recepcion_item_id')
            ->pluck('recepcion_item_id');

        return RecepcionItem::query()
            ->whereHas('producto', fn ($q) => $q->where('tipo', 3))
            ->whereNotIn('id', $usados)
            ->with(['recepcion.ordenCompra.proveedor.persona.personaNatural',
                'recepcion.ordenCompra.proveedor.persona.personaJuridica',
                'producto'])
            ->get()
            ->mapWithKeys(function (RecepcionItem $item): array {
                $recepcion = $item->recepcion;
                $oc = $recepcion?->ordenCompra;
                $proveedor = $oc?->proveedor;

                $nombreProv = $proveedor?->persona
                    ? ObtenerNombrePersona::desde($proveedor->persona)
                    : 'Desconocido';

                $label = "Recepción: {$recepcion?->codigo} — "
                       ."Producto: {$item->producto?->nombre} "
                       ."(OC: {$oc?->codigo}, Proveedor: {$nombreProv})";

                return [$item->id => $label];
            })
            ->all();
    }
}
