<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Enums\Catalogos\TipoUbicacion;
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

        return Ubicacion::query()
            ->where(function ($q) {
                $q->where('tipo', TipoUbicacion::CARRITO->value)
                    ->orWhere('tipo', 'carrito')
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%carrito%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%carro%']);
            })
            ->where('estado', 1)
            ->whereNotIn('id', $assignedCartIds)
            ->get();
    }
}
