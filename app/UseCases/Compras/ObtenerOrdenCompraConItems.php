<?php

namespace App\UseCases\Compras;

use App\Models\Compras\OrdenCompra;

class ObtenerOrdenCompraConItems
{
    public function execute(int $id): ?OrdenCompra
    {
        return OrdenCompra::with('items')->find($id);
    }
}
