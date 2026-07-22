<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Stock;

use App\Repository\Models\Inventario\Stock;

class ObtenerOpcionesStockPorUbicacion
{
    /**
     * @return array<int, string>
     */
    public function execute(int $ubicacionId, ?int $excluirStockId = null): array
    {
        $query = Stock::with(['variante.producto', 'lote'])
            ->where('ubicacion_id', $ubicacionId)
            ->where('cantidad', '>', 0);

        if ($excluirStockId !== null) {
            $query->where('id', '!=', $excluirStockId);
        }

        /** @var array<int, string> */
        return $query->get()
            ->mapWithKeys(function (Stock $stock) {
                $nombre = ($stock->variante->producto->nombre ?? 'Insumo');

                if ($stock->variante?->codigo) {
                    $nombre .= " [SKU: {$stock->variante->codigo}]";
                }

                if ($stock->variante?->nombre_variante) {
                    $nombre .= " ({$stock->variante->nombre_variante})";
                }

                $nombre .= ' · Lote: '.($stock->lote->codigo_lote ?? 'N/A');
                $nombre .= " · Disp: {$stock->cantidad}";

                return [$stock->id => $nombre];
            })
            ->toArray();
    }
}
