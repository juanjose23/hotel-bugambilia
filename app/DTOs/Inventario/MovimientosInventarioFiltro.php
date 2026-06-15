<?php

declare(strict_types=1);

namespace App\DTOs\Inventario;

readonly class MovimientosInventarioFiltro
{
    public function __construct(
        public ?string $tipo = null,
        public ?int $productoId = null,
        public ?int $loteId = null,
        public ?string $fechaDesde = null,
        public ?string $fechaHasta = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tipo: $data['tipo'] ?? null,
            productoId: isset($data['producto_id']) ? (int) $data['producto_id'] : null,
            loteId: isset($data['lote_id']) ? (int) $data['lote_id'] : null,
            fechaDesde: $data['fecha_desde'] ?? null,
            fechaHasta: $data['fecha_hasta'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tipo' => $this->tipo,
            'producto_id' => $this->productoId,
            'lote_id' => $this->loteId,
            'fecha_desde' => $this->fechaDesde,
            'fecha_hasta' => $this->fechaHasta,
        ];
    }
}
