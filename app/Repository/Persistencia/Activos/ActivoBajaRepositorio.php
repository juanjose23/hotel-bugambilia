<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Activos;

use App\Repository\Models\Activos\ActivoBaja;

class ActivoBajaRepositorio implements ActivoBajaRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): ActivoBaja
    {
        return ActivoBaja::create($datos);
    }
}
