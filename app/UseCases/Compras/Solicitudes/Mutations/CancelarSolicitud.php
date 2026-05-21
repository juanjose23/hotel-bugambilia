<?php

namespace App\UseCases\Compras\Solicitudes\Mutations;

use App\Enums\Compras\EstadoSolicitud;
use App\Models\Compras\Solicitud;

class CancelarSolicitud
{
    public function execute(Solicitud $solicitud): void
    {
        $solicitud->update(['estado' => EstadoSolicitud::Cancelada]);
    }
}
