<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Trazabilidad;

use App\BusinessLogic\Inventario\Data\Lotes\LoteAlertaData;
use Illuminate\Support\Collection;

final readonly class TrazabilidadAdelanteData
{
    /**
     * @param  Collection<int, MovimientoTrazabilidadData>  $movimientos
     */
    public function __construct(
        public LoteAlertaData $lote,
        public Collection $movimientos,
    ) {}
}
