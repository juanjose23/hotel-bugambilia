<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Repository\Models\Inventario\Lote;

final class ObtenerOpcionesLotesLavanderia
{
    public function __construct(
        private ObtenerStockDisponiblePorOrigen $stockPorOrigen,
    ) {}

    /**
     * @return array<int, string>
     */
    public function execute(?int $productoVarianteId, ?string $tipoOrigen = null, ?int $origenId = null): array
    {
        if ($productoVarianteId === null || $productoVarianteId <= 0) {
            return [];
        }

        if ($tipoOrigen !== null && $origenId !== null && $origenId > 0) {
            $itemsStock = $this->stockPorOrigen->execute($tipoOrigen, $origenId);
            $lotes = [];

            foreach ($itemsStock as $item) {
                if ($item['producto_variante_id'] === $productoVarianteId) {
                    $label = "[{$item['codigo_lote']}] · Disp en Origen: {$item['cantidad_disponible']}";
                    $lotes[$item['lote_id']] = $label;
                }
            }

            if (! empty($lotes)) {
                return $lotes;
            }
        }

        /** @var array<int, string> $lotes */
        $lotes = Lote::query()
            ->where('producto_variante_id', $productoVarianteId)
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function (Lote $lote): array {
                $vencimiento = $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : 'N/A';
                $label = "[{$lote->codigo_lote}] · Disp: {$lote->cantidad_disponible} · Vence: {$vencimiento}";

                return [(int) $lote->id => $label];
            })
            ->toArray();

        return $lotes;
    }
}
