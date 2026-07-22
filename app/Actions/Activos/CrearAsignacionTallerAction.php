<?php

declare(strict_types=1);

namespace App\Actions\Activos;

use App\Enums\Activos\EstadoAsignacion;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorioInterface;

class CrearAsignacionTallerAction
{
    public function __construct(
        private readonly ActivoAsignacionRepositorioInterface $asignacionRepositorio,
    ) {}

    public function ejecutar(Activo $activo, Ubicacion $ubicacionTaller, int $userId, string $motivo): ActivoAsignacion
    {
        return $this->asignacionRepositorio->crear([
            'activo_id' => $activo->id,
            'asignable_type' => Ubicacion::class,
            'asignable_id' => $ubicacionTaller->id,
            'fecha_inicio' => now()->toDateString(),
            'motivo' => $motivo,
            'asignado_por_id' => $userId,
            'estado' => EstadoAsignacion::Vigente,
        ]);
    }
}
