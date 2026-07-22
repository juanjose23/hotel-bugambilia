<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Recepciones;

use App\BusinessLogic\Compras\Data\OrdenesCompra\OrdenCompraReporteData;
use App\BusinessLogic\Compras\Data\Shared\ColaboradorReporteData;
use App\BusinessLogic\Compras\Data\Shared\EstadoReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class RecepcionReporteData
{
    /**
     * @param  Collection<int, RecepcionItemReporteData>  $items
     */
    public function __construct(
        public int $id,
        public string $codigo,
        public ?CarbonInterface $fecha_recepcion,
        public ?string $guia_remision,
        public ?string $factura_referencia,
        public ?ColaboradorReporteData $receptor,
        public ?OrdenCompraReporteData $ordenCompra,
        public ?EstadoReporteData $estado,
        public Collection $items,
    ) {}
}
