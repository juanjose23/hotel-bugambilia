<?php

namespace App\UseCases\Compras\OrdenesCompra\Mutations;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\RecepcionCompra;
use App\Services\Compras\NotificadorCompras;

class CancelarOrdenCompra
{
    public function execute(OrdenCompra $orden): void
    {
        if (RecepcionCompra::where('orden_compra_id', $orden->id)->exists()) {
            throw new \DomainException('No se puede cancelar una orden que ya tiene recepciones registradas.');
        }

        $orden->update(['estado' => EstadoOrdenCompra::Cancelada]);

        app(NotificadorCompras::class)->ordenCancelada($orden);
    }
}
