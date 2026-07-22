<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Repository\Models\Compras\RecepcionItem;

final readonly class AutocompletarActivoDesdeRecepcion
{
    /** @return array<string, float|int|null> */
    public function ejecutar(int $recepcionItemId): array
    {
        $item = RecepcionItem::query()
            ->with([
                'recepcion.ordenCompra',
                'ordenItem',
            ])
            ->find($recepcionItemId);

        if ($item === null) {
            return $this->datosVacios();
        }

        $ordenCompra = $item->recepcion?->ordenCompra;

        return [
            'producto_id' => $item->producto_id,
            'producto_variante_id' => $item->producto_variante_id,
            'proveedor_id' => $ordenCompra?->proveedor_id,
            'moneda_id' => $ordenCompra?->moneda_id,
            'costo_adquisicion' => $this->obtenerCosto($item),
        ];
    }

    /** @return array<string, null> */
    private function datosVacios(): array
    {
        return [
            'producto_id' => null,
            'producto_variante_id' => null,
            'proveedor_id' => null,
            'moneda_id' => null,
            'costo_adquisicion' => null,
        ];
    }

    private function obtenerCosto(RecepcionItem $item): ?float
    {
        return $item->ordenItem?->precio_unitario !== null
            ? (float) $item->ordenItem->precio_unitario
            : null;
    }
}
