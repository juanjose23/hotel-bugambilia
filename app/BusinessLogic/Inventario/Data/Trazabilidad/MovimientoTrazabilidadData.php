<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Trazabilidad;

use Carbon\CarbonInterface;

final readonly class MovimientoTrazabilidadData
{
    public function __construct(
        public int $id,
        public string $tipo,
        public string $producto,
        public float $cantidad,
        public ?string $ubicacionOrigen,
        public ?string $ubicacionDestino,
        public ?CarbonInterface $fecha,
    ) {}
}
