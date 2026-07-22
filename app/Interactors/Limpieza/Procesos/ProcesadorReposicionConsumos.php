<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\Data\ReabastecerItemData;
use App\BusinessLogic\Limpieza\Data\ReabastecerUbicacionData;
use App\Interactors\Limpieza\Stock\ReabastecerUbicacion;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Shared\Stock as SharedStock;

final class ProcesadorReposicionConsumos
{
    public function __construct(
        private readonly ReabastecerUbicacion $reabastecerUbicacion,
    ) {}

    /** @param array<int|string, int|float|string> $consumosReponer */
    public function procesar(array $consumosReponer, ?int $carritoId, string $tipoDestino, int $destinoId, ?int $usuarioId, int $ejecucionId): void
    {
        foreach ($consumosReponer as $stockId => $qty) {
            $qty = (float) $qty;
            if ($qty <= 0) {
                continue;
            }

            $sharedStock = SharedStock::where('id', $stockId)->lockForUpdate()->firstOrFail();
            $varianteId = $sharedStock->producto_variante_id;

            if ($carritoId) {
                $available = (float) InventarioStock::where('ubicacion_id', $carritoId)
                    ->where('producto_variante_id', $varianteId)
                    ->sum('cantidad');

                $aReponer = min($qty, $available);
                if ($aReponer > 0) {
                    $this->reabastecerUbicacion->execute(new ReabastecerUbicacionData(
                        tipoDestino: $tipoDestino,
                        destinoId: $destinoId,
                        items: [ReabastecerItemData::fromArray(['producto_variante_id' => $varianteId, 'cantidad' => $aReponer])],
                        bodegaOrigenId: (int) $carritoId,
                        creadoPorId: $usuarioId,
                        notas: "Reposición de amenity en ejecución #{$ejecucionId}"
                    ));
                }
            }
        }
    }
}
