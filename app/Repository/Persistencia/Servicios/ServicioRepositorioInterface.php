<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Servicios;

use App\Repository\Models\Servicios\Servicio;

interface ServicioRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Servicio;
}
