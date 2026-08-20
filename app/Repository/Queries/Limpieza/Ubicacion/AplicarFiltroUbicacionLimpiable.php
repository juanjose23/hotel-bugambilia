<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ubicacion;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use Illuminate\Database\Eloquent\Builder;

final class AplicarFiltroUbicacionLimpiable
{
    /** @param Builder<LimpiezaEjecucion> $query */
    public function execute(Builder $query, mixed $ubicacionId): void
    {
        if (! is_numeric($ubicacionId)) {
            return;
        }

        $ubicacionIds = Ubicacion::obtenerDescendientesIds((int) $ubicacionId);

        $query->where(function (Builder $q) use ($ubicacionIds): void {
            $q->where(function (Builder $sub) use ($ubicacionIds): void {
                $sub->where('limpiable_type', Ubicacion::class)
                    ->whereIn('limpiable_id', $ubicacionIds);
            })->orWhere(function (Builder $sub) use ($ubicacionIds): void {
                $sub->where('limpiable_type', Habitacion::class)
                    ->whereIn('limpiable_id', function ($subQuery) use ($ubicacionIds): void {
                        $subQuery->select('id')
                            ->from('habitaciones')
                            ->whereIn('ubicacion_id', $ubicacionIds);
                    });
            })->orWhere(function (Builder $sub) use ($ubicacionIds): void {
                $sub->where('limpiable_type', Espacio::class)
                    ->whereIn('limpiable_id', function ($subQuery) use ($ubicacionIds): void {
                        $subQuery->select('id')
                            ->from('espacios')
                            ->whereIn('ubicacion_id', $ubicacionIds);
                    });
            });
        });
    }
}
