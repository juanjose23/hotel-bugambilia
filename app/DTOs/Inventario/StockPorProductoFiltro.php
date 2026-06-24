<?php

declare(strict_types=1);

namespace App\DTOs\Inventario;

readonly class StockPorProductoFiltro
{
    public function __construct(
        public ?int $productoId = null,
        public ?int $ubicacionId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $productoId = $data['producto_id'] ?? null;
        $ubicacionId = $data['ubicacion_id'] ?? null;

        return new self(
            productoId: is_numeric($productoId) ? (int) $productoId : null,
            ubicacionId: is_numeric($ubicacionId) ? (int) $ubicacionId : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'producto_id' => $this->productoId,
            'ubicacion_id' => $this->ubicacionId,
        ];
    }
}
