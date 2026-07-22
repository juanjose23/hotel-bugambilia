<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Servicios;

use App\Repository\Models\Servicios\Servicio;

class ServicioRepositorio implements ServicioRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Servicio
    {
        return Servicio::create($datos);
    }
}
