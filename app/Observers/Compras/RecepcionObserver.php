<?php

namespace App\Observers\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Compras\RecepcionCompra;
use App\Services\Compras\NotificadorCompras;

class RecepcionObserver
{
    public function created(RecepcionCompra $recepcion): void
    {
        if ($recepcion->ordenCompra) {
            $recepcion->ordenCompra->update([
                'estado' => EstadoOrdenCompra::Recibida,
            ]);
        }

        app(NotificadorCompras::class)->recepcionCreada($recepcion);
    }
}
