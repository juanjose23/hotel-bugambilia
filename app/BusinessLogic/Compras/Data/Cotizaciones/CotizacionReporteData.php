<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Cotizaciones;

use App\BusinessLogic\Compras\Data\Shared\MonedaReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProveedorReporteData;
use App\BusinessLogic\Compras\Data\Solicitudes\SolicitudReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class CotizacionReporteData
{
    /**
     * @param  Collection<int, CotizacionItemReporteData>  $items
     */
    public function __construct(
        public int $id,
        public int $solicitud_id,
        public int $proveedor_id,
        public ?ProveedorReporteData $proveedor,
        public ?MonedaReporteData $moneda,
        public float $total,
        public int $tiempo_entrega_dias,
        public int $dias_entrega,
        public ?CarbonInterface $fecha_cotizacion,
        public bool $es_elegida,
        public float $tasa_cambio,
        public ?string $observaciones,
        public ?SolicitudReporteData $solicitud,
        public Collection $items,
    ) {}
}
