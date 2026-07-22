<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\ActivoAsignacion;

class ActivoAsignacionRepositorio implements ActivoAsignacionRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): ActivoAsignacion
    {
        return ActivoAsignacion::create($datos);
    }

    public function cerrarAsignacionesVigentes(int $activoId, string $fechaFin, int $estado): void
    {
        ActivoAsignacion::where('activo_id', $activoId)
            ->whereNull('fecha_fin')
            ->update([
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
            ]);
    }

    public function buscarVigentePorActivo(int $activoId): ?ActivoAsignacion
    {
        return ActivoAsignacion::where('activo_id', $activoId)
            ->whereNull('fecha_fin')
            ->first();
    }

    public function guardar(ActivoAsignacion $asignacion): void
    {
        $asignacion->save();
    }
}
