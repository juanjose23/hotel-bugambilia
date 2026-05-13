<?php

namespace App\Observers\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Models\Compras\RecepcionCompra;

class RecepcionObserver
{
    /**
     * Handle the RecepcionCompra "created" event.
     */
    public function created(RecepcionCompra $recepcion): void
    {
        // Al recibir la mercancía, la orden de compra cambia su estado automáticamente
        if ($recepcion->ordenCompra) {
            $recepcion->ordenCompra->update([
                'estado' => EstadoOrdenCompra::Recibida,
            ]);
        }
    }
}
