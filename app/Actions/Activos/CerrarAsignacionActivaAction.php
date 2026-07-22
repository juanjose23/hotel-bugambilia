<?php

declare(strict_types=1);

namespace App\Actions\Activos;

use App\Enums\Activos\EstadoAsignacion;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorioInterface;

class CerrarAsignacionActivaAction
{
    public function __construct(
        private readonly ActivoAsignacionRepositorioInterface $asignacionRepositorio,
    ) {}

    public function ejecutar(Activo $activo): ?ActivoAsignacion
    {
        $ultimaAsignacion = $this->asignacionRepositorio->buscarVigentePorActivo($activo->id);

        if ($ultimaAsignacion) {
            $ultimaAsignacion->fecha_fin = now()->toImmutable();
            $ultimaAsignacion->estado = EstadoAsignacion::Cerrada;
            $this->asignacionRepositorio->guardar($ultimaAsignacion);
        }

        return $ultimaAsignacion;
    }
}
