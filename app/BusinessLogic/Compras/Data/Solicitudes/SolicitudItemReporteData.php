<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Solicitudes;

use App\BusinessLogic\Compras\Data\Shared\ProductoReporteData;
use App\BusinessLogic\Compras\Data\Shared\VarianteReporteData;

final readonly class SolicitudItemReporteData
{
    public function __construct(
        public int $id,
        public int $producto_id,
        public ?ProductoReporteData $producto,
        public ?VarianteReporteData $productoVariante,
        public ?VarianteReporteData $variante,
        public float $cantidad_solicitada,
        public float $cantidad_aprobada,
    ) {}
}
