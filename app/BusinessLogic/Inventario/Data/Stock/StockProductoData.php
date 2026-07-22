<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Stock;

final readonly class StockProductoData
{
    public function __construct(
        public int $productoId,
        public string $producto,
        public ?string $variante,
        public ?string $categoria,
        public int $ubicacionId,
        public string $ubicacion,
        public float $stockDisponible,
        public float $stockCuarentena,
        public int $totalLotes,
    ) {}
}
