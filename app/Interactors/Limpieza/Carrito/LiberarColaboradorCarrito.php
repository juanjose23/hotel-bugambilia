<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

class LiberarColaboradorCarrito
{
    /**
     * Libera al colaborador de su carro asignado para una fecha (por defecto hoy).
     */
    public function execute(int $colaboradorId, ?string $fecha = null): bool
    {
        $fecha = $fecha ?: now()->toDateString();

        $ejecuciones = LimpiezaEjecucion::where('colaborador_id', $colaboradorId)
            ->whereDate('fecha', $fecha)
            ->whereNotNull('carrito_id')
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->get();

        if ($ejecuciones->isEmpty()) {
            return false;
        }

        foreach ($ejecuciones as $ejecucion) {
            $ejecucion->update([
                'carrito_id' => null,
            ]);
        }

        return true;
    }
}
