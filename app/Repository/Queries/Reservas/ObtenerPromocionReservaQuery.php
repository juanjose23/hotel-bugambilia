<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Repository\Models\Promociones\Promocion;
use InvalidArgumentException;

final class ObtenerPromocionReservaQuery
{
    public function vigente(int $id): Promocion
    {
        $promocion = Promocion::query()->vigentes()->where('web', true)->find($id);

        if (! $promocion instanceof Promocion) {
            throw new InvalidArgumentException('La promoción seleccionada ya no está disponible.');
        }

        return $promocion;
    }
}
