<?php

namespace App\UseCases\Compras;

use App\Models\Compras\Solicitud;

class ObtenerSolicitudConItems
{
    public function execute(int $id): ?Solicitud
    {
        return Solicitud::with('items')->find($id);
    }
}
