<?php

namespace App\UseCases\Compras\Solicitudes\Queries;

use App\Models\Compras\Solicitud;

class ObtenerSolicitudConItems
{
    public function execute(int $id): ?Solicitud
    {
        return Solicitud::with('items')->find($id);
    }
}
