<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Stock;

final readonly class CostoVentasData
{
    public function __construct(
        public int $productoId,
        public string $producto,
        public ?string $variante,
        public ?string $categoria,
        public float $cantidadComprada,
        public float $costoCompras,
        public float $cantidadConsumida,
        public float $costoConsumo,
        public float $desviacionPorcentaje,
    ) {}
}
