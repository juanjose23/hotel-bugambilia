<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Cotizaciones;

use App\Repository\Models\Compras\Solicitud;

final class ObtenerCotizacionesParaComparativa
{
    public function ejecutar(int $solicitudId): ?Solicitud
    {
        return Solicitud::with([
            'items.producto',
            'items.variante',
            'cotizaciones.proveedor.persona.personaJuridica',
            'cotizaciones.proveedor.persona.personaNatural',
            'cotizaciones.proveedor.tipoProveedor',
            'cotizaciones.items.producto',
            'cotizaciones.items.variante',
            'cotizaciones.moneda',
            'colaborador.persona',
            'departamentoSolicitante',
        ])->find($solicitudId);
    }
}
