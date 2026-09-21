<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Repository\Models\Inventario\MovimientoStock;

final class MovimientoStockRepositorio implements MovimientoStockRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function registrar(array $datos): MovimientoStock
    {
        return MovimientoStock::create($datos);
    }

    /** @param array<int, array<string, mixed>> $movimientos */
    public function insertarMuchos(array $movimientos): void
    {
        MovimientoStock::query()->insert($movimientos);
    }
}
