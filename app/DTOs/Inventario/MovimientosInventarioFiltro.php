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
        $tipo = $data['tipo'] ?? null;
        $productoId = $data['producto_id'] ?? null;
        $loteId = $data['lote_id'] ?? null;
        $fechaDesde = $data['fecha_desde'] ?? null;
        $fechaHasta = $data['fecha_hasta'] ?? null;

        return new self(
            tipo: is_string($tipo) ? $tipo : null,
            productoId: is_numeric($productoId) ? (int) $productoId : null,
            loteId: is_numeric($loteId) ? (int) $loteId : null,
            fechaDesde: is_string($fechaDesde) ? $fechaDesde : null,
            fechaHasta: is_string($fechaHasta) ? $fechaHasta : null,
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
