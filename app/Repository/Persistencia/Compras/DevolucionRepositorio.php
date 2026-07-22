<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Repository\Models\Compras\DevolucionCompra;

class DevolucionRepositorio implements DevolucionRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): DevolucionCompra
    {
        return DevolucionCompra::create($datos);
    }

    public function guardar(DevolucionCompra $devolucion): bool
    {
        return $devolucion->save();
    }
}
