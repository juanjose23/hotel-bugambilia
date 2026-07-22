<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\OrdenesCompra;

use App\BusinessLogic\Compras\Data\Cotizaciones\CotizacionReporteData;
use App\BusinessLogic\Compras\Data\Shared\EstadoReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProveedorReporteData;
use App\BusinessLogic\Compras\Data\Shared\ValorReporteData;
use App\BusinessLogic\Compras\Data\Solicitudes\SolicitudReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class OrdenCompraReporteData
{
    /**
     * @param  Collection<int, OrdenCompraItemReporteData>  $items
     */
    public function __construct(
        public int $id,
        public string $codigo,
        public ?CarbonInterface $fecha_orden,
        public ?CarbonInterface $fecha_entrega_estimada,
        public float $total,
        public float $subtotal,
        public float $impuestos,
        public float $tasa_cambio,
        public ?ProveedorReporteData $proveedor,
        public ?ValorReporteData $condicionPago,
        public ?SolicitudReporteData $solicitud,
        public ?CotizacionReporteData $cotizacion,
        public ?EstadoReporteData $estado,
        public Collection $items,
    ) {}
}
