<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Stock;

use App\Interactors\Inventario\ConsumirStock;
use App\Repository\Persistencia\Catalogos\ProductoRepositorioInterface;
use InvalidArgumentException;

final readonly class RegistrarConsumoInsumoLimpieza
{
    public function __construct(
        private ConsumirStock $consumirStock,
        private ProductoRepositorioInterface $productoRepositorio,
    ) {}

    /**
     * Registra consumo de insumos de limpieza desde el carrito físico.
     */
    public function execute(int $carritoId, int $productoId, float $cantidad, ?int $productoVarianteId = null, ?int $ejecucionId = null, ?int $creadoPorId = null): void
    {
        $this->ejecutar($carritoId, $productoId, $cantidad, $productoVarianteId, $ejecucionId, $creadoPorId);
    }

    /**
     * Registra consumo de insumos de limpieza desde el carrito físico.
     */
    public function ejecutar(int $carritoId, int $productoId, float $cantidad, ?int $productoVarianteId = null, ?int $ejecucionId = null, ?int $creadoPorId = null): void
    {
        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad a consumir debe ser mayor a cero.');
        }

        if ($productoVarianteId !== null) {
            $variante = $this->productoRepositorio->buscarVariantePorId($productoVarianteId);
            if ($variante) {
                $productoId = (int) $variante->producto_id;
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
