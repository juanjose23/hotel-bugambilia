<?php

namespace App\BusinessLogic\Compras\Data\Solicitudes;

final class ItemSolicitudAprobadoData
{
    public function __construct(
        public int $id,
        public float $cantidadAprobada,
    ) {}

    /**
     * @param array{
     *     id:int|string,
     *     cantidad_aprobada:int|float|string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            cantidadAprobada: (float) $data['cantidad_aprobada'],
        );
    }
}
