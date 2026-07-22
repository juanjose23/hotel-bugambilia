<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\OrdenesCompra;

use App\BusinessLogic\Compras\Data\Shared\ProductoReporteData;
use App\BusinessLogic\Compras\Data\Shared\ValorReporteData;
use App\BusinessLogic\Compras\Data\Shared\VarianteReporteData;

final readonly class OrdenCompraItemReporteData
{
    public function __construct(
        public int $id,
        public ?ProductoReporteData $producto,
        public ?VarianteReporteData $variante,
        public ?ValorReporteData $unidadMedida,
        public float $cantidad,
        public float $precio_unitario,
        public float $subtotal,
    ) {}
}
