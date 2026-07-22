<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Producto;

final class BuscarProductoPorId
{
    public function ejecutar(int $id): ?Producto
    {
        return Producto::query()->find($id);
    }
}
