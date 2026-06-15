<?php

declare(strict_types=1);

namespace App\DTOs\Inventario;

readonly class LotesCuarentenaFiltro
{
    public function __construct(
        public ?int $productoId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productoId: isset($data['producto_id']) ? (int) $data['producto_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'producto_id' => $this->productoId,
        ];
    }
}
