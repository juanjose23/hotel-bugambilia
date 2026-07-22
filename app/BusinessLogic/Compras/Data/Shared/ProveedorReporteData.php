<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Shared;

final readonly class ProveedorReporteData
{
    public function __construct(
        public ?PersonaReporteData $persona,
        public ?string $contacto_nombre,
    ) {}
}
