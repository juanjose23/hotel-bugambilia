<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Queries;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoBaja;
use App\Models\Activos\ActivoMantenimiento;

class ObtenerEstadisticasReportesUseCase
{
    /**
     * @return array<string, int>
     */
    public function ejecutar(): array
    {
        $totalActivos = Activo::count();
        $enMantenimiento = Activo::where('estado', EstadoActivo::EnMantenimiento->value)->count();
        $extraviados = Activo::where('estado', EstadoActivo::Extraviado->value)->count();

        $sinAsignacion = Activo::whereDoesntHave('asignacionActiva')
            ->where('estado', '!=', EstadoActivo::DadoDeBaja->value)
            ->count();

        $mantenimientosVencidos = ActivoMantenimiento::whereIn('estado', [
            EstadoMantenimiento::Programado->value,
            EstadoMantenimiento::EnProceso->value,
        ])
            ->where('fecha_programada', '<', now())
            ->count();

        $garantiasProximas = Activo::whereNotNull('fecha_garantia_fin')
            ->where('fecha_garantia_fin', '<=', now()->addDays(90))
            ->count();

        $totalBajas = ActivoBaja::count();

        return [
            'totalActivos' => $totalActivos,
            'enMantenimiento' => $enMantenimiento,
            'extraviados' => $extraviados,
            'sinAsignacion' => $sinAsignacion,
            'mantenimientosVencidos' => $mantenimientosVencidos,
            'garantiasProximas' => $garantiasProximas,
            'totalBajas' => $totalBajas,
        ];
    }
}
