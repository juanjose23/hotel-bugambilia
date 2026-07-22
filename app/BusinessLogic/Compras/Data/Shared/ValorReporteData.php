<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Shared;

final readonly class ValorReporteData
{
    public function __construct(
        public ?string $valor,
    ) {}
}
