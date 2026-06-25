<?php

namespace App\Observers\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\RecepcionCompra;
use App\Services\Compras\NotificadorCompras;
use App\UseCases\Compras\OrdenesCompra\Queries\VerificarEstadoOrdenCompra;

class RecepcionObserver
{
    public function created(RecepcionCompra $recepcion): void
    {
        app(NotificadorCompras::class)->recepcionCreada($recepcion);

        $this->verificarOrden($recepcion);
    }

    public function updated(RecepcionCompra $recepcion): void
    {
        if (! $recepcion->isDirty('estado')) {
            return;
        }

        $this->verificarOrden($recepcion);
    }

    private function verificarOrden(RecepcionCompra $recepcion): void
    {
        match ($recepcion->estado) {
            EstadoRecepcion::Completa,
            EstadoRecepcion::Parcial,
            EstadoRecepcion::ConDiscrepancia,
            EstadoRecepcion::EnCuarentena => $recepcion->ordenCompra
                ? app(VerificarEstadoOrdenCompra::class)->execute($recepcion->ordenCompra)
                : null,
            EstadoRecepcion::Rechazada => $recepcion->ordenCompra?->update([
                'estado' => EstadoOrdenCompra::Emitida,
            ]),
            default => null,
        };
    }
}
