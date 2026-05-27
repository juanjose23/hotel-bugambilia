<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Queries;

use App\Models\Compras\RecepcionItem;

/**
 * Dado un recepcion_item_id, devuelve los campos que deben rellenarse
 * automáticamente en el formulario de creación de un Activo Fijo.
 *
 * @return array{
 *   producto_id: int|null,
 *   producto_variante_id: int|null,
 *   proveedor_id: int|null,
 *   moneda_id: int|null,
 *   costo_adquisicion: float|null,
 * }
 */
class AutocompletarActivoDesdeRecepcion
{
    /**
     * @return array<string, int|float|null>
     */
    public static function ejecutar(int $recepcionItemId): array
    {
        $item = RecepcionItem::with([
            'recepcion.ordenCompra',
            'producto',
            'ordenItem',
        ])->find($recepcionItemId);

        if (! $item) {
            return [
                'producto_id' => null,
                'producto_variante_id' => null,
                'proveedor_id' => null,
                'moneda_id' => null,
                'costo_adquisicion' => null,
            ];
        }

        $oc = $item->recepcion?->ordenCompra;
        $costo = $item->ordenItem?->precio_unitario;

        return [
            'producto_id' => $item->producto_id,
            'producto_variante_id' => $item->producto_variante_id,
            'proveedor_id' => $oc?->proveedor_id,
            'moneda_id' => $oc?->moneda_id,
            'costo_adquisicion' => $costo !== null ? (float) $costo : null,
        ];
    }
}
