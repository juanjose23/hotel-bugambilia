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
        $fechaInicio = $data['fecha_inicio'] ?? null;
        $fechaFin = $data['fecha_fin'] ?? null;

        return new self(
            fechaInicio: is_string($fechaInicio) ? $fechaInicio : null,
            fechaFin: is_string($fechaFin) ? $fechaFin : null,
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
