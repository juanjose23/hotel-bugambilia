<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Stock;

use App\Interactors\Inventario\ConsumirStock;
use App\Repository\Models\Catalogos\ProductoVariante;

class RegistrarConsumoInsumoLimpieza
{
    public function __construct(
        private readonly ConsumirStock $consumirStock
    ) {}

    /**
     * Registra consumo de insumos de limpieza desde el carrito físico.
     */
    public function execute(int $carritoId, int $productoId, float $cantidad, ?int $productoVarianteId = null, ?int $ejecucionId = null, ?int $creadoPorId = null): void
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a consumir debe ser mayor a cero.');
        }

        if ($productoVarianteId !== null) {
            $variante = ProductoVariante::find($productoVarianteId);
            if ($variante) {
                $productoId = $variante->producto_id;
            }
        }

        $this->consumirStock->execute(
            productoId: $productoId,
            cantidadRequerida: $cantidad,
            ubicacionId: $carritoId,
            tipoMovimiento: 'CONSUMO_LIMPIEZA',
            productoVarianteId: $productoVarianteId,
            documentoId: $ejecucionId,
            documentoTipo: $ejecucionId ? 'limp_ejecuciones' : null,
            creadoPorId: $creadoPorId,
            referencia: $ejecucionId ? "Consumo de insumos en ejecución #{$ejecucionId}" : "Consumo manual de insumos desde carrito #{$carritoId}",
            notas: 'Consumo de insumos/herramientas durante la limpieza.'
        );
    }
}
