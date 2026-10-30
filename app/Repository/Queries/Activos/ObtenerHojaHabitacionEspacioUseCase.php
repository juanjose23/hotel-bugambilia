<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class ObtenerHojaHabitacionEspacioUseCase
{
    /**
     * @return array{entidad: ?Model, activos: Collection<int, ActivoAsignacion>}
     */
    public function ejecutar(string $tipo, int $id): array
    {
        if ($tipo === 'habitacion') {
            $entidad = $id > 0
                ? Habitacion::with(['categoria', 'ubicacion', 'detalle'])->find($id)
                : Habitacion::with(['categoria', 'ubicacion', 'detalle'])->first();

            $activos = $entidad
                ? ActivoAsignacion::with('activo.producto', 'activo.moneda')
                    ->where('asignable_type', Habitacion::class)
                    ->where('asignable_id', $entidad->id)
                    ->whereNull('fecha_fin')
                    ->get()
                : new Collection;
        } else {
            $entidad = $id > 0
                ? Espacio::with(['padre', 'ubicacion'])->find($id)
                : Espacio::with(['padre', 'ubicacion'])->first();

            $activos = $entidad
                ? ActivoAsignacion::with('activo.producto', 'activo.moneda')
                    ->where('asignable_type', Espacio::class)
                    ->where('asignable_id', $entidad->id)
                    ->whereNull('fecha_fin')
                    ->get()
                : new Collection;
        }

        return [
            'entidad' => $entidad,
            'activos' => $activos,
        ];
    }
}
