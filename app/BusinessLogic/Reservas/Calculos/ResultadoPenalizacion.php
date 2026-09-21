<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Calculos;

final readonly class ResultadoPenalizacion
{
    public function __construct(
        public float $porcentaje,
        public float $monto,
    ) {}
}
