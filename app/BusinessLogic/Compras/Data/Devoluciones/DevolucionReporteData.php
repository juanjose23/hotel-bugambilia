<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Devoluciones;

use App\BusinessLogic\Compras\Data\Shared\ColaboradorReporteData;
use App\BusinessLogic\Compras\Data\Shared\EstadoReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final readonly class DevolucionReporteData
{
    /**
     * @param  Collection<int, DevolucionItemReporteData>  $items
     */
    public function __construct(
        public int $id,
        public string $codigo,
        public ?CarbonInterface $fecha_devolucion,
        public ?string $motivo,
        public ?string $documento_externo,
        public ?ColaboradorReporteData $creador,
        public ?string $ordenCompraCodigo,
        public ?string $recepcionCompraCodigo,
        public ?EstadoReporteData $estado,
        public Collection $items,
    ) {}
}
