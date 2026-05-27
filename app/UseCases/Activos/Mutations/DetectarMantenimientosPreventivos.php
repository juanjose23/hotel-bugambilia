<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\Activos\ActPlanMantenimiento;
use App\Services\Activos\NotificadorActivos;
use Carbon\Carbon;

class DetectarMantenimientosPreventivos
{
    public function __construct(private readonly NotificadorActivos $notificador) {}

    public function execute(): int
    {
        $creados = 0;

        // 1. Obtener todos los planes preventivos activos
        $planes = ActPlanMantenimiento::query()
            ->where('tipo', 'preventivo')
            ->where('estado', 1) // 1 = Activo
            ->whereNotNull('frecuencia_dias')
            ->get();

        foreach ($planes as $plan) {
            // 2. Obtener los activos asociados a este plan (de sus mantenimientos previos o asignaciones)
            $activoIds = ActivoMantenimiento::query()
                ->where('plan_id', $plan->id)
                ->pluck('activo_id')
                ->unique();

            foreach ($activoIds as $activoId) {
                // 3. Buscar el último mantenimiento para este activo y plan
                $ultimoMantenimiento = ActivoMantenimiento::query()
                    ->where('plan_id', $plan->id)
                    ->where('activo_id', $activoId)
                    ->orderBy('fecha_programada', 'desc')
                    ->first();

                if (! $ultimoMantenimiento) {
                    continue;
                }

                $activo = $ultimoMantenimiento->activo;

                if (! $activo || $activo->estado === EstadoActivo::DadoDeBaja) {
                    continue;
                }

                // Si ya hay un mantenimiento pendiente (Programado o En Proceso), no creamos otro
                $yaExisteActivo = ActivoMantenimiento::query()
                    ->where('activo_id', $activoId)
                    ->whereIn('estado', [EstadoMantenimiento::Programado, EstadoMantenimiento::EnProceso])
                    ->exists();

                if ($yaExisteActivo) {
                    continue;
                }

                // Calcular la próxima fecha programada basada en el último mantenimiento completado o programado
                $fechaReferencia = $ultimoMantenimiento->fecha_realizada ?? $ultimoMantenimiento->fecha_programada;

                $proximoMantenimiento = Carbon::parse($fechaReferencia)->addDays($plan->frecuencia_dias);

                // Si la fecha programada ya llegó o es hoy/pasada, creamos el nuevo mantenimiento
                if ($proximoMantenimiento->lte(now()->endOfDay())) {
                    $mantenimiento = ActivoMantenimiento::create([
                        'plan_id' => $plan->id,
                        'activo_id' => $activoId,
                        'fecha_programada' => $proximoMantenimiento->toDateString(),
                        'estado' => EstadoMantenimiento::Programado,
                        'notas' => "Mantenimiento preventivo programado automáticamente cada {$plan->frecuencia_dias} días por el plan '{$plan->nombre}'.",
                    ]);

                    $this->notificador->mantenimientoPreventivoProgramado($mantenimiento);
                    $creados++;
                }
            }
        }

        return $creados;
    }
}
