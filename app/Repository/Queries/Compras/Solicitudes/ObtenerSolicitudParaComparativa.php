<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Solicitudes;

use App\Repository\Models\Compras\Solicitud;

final class ObtenerSolicitudParaComparativa
{
    public function ejecutar(int $id): ?Solicitud
    {
        return Solicitud::with([
            'items.producto',
            'cotizaciones.proveedor.persona.personaJuridica',
            'cotizaciones.proveedor.tipoProveedor',
            'cotizaciones.items',
        ])->find($id);
    }
}
