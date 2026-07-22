<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Mermas;

final readonly class MermaDetalleData
{
    public function __construct(
        public string $tipoMovimiento,
        public ?string $referencia,
        public string $producto,
        public float $cantidadPerdida,
        public float $costoUnitario,
        public float $perdidaTotal,
        public string $categoria,
    ) {}
}
