<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza\Data;

final readonly class EnviarLavanderiaItemData
{
    public function __construct(
        public string $tipo,
        public int $stockId,
        public float $cantidad,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        /** @var string $tipo */
        $tipo = $data['tipo'];
        /** @var int|string $stockId */
        $stockId = $data['stock_id'];
        /** @var float|int $cantidad */
        $cantidad = $data['cantidad'];

        return new self(
            tipo: (string) $tipo,
            stockId: (int) $stockId,
            cantidad: (float) $cantidad,
        );
    }
}
