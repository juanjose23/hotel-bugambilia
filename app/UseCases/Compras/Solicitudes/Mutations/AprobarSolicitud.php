<?php

namespace App\UseCases\Compras\Solicitudes\Mutations;

use App\Enums\Compras\EstadoSolicitud;
use App\Models\Compras\Solicitud;
use App\Services\Compras\NotificadorCompras;

class AprobarSolicitud
{
    /**
     * @param  array<int, array{id: int, cantidad_aprobada: float}>  $itemsAprobados
     */
    public function execute(Solicitud $solicitud, array $itemsAprobados = []): void
    {
        foreach ($itemsAprobados as $itemData) {
            $solicitud->items()
                ->where('id', $itemData['id'])
                ->update(['cantidad_aprobada' => (float) $itemData['cantidad_aprobada']]);
        }

        $solicitud->update(['estado' => EstadoSolicitud::Aprobada]);

        app(NotificadorCompras::class)->solicitudAprobada($solicitud);
    }
}
