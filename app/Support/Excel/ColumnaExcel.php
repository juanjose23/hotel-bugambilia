<?php

declare(strict_types=1);

namespace App\Support\Excel;

final readonly class ColumnaExcel
{
    private function __construct(
        public string $encabezado,
        public \Closure $accesor,
        public bool $numerica = false,
        public ?string $formato = null,
    ) {}

    public static function make(
        string $encabezado,
        \Closure $accesor,
        bool $numerica = false,
        ?string $formato = null,
    ): self {
        return new self($encabezado, $accesor, $numerica, $formato);
    }
}
