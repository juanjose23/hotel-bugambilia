<?php

namespace App\UseCases\Compras\OrdenesCompra\Queries;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\RecepcionItem;

class VerificarEstadoOrdenCompra
{
    public function execute(OrdenCompra $orden): void
    {
        $totalOrdenado = (float) $orden->items()->sum('cantidad');

        $totalRecibido = (float) RecepcionItem::query()
            ->whereHas('recepcion', fn ($q) => $q
                ->where('orden_compra_id', $orden->id)
                ->whereIn('estado', [
                    EstadoRecepcion::Completa,
                    EstadoRecepcion::Parcial,
                    EstadoRecepcion::ConDiscrepancia,
                    EstadoRecepcion::EnCuarentena,
                ])
            )
            ->sum('cantidad_recibida');

        if ($totalOrdenado > 0) {
            if ($totalRecibido >= $totalOrdenado) {
                $orden->update(['estado' => EstadoOrdenCompra::Recibida]);
            } elseif ($totalRecibido > 0.0) {
                $orden->update(['estado' => EstadoOrdenCompra::Parcial]);
            }
        }
    }
}
