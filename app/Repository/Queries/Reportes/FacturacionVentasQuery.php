<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Repository\Models\Facturacion\Factura;
use Illuminate\Database\Eloquent\Collection;

final class FacturacionVentasQuery
{
    /**
     * @return Collection<int, Factura>
     */
    public function porRango(string $fechaInicio, string $fechaFin): Collection
    {
        return Factura::with(['cliente.persona'])
            ->whereDate('fecha_emision', '>=', $fechaInicio)
            ->whereDate('fecha_emision', '<=', $fechaFin)
            ->orderBy('fecha_emision', 'desc')
            ->get();
    }
}
