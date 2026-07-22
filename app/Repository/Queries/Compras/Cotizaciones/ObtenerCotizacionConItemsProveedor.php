<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Cotizaciones;

use App\Repository\Models\Compras\Cotizacion;

class ObtenerCotizacionConItemsProveedor
{
    public function execute(int $id): ?Cotizacion
    {
        return Cotizacion::with(['items', 'proveedor'])->find($id);
    }
}
