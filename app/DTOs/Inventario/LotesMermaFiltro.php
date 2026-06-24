<?php

declare(strict_types=1);

namespace App\DTOs\Inventario;

use Carbon\Carbon;

readonly class LotesMermaFiltro
{
    public function __construct(
        public ?string $periodoDesde = null,
        public ?string $periodoHasta = null,
        public ?string $motivo = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $periodoDesde = $data['periodo_desde'] ?? null;
        $periodoHasta = $data['periodo_hasta'] ?? null;

        if ($periodoDesde instanceof Carbon) {
            $periodoDesde = $periodoDesde->toDateString();
        }
        if ($periodoHasta instanceof Carbon) {
            $periodoHasta = $periodoHasta->toDateString();
        }

        $motivo = $data['motivo'] ?? null;

        return new self(
            periodoDesde: is_string($periodoDesde) ? $periodoDesde : null,
            periodoHasta: is_string($periodoHasta) ? $periodoHasta : null,
            motivo: is_string($motivo) ? $motivo : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'periodo_desde' => $this->periodoDesde,
            'periodo_hasta' => $this->periodoHasta,
            'motivo' => $this->motivo,
        ];
    }
}
