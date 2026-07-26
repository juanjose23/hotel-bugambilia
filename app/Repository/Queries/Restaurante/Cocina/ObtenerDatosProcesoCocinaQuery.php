<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Restaurante\Plato;

final class ObtenerDatosProcesoCocinaQuery
{
    /**
     * @return array<int|string, string>
     */
    public function platosConReceta(): array
    {
        /** @var array<int|string, string> */
        return Plato::activos()
            ->whereNotNull('producto_receta_id')
            ->pluck('nombre', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function productosDisponibles(): array
    {
        /** @var array<int|string, string> */
        return Producto::pluck('nombre', 'id')
            ->all();
    }
}
