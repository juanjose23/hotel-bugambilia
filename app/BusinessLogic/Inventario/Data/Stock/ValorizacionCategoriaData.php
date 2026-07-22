<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Stock;

final readonly class ValorizacionCategoriaData
{
    public function __construct(
        public int $productoId,
        public string $producto,
        public ?string $categoria,
        public string $ubicacion,
        public float $stockTotal,
        public float $costoPromedio,
        public float $valorTotal,
    ) {}
}
