<?php

declare(strict_types=1);

namespace App\Interactors\Compras\OrdenesCompra;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Events\Compras\OrdenEmitida;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Persistencia\Compras\OrdenCompraRepositorioInterface;

final class EmitirOrdenCompra
{
    public function __construct(
        private readonly OrdenCompraRepositorioInterface $ordenCompraRepositorio,
    ) {}

    public function ejecutar(OrdenCompra $orden): void
    {
        $this->ordenCompraRepositorio->actualizarEstado($orden, EstadoOrdenCompra::Emitida);

        OrdenEmitida::dispatch($orden);
    }
}
