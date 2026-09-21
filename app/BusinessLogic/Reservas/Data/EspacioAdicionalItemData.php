<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

final readonly class EspacioAdicionalItemData
{
    public function __construct(
        public int $espacioId,
        public int $cantidad = 1,
        public ?float $precio = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $idRaw = $data['espacio_id'] ?? $data['id'] ?? 0;
        $espacioId = is_numeric($idRaw) ? (int) $idRaw : 0;
        $cantRaw = $data['cantidad'] ?? 1;
        $cantidad = is_numeric($cantRaw) ? max(1, (int) $cantRaw) : 1;
        $precio = isset($data['precio']) && is_numeric($data['precio']) ? (float) $data['precio'] : null;

        return new self(
            espacioId: $espacioId,
            cantidad: $cantidad,
            precio: $precio,
        );
    }
}
