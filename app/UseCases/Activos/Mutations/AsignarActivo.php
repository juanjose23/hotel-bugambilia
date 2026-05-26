<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Mutations;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use Illuminate\Support\Facades\DB;

class AsignarActivo
{
    public function execute(int $activoId, string $asignableType, int $asignableId, int $userId, ?string $motivo = null): void
    {
        $activo = Activo::findOrFail($activoId);

        if ($activo->estado === EstadoActivo::DadoDeBaja) {
            throw new \RuntimeException('No se puede asignar un activo dado de baja.');
        }

        DB::transaction(function () use ($activo, $asignableType, $asignableId, $userId, $motivo) {
            // Cerrar asignación anterior
            ActivoAsignacion::where('activo_id', $activo->id)
                ->whereNull('fecha_fin')
                ->update([
                    'fecha_fin' => now()->toDateString(),
                    'estado' => EstadoAsignacion::Cerrada,
                ]);

            // Crear nueva asignación física
            ActivoAsignacion::create([
                'activo_id' => $activo->id,
                'asignable_type' => $asignableType,
                'asignable_id' => $asignableId,
                'fecha_inicio' => now()->toDateString(),
                'motivo' => $motivo ?: 'Traslado y reasignación física de activo',
                'asignado_por_id' => $userId,
                'estado' => EstadoAsignacion::Vigente,
            ]);
        });
    }
}
