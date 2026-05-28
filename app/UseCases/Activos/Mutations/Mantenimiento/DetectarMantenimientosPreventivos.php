<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations\Mantenimiento;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Enums\Activos\EstadoPlanMantenimiento;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\Activos\ActPlanMantenimiento;
use Carbon\Carbon;

class DetectarMantenimientosPreventivos
{
    public function __construct() {}

    public function execute(): int
    {
        $creados = 0;

        $planes = ActPlanMantenimiento::query()
            ->where('tipo', 'preventivo')
            ->where('estado', EstadoPlanMantenimiento::Activo)
            ->whereNotNull('frecuencia_dias')
            ->has('activos')
            ->get();

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

                $yaExiste = ActivoMantenimiento::query()
                    ->where('activo_id', $activo->id)
                    ->where('plan_id', $plan->id)
                    ->whereIn('estado', [EstadoMantenimiento::Programado, EstadoMantenimiento::EnProceso])
                    ->exists();

                if ($yaExiste) {
                    continue;
                }

                ActivoMantenimiento::create([
                    'plan_id' => $plan->id,
                    'activo_id' => $activo->id,
                    'fecha_programada' => $proximo->toDateString(),
                    'estado' => EstadoMantenimiento::Programado,
                    'notas' => "Mantenimiento preventivo programado automáticamente cada {$plan->frecuencia_dias} días por el plan '{$plan->nombre}'.",
                ]);

                $creados++;
            }

            $plan->fecha_ultimo_mantenimiento = now();
            $plan->fecha_proximo_mantenimiento = Carbon::parse($proximo)->addDays((int) $plan->frecuencia_dias);
            $plan->save();
        }

        return $creados;
    }
}
