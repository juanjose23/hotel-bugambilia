<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Activos\ActivoMantenimiento;
use App\Models\Catalogos\Ubicacion;
use Illuminate\Support\Facades\DB;

/**
 * Completa una orden de mantenimiento, restaura el activo a estado Activo
 * y lo devuelve físicamente al almacén general.
 */
class CompletarMantenimiento
{
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
            // 1. Cerrar la orden de mantenimiento
            $mantenimiento->update([
                'fecha_realizada' => $fechaRealizada,
                'costo_real' => $costoReal,
                'estado' => EstadoMantenimiento::Completado,
                'notas' => $notas,
            ]);

            // 2. Reactivar el activo
            $mantenimiento->activo->update([
                'estado' => EstadoActivo::Activo,
            ]);

            // 3. Cerrar la asignación de taller vigente
            ActivoAsignacion::where('activo_id', $mantenimiento->activo_id)
                ->whereNull('fecha_fin')
                ->update([
                    'fecha_fin' => now()->toDateString(),
                    'estado' => EstadoAsignacion::Cerrada,
                ]);

            // 4. Devolver físicamente al almacén general
            $almacen = Ubicacion::where('tipo', 'almacen')
                ->where('estado', 1)
                ->first()
                ?? Ubicacion::where('estado', 1)->first();

            if ($almacen) {
                ActivoAsignacion::create([
                    'activo_id' => $mantenimiento->activo_id,
                    'asignable_type' => Ubicacion::class,
                    'asignable_id' => $almacen->id,
                    'fecha_inicio' => now()->toDateString(),
                    'motivo' => 'Devolución física a almacén tras completado de mantenimiento técnico',
                    'asignado_por_id' => $usuarioId,
                    'estado' => EstadoAsignacion::Vigente,
                ]);
            }
        });
    }
}
