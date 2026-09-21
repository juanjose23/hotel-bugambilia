<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Repository\Models\Inventario\MovimientoStock;

interface MovimientoStockRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function registrar(array $datos): MovimientoStock;

    /** @param array<int, array<string, mixed>> $movimientos */
    public function insertarMuchos(array $movimientos): void;
}
