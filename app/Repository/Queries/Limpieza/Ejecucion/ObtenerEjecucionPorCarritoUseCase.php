<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ejecucion;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use Illuminate\Database\Eloquent\Builder;

class ObtenerEjecucionPorCarritoUseCase
{
    public function execute(int $carritoId): ?LimpiezaEjecucion
    {
        return $this->queryForCarrito($carritoId)->first();
    }

    /** @return Builder<LimpiezaEjecucion> */
    public function queryForCarrito(int $carritoId): Builder
    {
        return LimpiezaEjecucion::where('carrito_id', $carritoId)
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->with(['colaborador.persona.personaNatural']);
    }
}
