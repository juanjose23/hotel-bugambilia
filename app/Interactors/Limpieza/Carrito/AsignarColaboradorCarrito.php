<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

class AsignarColaboradorCarrito
{
    /**
     * Asigna un colaborador a un carro físico para una fecha (por defecto hoy).
     */
    public function execute(int $colaboradorId, int $carritoId, ?string $fecha = null): LimpiezaEjecucion
    {
        $fecha = $fecha ?: now()->toDateString();

        // 1. Validar que la ubicación sea de tipo 'carrito'
        $carrito = Ubicacion::findOrFail($carritoId);
        if ($carrito->tipo !== 'carrito') {
            throw new \InvalidArgumentException("La ubicación con ID {$carritoId} no es de tipo 'carrito'.");
        }

        // 2. Validar que el carro no esté ya ocupado hoy
        $existingCartAsg = LimpiezaEjecucion::where('carrito_id', $carritoId)
            ->whereDate('fecha', $fecha)
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->first();
        if ($existingCartAsg) {
            throw new \RuntimeException("El carrito seleccionado ya está asignado en otra limpieza activa para la fecha {$fecha}.");
        }

        // 3. Buscar ejecución del colaborador hoy
        $ejecucion = LimpiezaEjecucion::where('colaborador_id', $colaboradorId)
            ->whereDate('fecha', $fecha)
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->first();

        if (! $ejecucion) {
            throw new \RuntimeException('No se encontró una tarea de limpieza activa o pendiente para el colaborador hoy.');
        }

        $ejecucion->update([
            'carrito_id' => $carritoId,
        ]);

        return $ejecucion;
    }
}
