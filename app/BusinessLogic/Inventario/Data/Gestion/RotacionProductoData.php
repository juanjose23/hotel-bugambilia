<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Gestion;

final readonly class RotacionProductoData
{
    public function __construct(
        public string $producto,
        public int $totalSalidas,
        public float $stockPromedio,
        public float $indiceRotacion,
        public string $clasificacion,
    ) {}
}
