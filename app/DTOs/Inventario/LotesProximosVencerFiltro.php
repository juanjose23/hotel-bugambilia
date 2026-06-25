<?php

declare(strict_types=1);

namespace App\DTOs\Inventario;

readonly class LotesProximosVencerFiltro
{
    public function __construct(
        public ?int $dias = null,
        public ?int $productoId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $dias = $data['dias'] ?? null;
        $productoId = $data['producto_id'] ?? null;

        return new self(
            dias: is_numeric($dias) ? (int) $dias : null,
            productoId: is_numeric($productoId) ? (int) $productoId : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'dias' => $this->dias,
            'producto_id' => $this->productoId,
        ];
    }
}
