<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\BusinessLogic\Activos\CalcularDepreciacionActivo;
use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoBaja;
use App\Repository\Models\Activos\ActivoMantenimiento;

class ObtenerEstadisticasReportesUseCase
{
    public function __construct(
        private readonly CalcularDepreciacionActivo $calcularDepreciacion,
    ) {}

    /**
     * @return array<string, int|float>
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

        $valorTotalNeto = Activo::where('estado', '!=', EstadoActivo::DadoDeBaja->value)
            ->get()
            ->sum(fn (Activo $a) => $this->calcularDepreciacion->ejecutar(
                costoAdquisicion: $a->costo_adquisicion !== null ? (float) $a->costo_adquisicion : null,
                vidaUtilMeses: $a->vida_util_meses !== null ? (int) $a->vida_util_meses : null,
                fechaAdquisicion: $a->fecha_adquisicion,
            )['valor_libros'] ?? 0.0);

        return [
            'totalActivos' => $totalActivos,
            'enMantenimiento' => $enMantenimiento,
            'extraviados' => $extraviados,
            'sinAsignacion' => $sinAsignacion,
            'mantenimientosVencidos' => $mantenimientosVencidos,
            'garantiasProximas' => $garantiasProximas,
            'totalBajas' => $totalBajas,
            'valorTotalNeto' => $valorTotalNeto,
        ];
    }
}
