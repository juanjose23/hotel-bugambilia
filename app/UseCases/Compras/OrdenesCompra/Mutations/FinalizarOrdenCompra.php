<?php

declare(strict_types=1);

namespace App\UseCases\Compras\OrdenesCompra\Mutations;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Compras\OrdenCompra;

class FinalizarOrdenCompra
{
    /**
     * Finaliza la orden de compra marcándola como Recibida.
     */
    public function execute(OrdenCompra $orden): void
    {
        $orden->update(['estado' => EstadoOrdenCompra::Recibida]);
    }
}
