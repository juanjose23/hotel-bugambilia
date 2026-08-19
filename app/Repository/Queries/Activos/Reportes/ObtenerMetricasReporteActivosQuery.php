<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos\Reportes;

use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoMantenimiento;

final class ObtenerMetricasReporteActivosQuery
{
    /**
     * @return array{
     *     totalActivos: int,
     *     activosPendientes: int,
     *     valorTotalActivos: float,
     *     mantenimientosVencidos: int
     * }
     */
    public function ejecutar(): array
    {
        return [
            'totalActivos' => Activo::count(),
            'activosPendientes' => ActivoMantenimiento::whereIn('estado', [
                EstadoMantenimiento::Programado,
                EstadoMantenimiento::EnProceso,
            ])->count(),
            'valorTotalActivos' => (float) Activo::sum('costo_adquisicion'),
            'mantenimientosVencidos' => ActivoMantenimiento::where('fecha_programada', '<', now()->toDateString())
                ->whereIn('estado', [
                    EstadoMantenimiento::Programado,
                    EstadoMantenimiento::EnProceso,
                ])->count(),
        ];
    }
}
