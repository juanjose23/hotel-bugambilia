<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Cotizaciones;

use App\BusinessLogic\Compras\Data\Shared\ProductoReporteData;
use App\BusinessLogic\Compras\Data\Shared\VarianteReporteData;

final readonly class CotizacionItemReporteData
{
    public function __construct(
        public int $id,
        public int $producto_id,
        public ?ProductoReporteData $producto,
        public ?VarianteReporteData $variante,
        public float $cantidad,
        public float $precio_unitario,
        public float $subtotal,
        public bool $es_elegido,
    ) {}
}
