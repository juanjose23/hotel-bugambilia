<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Queries;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoMantenimiento;
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
                ->with('plan.proveedor.persona') // Fix: use plan.proveedor
                ->latest('fecha_programada'), // Fix: use fecha_programada instead of fecha_inicio
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
            'plan.proveedor.persona', // Fix: use plan.proveedor
        ])
            ->whereIn('estado', [EstadoMantenimiento::Programado->value, EstadoMantenimiento::EnProceso->value])
            ->where('fecha_programada', '<', now()) // Fix: use fecha_programada instead of fecha_inicio
            ->orderBy('fecha_programada') // Fix: use fecha_programada instead of fecha_inicio
            ->get();
    }
}
