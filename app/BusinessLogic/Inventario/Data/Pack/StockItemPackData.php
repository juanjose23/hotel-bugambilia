<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Pack;

final readonly class StockItemPackData
{
    public function __construct(
        public int $varianteId,
        public string $nombreVariante,
        public string $codigo,
        public float $cantidadNecesaria,
        public float $stockTotal,
    ) {}
}
