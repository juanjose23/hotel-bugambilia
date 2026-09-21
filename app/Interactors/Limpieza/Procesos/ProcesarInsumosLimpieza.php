<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\Interactors\Limpieza\Stock\RegistrarConsumoInsumoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Persistencia\Catalogos\ProductoRepositorioInterface;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use RuntimeException;

final readonly class ProcesarInsumosLimpieza
{
    public function __construct(
        private RegistrarConsumoInsumoLimpieza $registrarConsumoInsumoLimpieza,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
        private ProductoRepositorioInterface $productoRepositorio,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(LimpiezaEjecucion $ejecucion, array $data, int $carritoId, ?int $usuarioId): void
    {
        $this->ejecutar($ejecucion, $data, $carritoId, $usuarioId);
    }

    /** @param array<string, mixed> $data */
    public function ejecutar(LimpiezaEjecucion $ejecucion, array $data, int $carritoId, ?int $usuarioId): void
    {
        /** @var array<int|string, int|float|string> $insumosConsumo */
        $insumosConsumo = $data['insumos_consumo'] ?? [];
        foreach ($insumosConsumo as $varianteId => $qty) {
            $qty = (float) $qty;
            if ($qty <= 0) {
                continue;
            }

            $variante = $this->productoRepositorio->buscarVariantePorId((int) $varianteId);
            if (! $variante) {
                continue;
            }

            $available = $this->limpiezaRepositorio->obtenerStockDisponibleEnCarrito($carritoId, (int) $varianteId);

            if ($available < $qty) {
                throw new RuntimeException(sprintf(
                    'El carrito no cuenta con stock suficiente del insumo de limpieza "%s". Requerido: %f, Disponible: %f',
                    $variante->producto->nombre ?? 'Insumo',
                    $qty,
                    $available
                ));
            }

            $this->registrarConsumoInsumoLimpieza->execute(
                carritoId: $carritoId,
                productoId: (int) $variante->producto_id,
                cantidad: $qty,
                productoVarianteId: (int) $varianteId,
                ejecucionId: (int) $ejecucion->id,
                creadoPorId: $usuarioId
            );
        }
    }
}
