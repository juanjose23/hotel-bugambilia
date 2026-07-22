<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Data\Pack;

final readonly class VarianteData
{
    public function __construct(
        public int $id,
        public string $label,
    ) {}
}
