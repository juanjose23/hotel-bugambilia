<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Shared;

final readonly class ColaboradorReporteData
{
    public function __construct(
        public ?string $codigo,
        public ?PersonaReporteData $persona,
    ) {}
}
