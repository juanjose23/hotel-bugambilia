<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Cotizaciones;

use App\Repository\Models\Compras\Cotizacion;
use Illuminate\Database\Eloquent\Collection;

class ObtenerCotizacionesPorSolicitud
{
    /** @return Collection<int, Cotizacion> */
    public function execute(?int $solicitudId): Collection
    {
        return Cotizacion::query()
            ->when($solicitudId, fn ($q) => $q->where('solicitud_id', $solicitudId))
            ->with('proveedor')
            ->get();
    }
}
