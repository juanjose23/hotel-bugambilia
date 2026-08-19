<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Repository\Models\Compras\Solicitud;

final class ObtenerSolicitudParaReporteQuery
{
    public function ejecutar(int $solicitudId): ?Solicitud
    {
        return Solicitud::with([
            'items.producto',
            'cotizaciones.proveedor',
            'cotizaciones.items.solicitudItem.producto',
            'ordenesCompra.proveedor',
            'ordenesCompra.items',
            'ordenesCompra.recepciones.items',
            'ordenesCompra.recepciones.creador.persona',
        ])->find($solicitudId);
    }
}
