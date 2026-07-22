<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Stock;

final readonly class StockMinimoData
{
    public function __construct(
        public int $productoId,
        public string $producto,
        public ?string $variante,
        public ?string $categoria,
        public float $stockActual,
        public float $puntoPedido,
        public float $pendienteReplenish,
        public string $estado,
    ) {}
}
