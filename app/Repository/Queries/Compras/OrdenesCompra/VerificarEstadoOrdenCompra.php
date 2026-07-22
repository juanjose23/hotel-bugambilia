<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\OrdenesCompra;

use App\Enums\Compras\EstadoRecepcion;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionItem;

final class VerificarEstadoOrdenCompra
{
    /** @return array{total_ordenado: float, total_recibido: float} */
    public function execute(OrdenCompra $orden): array
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

        return [
            'total_ordenado' => $totalOrdenado,
            'total_recibido' => $totalRecibido,
        ];
    }
}
