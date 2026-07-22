<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

class ObtenerCarritoAsignado
{
    /**
     * Obtiene el carrito físico asignado al colaborador en la fecha especificada (por defecto hoy).
     */
    public function execute(int $colaboradorId, ?string $fecha = null): ?Ubicacion
    {
        $fecha = $fecha ?: now()->toDateString();

        $ejecucion = LimpiezaEjecucion::with('carrito')
            ->where('colaborador_id', $colaboradorId)
            ->whereDate('fecha', $fecha)
            ->whereNotNull('carrito_id')
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->first();

        return $ejecucion?->carrito;
    }
}
