<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras\Data\Shared;

final readonly class EstadoReporteData
{
    public string $value;

    public function __construct(
        string|int $value,
        public string $label,
    ) {
        $this->value = (string) $value;
    }

    public function label(): string
    {
        return $this->label;
    }
}
