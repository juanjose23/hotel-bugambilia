<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Repository\Models\Catalogos\Ubicacion;

final class ObtenerCarritoPorId
{
    public function execute(?int $carritoId): ?Ubicacion
    {
        if ($carritoId === null) {
            return null;
        }

        return Ubicacion::query()->find($carritoId);
    }
}
