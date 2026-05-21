<?php

namespace App\UseCases\Compras\Solicitudes\Queries;

use App\Enums\Compras\EstadoSolicitud;
use App\Models\Compras\Solicitud;
use Illuminate\Database\Eloquent\Collection;

class ObtenerSolicitudesParaComparar
{
    /** @return Collection<int, Solicitud> */
    public function execute(): Collection
    {
        return Solicitud::withCount('cotizaciones')
            ->where('estado', EstadoSolicitud::Aprobada->value)
            ->whereDoesntHave('cotizaciones', function ($q) {
                $q->where('es_elegida', true)
                    ->orWhereHas('items', fn ($iq) => $iq->where('es_elegido', true));
            })
            ->whereDoesntHave('ordenesCompra')
            ->limit(50)
            ->get();
    }
}
