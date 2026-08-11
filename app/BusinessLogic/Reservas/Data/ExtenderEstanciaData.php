<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

use Carbon\CarbonInterface;

final readonly class ExtenderEstanciaData
{
    public function __construct(
        public int $estanciaId,
        public CarbonInterface $nuevaFechaSalida,
        public ?float $tarifaAdicional = null,
        public ?string $observaciones = null,
        public ?int $usuarioId = null,
    ) {}
}
