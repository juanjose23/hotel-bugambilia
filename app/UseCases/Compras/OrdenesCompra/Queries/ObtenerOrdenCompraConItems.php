<?php

namespace App\UseCases\Compras\OrdenesCompra\Queries;

use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;

class ObtenerOrdenCompraConItems
{
    public function execute(int $id): ?OrdenCompra
    {
        $orden = OrdenCompra::with([
            'items' => function ($query) {
                $query->withSum(['recepcionItems' => function ($q) {
                    $q->whereHas('recepcion', fn ($r) => $r->whereIn('estado', [
                        EstadoRecepcion::Completa,
                        EstadoRecepcion::Parcial,
                        EstadoRecepcion::ConDiscrepancia,
                        EstadoRecepcion::EnCuarentena,
                    ]));
                }], 'cantidad_recibida');
            },
            'items.producto',
            'items.variante',
        ])->find($id);

        if (! $orden) {
            return null;
        }

        $orden->items->each(function (OrdenCompraItem $item) {
            $receivedQty = (float) ($item->recepcion_items_sum_cantidad_recibida ?? 0);

            $item->setAttribute('cantidad_pendiente', max(0, (float) $item->cantidad - $receivedQty));
        });

        return $orden;
    }

    public function getItemPendingQuantity(int $ordenItemId): float
    {
        $item = OrdenCompraItem::withSum(['recepcionItems' => function ($q) {
            $q->whereHas('recepcion', fn ($r) => $r->whereIn('estado', [
                EstadoRecepcion::Completa,
                EstadoRecepcion::Parcial,
                EstadoRecepcion::ConDiscrepancia,
                EstadoRecepcion::EnCuarentena,
            ]));
        }], 'cantidad_recibida')->findOrFail($ordenItemId);
        $receivedQty = (float) ($item->recepcion_items_sum_cantidad_recibida ?? 0);

        return max(0, (float) $item->cantidad - $receivedQty);
    }

    /** @return array<int, string> */
    public function getItemOptions(int $ordenId): array
    {
        /** @var array<int, string> $result */
        $result = OrdenCompra::findOrFail($ordenId)
            ->items()
            ->with(['producto', 'variante'])
            ->withSum(['recepcionItems' => function ($q) {
                $q->whereHas('recepcion', fn ($r) => $r->whereIn('estado', [
                    EstadoRecepcion::Completa,
                    EstadoRecepcion::Parcial,
                    EstadoRecepcion::ConDiscrepancia,
                    EstadoRecepcion::EnCuarentena,
                ]));
            }], 'cantidad_recibida')
            ->get()
            ->mapWithKeys(function ($item) {
                $receivedQty = floatval($item->recepcion_items_sum_cantidad_recibida ?? 0);

                $pending = max(0, floatval($item->cantidad) - $receivedQty);

                if ($pending <= 0) {
                    return [];
                }

                $label = $item->producto !== null ? $item->producto->nombre : 'Producto #'.$item->producto_id;
                if ($item->variante) {
                    $label .= " ({$item->variante->codigo})";
                }
                $label .= " | Ord: {$item->cantidad} | Pend: {$pending}";

                return [$item->id => $label];
            })
            ->toArray();

        return $result;
    }
}
