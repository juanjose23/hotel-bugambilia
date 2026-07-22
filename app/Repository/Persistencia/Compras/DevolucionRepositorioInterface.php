<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Repository\Models\Compras\DevolucionCompra;

interface DevolucionRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): DevolucionCompra;

    public function guardar(DevolucionCompra $devolucion): bool;
}
