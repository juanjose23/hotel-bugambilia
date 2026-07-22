<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Shared;

final readonly class DepartamentoReporteData
{
    public function __construct(
        public ?string $nombre,
    ) {}
}
