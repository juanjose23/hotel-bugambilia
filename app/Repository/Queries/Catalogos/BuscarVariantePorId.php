<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\ProductoVariante;

final class BuscarVariantePorId
{
    public function ejecutar(int $id): ?ProductoVariante
    {
        return ProductoVariante::query()->find($id);
    }
}
