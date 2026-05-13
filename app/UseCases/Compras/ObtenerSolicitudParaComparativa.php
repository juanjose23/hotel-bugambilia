<?php

namespace App\UseCases\Compras;

use App\Models\Compras\Solicitud;

class ObtenerSolicitudParaComparativa
{
    public function execute(int $id): ?Solicitud
    {
        return Solicitud::with([
            'items.producto',
            'cotizaciones.proveedor.persona.personaJuridica',
            'cotizaciones.proveedor.tipoProveedor',
            'cotizaciones.items',
        ])->find($id);
    }
}
