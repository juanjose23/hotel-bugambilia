<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\Data\ReabastecerItemData;
use App\BusinessLogic\Limpieza\Data\ReabastecerUbicacionData;
use App\Interactors\Limpieza\Stock\ReabastecerUbicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;

final readonly class ProcesarAdicionalesLimpieza
{
    public function __construct(
        private ReabastecerUbicacion $reabastecerUbicacion,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(LimpiezaEjecucion $ejecucion, array $data, ?int $carritoId, string $tipoDestino, ?int $usuarioId): void
    {
        $this->ejecutar($ejecucion, $data, $carritoId, $tipoDestino, $usuarioId);
    }

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

            $this->limpiezaRepositorio->crearSharedStockSiNoExiste(
                stockableType: (string) $ejecucion->limpiable_type,
                stockableId: (int) $ejecucion->limpiable_id,
                varianteId: $varianteId,
            );

            if ($carritoId) {
                $available = $this->limpiezaRepositorio->obtenerStockDisponibleEnCarrito($carritoId, $varianteId);

                $aReponer = min($qty, $available);
                if ($aReponer > 0) {
                    $this->reabastecerUbicacion->execute(new ReabastecerUbicacionData(
                        tipoDestino: $tipoDestino,
                        destinoId: (int) $ejecucion->limpiable_id,
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
