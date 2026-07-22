<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ObtenerHojaHabitacionEspacioUseCase
{
    /**
     * @return array{entidad: Model, activos: Collection<int, ActivoAsignacion>}
     */
    public function ejecutar(string $tipo, int $id): array
    {
        if ($tipo === 'habitacion') {
            $entidad = Habitacion::with(['categoria', 'ubicacion', 'detalle'])->findOrFail($id);
            $activos = ActivoAsignacion::with('activo.producto', 'activo.moneda')
                ->where('asignable_type', Habitacion::class)
                ->where('asignable_id', $id)
                ->whereNull('fecha_fin')
                ->get();
        } else {
            $entidad = Espacio::with(['padre', 'ubicacion'])->findOrFail($id);
            $activos = ActivoAsignacion::with('activo.producto', 'activo.moneda')
                ->where('asignable_type', Espacio::class)
                ->where('asignable_id', $id)
                ->whereNull('fecha_fin')
                ->get();
        }

        return [
            'entidad' => $entidad,
            'activos' => $activos,
        ];
    }
}
