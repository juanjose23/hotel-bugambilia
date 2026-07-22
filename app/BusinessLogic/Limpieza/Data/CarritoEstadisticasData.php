<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza\Data;

use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final readonly class CarritoEstadisticasData
{
    public function __construct(
        public int $totalItems,
        public float $totalCantidad,
        public int $totalMovimientos,
        public bool $bloqueado,
        public bool $esAsignado,
        public ?string $nombreColaborador,
        public ?LimpiezaEjecucion $ejecucionActiva,
        public ?MovimientoStock $ultimoAbastecimiento,
    ) {}
}
