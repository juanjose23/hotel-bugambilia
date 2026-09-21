<?php

declare(strict_types=1);

namespace App\Interactors\Servicios;

use App\Repository\Models\Servicios\Servicio;

final class ObtenerAforoServicio
{
    /**
     * Aforo derivado de la flota fija: cantidad de activos vigentes asignados al servicio.
     */
    public function ejecutar(int $servicioId): int
    {
        $servicio = Servicio::query()->findOrFail($servicioId);

        return $servicio->inventarioFijo()->count();
    }
}
