<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Shared;

final readonly class VarianteReporteData
{
    public function __construct(
        public ?string $codigo,
        public ?string $nombre_variante,
    ) {}
}
