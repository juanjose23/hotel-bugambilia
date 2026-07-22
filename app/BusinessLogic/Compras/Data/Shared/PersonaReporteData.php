<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Shared;

final readonly class PersonaReporteData
{
    public function __construct(
        public ?string $primer_nombre,
        public ?string $primer_apellido,
        public ?string $nombre_completo,
        public ?string $razon_social,
    ) {}
}
