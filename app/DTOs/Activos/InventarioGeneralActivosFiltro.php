<?php

declare(strict_types=1);

namespace App\DTOs\Activos;

readonly class InventarioGeneralActivosFiltro
{
    public function __construct(
        public ?string $estado = null,
        public ?int $productoId = null,
        public ?string $ubicacionTipo = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $estado = $data['estado'] ?? null;
        $productoId = $data['producto_id'] ?? null;
        $ubicacionTipo = $data['ubicacion_tipo'] ?? null;

        return new self(
            estado: is_string($estado) ? $estado : null,
            productoId: is_numeric($productoId) ? (int) $productoId : null,
            ubicacionTipo: is_string($ubicacionTipo) ? $ubicacionTipo : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'estado' => $this->estado,
            'producto_id' => $this->productoId,
            'ubicacion_tipo' => $this->ubicacionTipo,
        ];
    }
}
