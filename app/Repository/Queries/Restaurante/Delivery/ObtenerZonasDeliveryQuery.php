<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Delivery;

use App\Repository\Models\Restaurante\ZonaDelivery;
use Illuminate\Support\Collection;

final class ObtenerZonasDeliveryQuery
{
    /**
     * @return Collection<int, ZonaDelivery>
     */
    public function ejecutar(bool $soloActivas = false): Collection
    {
        $query = ZonaDelivery::query()->orderBy('orden')->orderBy('nombre');

        if ($soloActivas) {
            $query->where('activo', true);
        }

        return $query->get();
    }
}
