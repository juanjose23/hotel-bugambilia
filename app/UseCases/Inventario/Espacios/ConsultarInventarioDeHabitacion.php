<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Espacios;

use App\Models\Espacios\InventarioFijo;
use Illuminate\Database\Eloquent\Collection;

class ConsultarInventarioDeHabitacion
{
    /**
     * @return Collection<int, InventarioFijo>
     */
    public function execute(int $habitacionId): Collection
    {
        return InventarioFijo::with(['producto', 'variante'])
            ->where('espacio_tipo', 'habitacion')
            ->where('espacio_id', $habitacionId)
            ->get();
    }
}
