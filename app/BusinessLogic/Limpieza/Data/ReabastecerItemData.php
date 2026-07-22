<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza\Data;

final readonly class ReabastecerItemData
{
    public function __construct(
        public int $productoVarianteId,
        public float $cantidad,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        /** @var int|string $productoVarianteId */
        $productoVarianteId = $data['producto_variante_id'];
        /** @var float|int $cantidad */
        $cantidad = $data['cantidad'];

        return new self(
            productoVarianteId: (int) $productoVarianteId,
            cantidad: (float) $cantidad,
        );
    }
}
