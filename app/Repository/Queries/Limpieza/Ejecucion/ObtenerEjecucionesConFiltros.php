<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ejecucion;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use Illuminate\Database\Eloquent\Collection;

class ObtenerEjecucionesConFiltros
{
    /**
     * @param  array{tipo_ubicacion?: string, limpiable_id?: int, sub_ubicacion_id?: int, mesa_id?: int}  $filtros
     * @return Collection<int, LimpiezaEjecucion>
     */
    public function execute(array $filtros = []): Collection
    {
        $query = LimpiezaEjecucion::query()
            ->whereDate('fecha', now()->toDateString())
            ->whereIn('estado', [
                EstadoLimpieza::Pendiente,
                EstadoLimpieza::EnProgreso,
                EstadoLimpieza::Completada,
                EstadoLimpieza::CompletadaConDiscrepancia,
            ])
            ->with(['limpiable', 'colaborador.persona', 'horario']);

        $tipoUbicacion = $filtros['tipo_ubicacion'] ?? null;
        $limpiableId = $filtros['limpiable_id'] ?? null;
        $subUbicacionId = $filtros['sub_ubicacion_id'] ?? ($filtros['mesa_id'] ?? null);

        if ($tipoUbicacion) {
            $limpiableIdInt = $limpiableId !== null ? (int) $limpiableId : 0;
            $subUbicacionIdInt = $subUbicacionId !== null ? (int) $subUbicacionId : 0;

            if ($tipoUbicacion === 'habitacion') {
                $query->where('limpiable_type', Habitacion::class);
                if ($limpiableIdInt > 0) {
                    $query->where('limpiable_id', $limpiableIdInt);
                }
            } elseif ($tipoUbicacion === 'espacio') {
                $query->where('limpiable_type', Espacio::class);
                if ($subUbicacionIdInt > 0) {
                    $query->where('limpiable_id', $subUbicacionIdInt);
                } elseif ($limpiableIdInt > 0) {
                    $restaurantId = $limpiableIdInt;
                    $query->where(function ($q) use ($restaurantId) {
                        $q->where('limpiable_id', $restaurantId)
                            ->orWhereIn('limpiable_id', function ($sub) use ($restaurantId) {
                                $sub->select('id')
                                    ->from((new Espacio)->getTable())
                                    ->where('padre_id', $restaurantId);
                            });
                    });
                }
            } elseif ($tipoUbicacion === 'ubicacion') {
                $targetUbicacionId = $subUbicacionIdInt > 0 ? $subUbicacionIdInt : $limpiableIdInt;

                if ($targetUbicacionId > 0) {
                    $ubicacionIds = Ubicacion::obtenerDescendientesIds($targetUbicacionId);
                    $query->where(function ($q) use ($ubicacionIds) {
                        $q->where(function ($sub) use ($ubicacionIds) {
                            $sub->where('limpiable_type', Ubicacion::class)
                                ->whereIn('limpiable_id', $ubicacionIds);
                        })->orWhere(function ($sub) use ($ubicacionIds) {
                            $sub->where('limpiable_type', Habitacion::class)
                                ->whereIn('limpiable_id', function ($subQuery) use ($ubicacionIds) {
                                    $subQuery->select('id')
                                        ->from((new Habitacion)->getTable())
                                        ->whereIn('ubicacion_id', $ubicacionIds);
                                });
                        })->orWhere(function ($sub) use ($ubicacionIds) {
                            $sub->where('limpiable_type', Espacio::class)
                                ->whereIn('limpiable_id', function ($subQuery) use ($ubicacionIds) {
                                    $subQuery->select('id')
                                        ->from((new Espacio)->getTable())
                                        ->whereIn('ubicacion_id', $ubicacionIds);
                                });
                        });
                    });
                } else {
                    $query->where('limpiable_type', Ubicacion::class);
                }
            }
        }

        /** @var Collection<int, LimpiezaEjecucion> */
        return $query->get();
    }
}
