<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Solicitudes;

use App\BusinessLogic\Compras\Data\Cotizaciones\CotizacionReporteData;
use App\BusinessLogic\Compras\Data\Shared\ColaboradorReporteData;
use App\BusinessLogic\Compras\Data\Shared\DepartamentoReporteData;
use App\BusinessLogic\Compras\Data\Shared\EstadoReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class SolicitudReporteData
{
    /**
     * @param  Collection<int, SolicitudItemReporteData>  $items
     * @param  Collection<int, CotizacionReporteData>  $cotizaciones
     */
    public function __construct(
        public int $id,
        public string $codigo,
        public ?CarbonInterface $fecha_solicitud,
        public ?CarbonInterface $fecha_necesita,
        public ?string $motivo,
        public ?string $notas,
        public ?ColaboradorReporteData $colaborador,
        public ?DepartamentoReporteData $departamentoSolicitante,
        public ?EstadoReporteData $estado,
        public Collection $items,
        public Collection $cotizaciones,
    ) {}
}
