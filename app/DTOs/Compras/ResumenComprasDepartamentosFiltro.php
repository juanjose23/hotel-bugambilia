<?php

declare(strict_types=1);

namespace App\DTOs\Compras;

readonly class ResumenComprasDepartamentosFiltro
{
    public function __construct(
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fechaInicio: $data['fecha_inicio'] ?? null,
            fechaFin: $data['fecha_fin'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
        ];
    }
}
