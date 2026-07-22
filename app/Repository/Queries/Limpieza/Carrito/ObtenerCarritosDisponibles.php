<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

class ObtenerCarritosDisponibles
{
    /**
     * @return array<int, string>
     */
    public function execute(int $ejecucionId): array
    {
        $turno = LimpiezaEjecucion::find($ejecucionId)?->turno;

        if (! $turno) {
            return [];
        }

        $carritosIds = $turno->carritos()->pluck('ubicaciones.id')->toArray();

        if ($carritosIds === []) {
            return [];
        }

        $busyCarritos = LimpiezaEjecucion::where('estado', EstadoLimpieza::EnProgreso)
            ->whereNotNull('carrito_id')
            ->pluck('carrito_id')
            ->toArray();

        /** @var array<int, string> */
        return Ubicacion::whereIn('id', $carritosIds)
            ->whereNotIn('id', $busyCarritos)
            ->pluck('nombre', 'id')
            ->toArray();
    }
}
