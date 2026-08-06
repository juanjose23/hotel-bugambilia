<?php

declare(strict_types=1);

namespace App\Interactors\Activos\Mantenimiento;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Persistencia\Activos\ActivoMantenimientoRepositorioInterface;
use App\Repository\Persistencia\Activos\ActPlanMantenimientoRepositorioInterface;
use App\Repository\Queries\Activos\ObtenerPlanesMantenimientoPreventivosActivos;

class DetectarMantenimientosPreventivos
{
    public function __construct(
        private readonly ObtenerPlanesMantenimientoPreventivosActivos $obtenerPlanes,
        private readonly ActivoMantenimientoRepositorioInterface $mantenimientoRepositorio,
        private readonly ActPlanMantenimientoRepositorioInterface $planRepositorio,
    ) {}

    public function ejecutar(): int
    {
        $creados = 0;

        $planes = $this->obtenerPlanes->ejecutar();

        foreach ($planes as $plan) {
            $proximo = $plan->fecha_proximo_mantenimiento;

            if ($proximo === null) {
                continue;
            }

            if ($proximo->gt(now()->endOfDay())) {
                continue;
            }

            foreach ($plan->activos as $activo) {
                if ($activo->estado === EstadoActivo::DadoDeBaja) {
                    continue;
                }

                $yaExiste = $this->mantenimientoRepositorio->buscarAbiertosPorActivoPlan(
                    activoId: $activo->id,
                    planId: $plan->id,
                    estados: [EstadoMantenimiento::Programado->value, EstadoMantenimiento::EnProceso->value]
                );

                if ($yaExiste) {
                    continue;
                }

                $this->mantenimientoRepositorio->crear([
                    'plan_id' => $plan->id,
                    'activo_id' => $activo->id,
                    'fecha_programada' => $proximo->toDateString(),
                    'estado' => EstadoMantenimiento::Programado,
                    'notas' => "Mantenimiento preventivo programado automáticamente cada {$plan->frecuencia_dias} días por el plan '{$plan->nombre}'.",
                ]);

                $creados++;
            }

            $plan->fecha_ultimo_mantenimiento = now();
            $plan->fecha_proximo_mantenimiento = $proximo->addDays((int) $plan->frecuencia_dias);
            $this->planRepositorio->guardar($plan);
        }

        return $creados;
    }
}
