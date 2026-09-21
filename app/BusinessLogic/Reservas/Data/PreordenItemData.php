<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

final readonly class PreordenItemData
{
    public function __construct(
        public int $platoId,
        public int $cantidad = 1,
        public ?float $precioUnitario = null,
        public ?string $observaciones = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $idRaw = $data['plato_id'] ?? $data['id'] ?? 0;
        $platoId = is_numeric($idRaw) ? (int) $idRaw : 0;
        $cantRaw = $data['cantidad'] ?? 1;
        $cantidad = is_numeric($cantRaw) ? max(1, (int) $cantRaw) : 1;

        $precioUnitario = isset($data['precio_unitario']) && is_numeric($data['precio_unitario'])
            ? (float) $data['precio_unitario']
            : (isset($data['precio']) && is_numeric($data['precio']) ? (float) $data['precio'] : null);

        $observaciones = isset($data['observaciones']) && is_string($data['observaciones']) && trim($data['observaciones']) !== ''
            ? trim($data['observaciones'])
            : null;

        return new self(
            platoId: $platoId,
            cantidad: $cantidad,
            precioUnitario: $precioUnitario,
            observaciones: $observaciones,
        );
    }
}
