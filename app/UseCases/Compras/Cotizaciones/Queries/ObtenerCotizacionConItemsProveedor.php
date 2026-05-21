<?php

namespace App\UseCases\Compras\Cotizaciones\Queries;

use App\Models\Compras\Cotizacion;

class ObtenerCotizacionConItemsProveedor
{
    public function execute(int $id): ?Cotizacion
    {
        return Cotizacion::with(['items', 'proveedor'])->find($id);
    }
}
