<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\Data\ReabastecerItemData;
use App\BusinessLogic\Limpieza\Data\ReabastecerUbicacionData;
use App\Interactors\Limpieza\Stock\ReabastecerUbicacion;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Shared\Stock as SharedStock;

final class ProcesadorReposicionBlancos
{
    public function __construct(
        private readonly ReabastecerUbicacion $reabastecerUbicacion,
    ) {}

    /**
     * @param  array<int|string, int|float|string>  $blancosReponer
     * @return list<array{variante_id: int|null, nombre: string, required: float, available: float}>
     */
    public function procesar(array $blancosReponer, ?int $carritoId, string $tipoDestino, int $destinoId, ?int $usuarioId, int $ejecucionId): array
    {
        /** @var list<array{variante_id: int|null, nombre: string, required: float, available: float}> $missingItems */
        $missingItems = [];

        foreach ($blancosReponer as $stockId => $qty) {
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
                        notas: "Reposición de blancos en ejecución #{$ejecucionId}"
                    ));
                }

                if ($aReponer < $qty) {
                    $nombre = $sharedStock->variante?->producto?->nombre.($sharedStock->variante?->nombre_variante ? " ({$sharedStock->variante->nombre_variante})" : '');
                    $missingItems[] = [
                        'variante_id' => $varianteId,
                        'nombre' => $nombre,
                        'required' => $qty,
                        'available' => $available,
                    ];
                }
            } else {
                $nombre = $sharedStock->variante?->producto?->nombre.($sharedStock->variante?->nombre_variante ? " ({$sharedStock->variante->nombre_variante})" : '');
                $missingItems[] = [
                    'variante_id' => $varianteId,
                    'nombre' => $nombre,
                    'required' => $qty,
                    'available' => 0.0,
                ];
            }
        }

        return $missingItems;
    }
}
