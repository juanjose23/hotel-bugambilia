<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza\Data;

final readonly class EnviarALavanderiaData
{
    /** @param array<int, EnviarLavanderiaItemData> $items */
    public function __construct(
        public array $items,
        public int $ubicacionLavanderiaId,
        public ?int $creadoPorId,
        public string $notas,
    ) {}
}
