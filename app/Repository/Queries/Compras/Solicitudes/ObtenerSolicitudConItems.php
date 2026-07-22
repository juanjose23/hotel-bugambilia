<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Solicitudes;

use App\Repository\Models\Compras\Solicitud;

class ObtenerSolicitudConItems
{
    public function execute(int $id): ?Solicitud
    {
        return Solicitud::with('items')->find($id);
    }
}
