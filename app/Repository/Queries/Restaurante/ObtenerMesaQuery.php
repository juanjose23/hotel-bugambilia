<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante;

use App\Repository\Models\Espacios\Espacio;

final class ObtenerMesaQuery
{
    public function porId(int $mesaId): ?Espacio
    {
        return Espacio::query()->where('tipo', 'mesa')->find($mesaId);
    }
}
