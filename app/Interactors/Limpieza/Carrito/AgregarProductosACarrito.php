<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Repository\Persistencia\Catalogos\ProductoRepositorioInterface;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class AgregarProductosACarrito
{
    public function __construct(
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
        private ProductoRepositorioInterface $productoRepositorio,
    ) {}

    /**
     * Agrega productos/suministros directamente a un carro.
     */
    public function execute(int $carritoId, int $productoId, float $cantidad, ?int $productoVarianteId = null, ?int $loteId = null): void
    {
        $this->ejecutar($carritoId, $productoId, $cantidad, $productoVarianteId, $loteId);
    }

    /**
     * Agrega productos/suministros directamente a un carro.
     */
    public function ejecutar(int $carritoId, int $productoId, float $cantidad, ?int $productoVarianteId = null, ?int $loteId = null): void
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a agregar debe ser mayor a cero.');
        }

        if ($productoVarianteId !== null) {
            $variante = $this->productoRepositorio->buscarVariantePorId($productoVarianteId);
            if ($variante) {
                $productoId = (int) $variante->producto_id;
            }
        }

        DB::transaction(function () use ($carritoId, $productoId, $productoVarianteId, $cantidad, $loteId): void {
            $this->limpiezaRepositorio->agregarStockACarrito(
                $carritoId,
                $productoId,
                $cantidad,
                $productoVarianteId,
                $loteId,
            );
        });
    }
}
