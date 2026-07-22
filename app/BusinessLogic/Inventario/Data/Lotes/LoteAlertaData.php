<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Lotes;

use App\Enums\Inventario\EstadoLote;
use Carbon\CarbonInterface;

final readonly class LoteAlertaData
{
    public function __construct(
        public int $id,
        public string $codigoLote,
        public string $producto,
        public ?string $variante,
        public string $ubicacion,
        public float $cantidadDisponible,
        public ?CarbonInterface $fechaVencimiento,
        public EstadoLote $estado,
    ) {}
}
