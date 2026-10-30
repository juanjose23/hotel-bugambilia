<?php

declare(strict_types=1);

namespace App\Repository\Queries\Espacios;

use App\Repository\Models\Espacios\Espacio;

final readonly class ObtenerJerarquiaEspaciosQuery
{
    /** @return array<int, int|null> */
    public function ejecutar(): array
    {
        $result = [];
        foreach (Espacio::query()->get(['id', 'padre_id']) as $espacio) {
            $result[$espacio->id] = $espacio->padre_id;
        }

        return $result;
    }
}
