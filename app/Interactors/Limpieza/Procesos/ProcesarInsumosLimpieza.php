<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\Interactors\Limpieza\Stock\RegistrarConsumoInsumoLimpieza;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

class ProcesarInsumosLimpieza
{
    public function __construct(
        private readonly RegistrarConsumoInsumoLimpieza $registrarConsumoInsumoLimpieza,
    ) {}

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

            $variante = ProductoVariante::findOrFail((int) $varianteId);

            $available = (float) InventarioStock::where('ubicacion_id', $carritoId)
                ->where('producto_variante_id', $varianteId)
                ->sum('cantidad');

            if ($available < $qty) {
                throw new \RuntimeException(sprintf(
                    'El carrito no cuenta con stock suficiente del insumo de limpieza "%s". Requerido: %f, Disponible: %f',
                    $variante->producto->nombre ?? 'Insumo',
                    $qty,
                    $available
                ));
            }

            $this->registrarConsumoInsumoLimpieza->execute(
                carritoId: $carritoId,
                productoId: $variante->producto_id,
                cantidad: $qty,
                productoVarianteId: (int) $varianteId,
                ejecucionId: $ejecucion->id,
                creadoPorId: $usuarioId
            );
        }
    }
}
