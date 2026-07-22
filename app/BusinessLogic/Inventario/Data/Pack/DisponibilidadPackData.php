<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Pack;

use Illuminate\Support\Collection;

final readonly class DisponibilidadPackData
{
    /**
     * @param  Collection<int, ItemDisponibilidadData>  $items
     */
    public function __construct(
        public bool $disponible,
        public Collection $items,
    ) {}
}
