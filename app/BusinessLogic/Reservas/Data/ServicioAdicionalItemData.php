<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

final readonly class ServicioAdicionalItemData
{
    public function __construct(
        public int $servicioId,
        public int $cantidad = 1,
        public ?float $precio = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $idRaw = $data['servicio_id'] ?? $data['id'] ?? 0;
        $servicioId = is_numeric($idRaw) ? (int) $idRaw : 0;
        $cantRaw = $data['cantidad'] ?? 1;
        $cantidad = is_numeric($cantRaw) ? max(1, (int) $cantRaw) : 1;
        $precio = isset($data['precio']) && is_numeric($data['precio']) ? (float) $data['precio'] : null;

        return new self(
            servicioId: $servicioId,
            cantidad: $cantidad,
            precio: $precio,
        );
    }
}
