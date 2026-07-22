<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ejecucion;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

class ObtenerOpcionesEjecucion
{
    /**
     * @return array<int, string>
     */
    public function execute(): array
    {
        /** @var array<int, string> $options */
        $options = LimpiezaEjecucion::query()
            ->with(['limpiable', 'turno'])
            ->where('estado', EstadoLimpieza::Pendiente)
            ->get()
            ->mapWithKeys(function ($e) {
                $limpiable = $e->limpiable;
                $nombre = $limpiable ? $limpiable->getAttribute('nombre') : null;
                $area = is_string($nombre) ? $nombre : '';
                $turno = $e->turno?->nombre ? " (Turno: {$e->turno->nombre})" : '';

                return [$e->id => "Ejecución #{$e->id}: {$area}{$turno}"];
            })
            ->toArray();

        return $options;
    }
}
