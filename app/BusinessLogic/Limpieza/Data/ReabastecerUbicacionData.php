<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza\Data;

final readonly class ReabastecerUbicacionData
{
    /**
     * @param  array<int, ReabastecerItemData>  $items
     */
    public function __construct(
        public string $tipoDestino,
        public int $destinoId,
        public array $items,
        public int $bodegaOrigenId,
        public ?int $creadoPorId,
        public string $notas,
    ) {}
}
