<?php

declare(strict_types=1);

namespace App\Interactors\Compras\OrdenesCompra;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Persistencia\Compras\OrdenCompraRepositorioInterface;

final readonly class FinalizarOrdenCompra
{
    public function __construct(
        private OrdenCompraRepositorioInterface $ordenCompraRepositorio,
    ) {}

    public function ejecutar(OrdenCompra $orden): void
    {
        $this->ordenCompraRepositorio->actualizarEstado($orden, EstadoOrdenCompra::Recibida);
    }
}
