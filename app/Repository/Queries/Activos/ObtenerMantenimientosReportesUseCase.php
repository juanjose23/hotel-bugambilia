<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ObtenerMantenimientosReportesUseCase
{
    /**
     * @return EloquentCollection<int, Activo>
     */
    public function enMantenimiento(): EloquentCollection
    {
        return Activo::with([
            'producto',
            'mantenimientos' => fn ($q) => $q->whereIn('estado', [
                EstadoMantenimiento::EnProceso->value,
                EstadoMantenimiento::Programado->value,
            ])
                ->with('plan.proveedor.persona')
                ->latest('fecha_programada'),
        ])
            ->where('estado', EstadoActivo::EnMantenimiento->value)
            ->get();
    }

    /**
     * @return EloquentCollection<int, ActivoMantenimiento>
     */
    public function mantenimientosVencidos(): EloquentCollection
    {
        return ActivoMantenimiento::with([
            'activo',
            'plan.proveedor.persona',
        ])
            ->whereIn('estado', [EstadoMantenimiento::Programado->value, EstadoMantenimiento::EnProceso->value])
            ->where('fecha_programada', '<', now())
            ->orderBy('fecha_programada')
            ->get();
    }
}
