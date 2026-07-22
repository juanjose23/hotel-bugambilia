<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\Data\ReabastecerItemData;
use App\BusinessLogic\Limpieza\Data\ReabastecerUbicacionData;
use App\Interactors\Limpieza\Stock\ReabastecerUbicacion;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Shared\Stock as SharedStock;

class ProcesarAdicionalesLimpieza
{
    public function __construct(
        private readonly ReabastecerUbicacion $reabastecerUbicacion,
    ) {}

    /** @param array<string, mixed> $data */
    public function ejecutar(LimpiezaEjecucion $ejecucion, array $data, ?int $carritoId, string $tipoDestino, ?int $usuarioId): void
    {
        /** @var list<array{ producto_variante_id: int|string, cantidad: int|float|string }> $adicionales */
        $adicionales = $data['adicionales'] ?? [];
        foreach ($adicionales as $item) {
            $varianteId = (int) $item['producto_variante_id'];
            $qty = (float) $item['cantidad'];

            if (! $varianteId || $qty <= 0) {
                continue;
            }

            SharedStock::firstOrCreate([
                'stockable_type' => $ejecucion->limpiable_type,
                'stockable_id' => $ejecucion->limpiable_id,
                'producto_variante_id' => $varianteId,
            ], [
                'cantidad_ideal' => 0.0,
                'cantidad_actual' => 0.0,
            ]);

            if ($carritoId) {
                $available = (float) InventarioStock::where('ubicacion_id', $carritoId)
                    ->where('producto_variante_id', $varianteId)
                    ->sum('cantidad');

                $aReponer = min($qty, $available);
                if ($aReponer > 0) {
                    $this->reabastecerUbicacion->execute(new ReabastecerUbicacionData(
                        tipoDestino: $tipoDestino,
                        destinoId: $ejecucion->limpiable_id,
                        items: [ReabastecerItemData::fromArray(['producto_variante_id' => $varianteId, 'cantidad' => $aReponer])],
                        bodegaOrigenId: $carritoId,
                        creadoPorId: $usuarioId,
                        notas: "Reposición de producto adicional en ejecución #{$ejecucion->id}"
                    ));
                }
            }
        }
    }
}
