<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use Illuminate\Database\Eloquent\Collection;

class ListarCarritosDisponibles
{
    /**
     * Lista los carritos disponibles para asignar en una fecha dada (no asignados).
     *
     * @return Collection<int, Ubicacion>
     */
    public function execute(?string $fecha = null): Collection
    {
        $fecha = $fecha ?: now()->toDateString();

        $assignedCartIds = LimpiezaEjecucion::whereDate('fecha', $fecha)
            ->whereNotNull('carrito_id')
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->pluck('carrito_id')
            ->toArray();

        return Ubicacion::where('tipo', 'carrito')
            ->where('estado', 1)
            ->whereNotIn('id', $assignedCartIds)
            ->get();
    }
}
