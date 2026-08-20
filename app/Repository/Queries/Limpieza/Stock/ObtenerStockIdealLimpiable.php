<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Stock;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Shared\Stock;
use Illuminate\Support\Collection;

final class ObtenerStockIdealLimpiable
{
    /**
     * @return Collection<int, Stock>
     */
    public function execute(string $limpiableType, int $limpiableId): Collection
    {
        $stockClass = match ($limpiableType) {
            Habitacion::class => Habitacion::class,
            Espacio::class => Espacio::class,
            default => null,
        };

        if ($stockClass === null) {
            return collect();
        }

        return Stock::query()
            ->with(['variante.producto'])
            ->where('stockable_type', $stockClass)
            ->where('stockable_id', $limpiableId)
            ->get();
    }
}
