<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\Data\ReabastecerItemData;
use App\BusinessLogic\Limpieza\Data\ReabastecerUbicacionData;
use App\Interactors\Limpieza\Stock\ReabastecerUbicacion;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use RuntimeException;

final readonly class ProcesarReposicionConsumos
{
    public function __construct(
        private ReabastecerUbicacion $reabastecerUbicacion,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    /** @param array<int|string, int|float|string> $consumosReponer */
    public function procesar(array $consumosReponer, ?int $carritoId, string $tipoDestino, int $destinoId, ?int $usuarioId, int $ejecucionId): void
    {
        foreach ($consumosReponer as $stockId => $qty) {
            $qty = (float) $qty;
            if ($qty <= 0) {
                continue;
            }

            $sharedStock = $this->limpiezaRepositorio->descontarSharedStockConLock((int) $stockId, 0);
            if (! $sharedStock) {
                throw new RuntimeException("Stock shared #{$stockId} no encontrado.");
            }

            $varianteId = (int) $sharedStock->producto_variante_id;

            if ($carritoId) {
                $available = $this->limpiezaRepositorio->obtenerStockDisponibleEnCarrito($carritoId, $varianteId);

                $aReponer = min($qty, $available);
                if ($aReponer > 0) {
                    $this->reabastecerUbicacion->execute(new ReabastecerUbicacionData(
                        tipoDestino: $tipoDestino,
                        destinoId: $destinoId,
                        items: [ReabastecerItemData::fromArray(['producto_variante_id' => $varianteId, 'cantidad' => $aReponer])],
                        bodegaOrigenId: $carritoId,
                        creadoPorId: $usuarioId,
                        notas: "Reposición de amenity en ejecución #{$ejecucionId}"
                    ));
                }
            }
        }
    }
}
