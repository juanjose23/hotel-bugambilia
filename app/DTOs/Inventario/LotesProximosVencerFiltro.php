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
        return new self(
            dias: isset($data['dias']) ? (int) $data['dias'] : null,
            productoId: isset($data['producto_id']) ? (int) $data['producto_id'] : null,
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
