<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ejecucion;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final class ObtenerOpcionesEjecucionesPendientesCarrito
{
    /**
     * @return array<int, string>
     */
    public function execute(): array
    {
        $opciones = LimpiezaEjecucion::query()
            ->where('estado', EstadoLimpieza::Pendiente)
            ->whereNull('carrito_id')
            ->whereDate('fecha', now()->toDateString())
            ->with(['limpiable', 'colaborador.persona'])
            ->get()
            ->mapWithKeys(function (LimpiezaEjecucion $ejecucion): array {
                $area = $ejecucion->limpiable ? (string) ($ejecucion->limpiable->nombre ?? $ejecucion->limpiable_type) : 'Área';
                $colaborador = $ejecucion->colaborador?->persona ? $ejecucion->colaborador->persona->primer_nombre : 'Sin asignación';

                return [(int) $ejecucion->id => "Limpieza #{$ejecucion->id} - {$area} ({$colaborador})"];
            })
            ->toArray();

        /** @var array<int, string> $opciones */
        return $opciones;
    }
}
