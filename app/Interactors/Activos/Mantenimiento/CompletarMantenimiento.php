<?php

declare(strict_types=1);

namespace App\Interactors\Activos\Mantenimiento;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoMantenimiento;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoMantenimientoRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use App\Repository\Persistencia\Activos\ActPlanMantenimientoRepositorioInterface;
use App\Repository\Queries\Catalogos\ObtenerUbicacionAlmacen;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Completa una orden de mantenimiento, restaura el activo a estado Activo
 * y lo devuelve físicamente al almacén general.
 */
class CompletarMantenimiento
{
    public function __construct(
        private readonly ActivoMantenimientoRepositorioInterface $mantenimientoRepositorio,
        private readonly ActivoRepositorioInterface $activoRepositorio,
        private readonly ActivoAsignacionRepositorioInterface $asignacionRepositorio,
        private readonly ActPlanMantenimientoRepositorioInterface $planRepositorio,
        private readonly ObtenerUbicacionAlmacen $obtenerAlmacen,
    ) {}

    /**
     * @throws \Throwable
     */
    public function execute(
        ActivoMantenimiento $mantenimiento,
        string $fechaRealizada,
        float $costoReal,
        string $notas,
        int $usuarioId
    ): void {
        DB::transaction(function () use ($mantenimiento, $fechaRealizada, $costoReal, $notas, $usuarioId): void {

            $mantenimiento->fecha_realizada = CarbonImmutable::parse($fechaRealizada);
            $mantenimiento->costo_real = $costoReal;
            $mantenimiento->estado = EstadoMantenimiento::Completado;
            $mantenimiento->notas = $notas;
            $this->mantenimientoRepositorio->guardar($mantenimiento);

            // 2. Reactivar el activo
            if ($mantenimiento->activo) {
                $mantenimiento->activo->estado = EstadoActivo::Activo;
                $this->activoRepositorio->guardar($mantenimiento->activo);
            }

            $this->asignacionRepositorio->cerrarAsignacionesVigentes(
                activoId: $mantenimiento->activo_id,
                fechaFin: now()->toDateString(),
                estado: EstadoAsignacion::Cerrada->value
            );

            $almacen = $this->obtenerAlmacen->ejecutar();

            if ($almacen) {
                $this->asignacionRepositorio->crear([
                    'activo_id' => $mantenimiento->activo_id,
                    'asignable_type' => Ubicacion::class,
                    'asignable_id' => $almacen->id,
                    'fecha_inicio' => now()->toDateString(),
                    'motivo' => 'Devolución física a almacén tras completado de mantenimiento técnico',
                    'asignado_por_id' => $usuarioId,
                    'estado' => EstadoAsignacion::Vigente,
                ]);
            }

            // 5. Actualizar fechas del plan si el ticket pertenece a uno
            $plan = $mantenimiento->plan;
            if ($plan !== null && $plan->frecuencia_dias !== null) {
                $plan->fecha_ultimo_mantenimiento = CarbonImmutable::parse($fechaRealizada);
                $plan->fecha_proximo_mantenimiento = CarbonImmutable::parse($fechaRealizada)
                    ->addDays((int) $plan->frecuencia_dias);
                $this->planRepositorio->guardar($plan);
            }
        });
    }
}
