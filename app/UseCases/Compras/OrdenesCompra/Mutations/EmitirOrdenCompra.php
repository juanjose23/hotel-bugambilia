<?php

namespace App\UseCases\Compras\OrdenesCompra\Mutations;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Compras\OrdenCompra;
use App\Services\Compras\NotificadorCompras;

class EmitirOrdenCompra
{
    public function execute(OrdenCompra $orden): void
    {
        $orden->update(['estado' => EstadoOrdenCompra::Emitida]);

        app(NotificadorCompras::class)->ordenEmitida($orden);
    }
}
