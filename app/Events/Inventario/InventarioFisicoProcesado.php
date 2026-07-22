<?php

declare(strict_types=1);

namespace App\Events\Inventario;

use App\Repository\Models\Inventario\InventarioFisico;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class InventarioFisicoProcesado
{
    use Dispatchable;

    public function __construct(
        public InventarioFisico $inventarioFisico,
        public int $procesadoPorId,
    ) {}
}
