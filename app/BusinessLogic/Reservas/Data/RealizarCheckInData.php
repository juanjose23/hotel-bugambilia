<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

final readonly class RealizarCheckInData
{
    /**
     * @param  array<int, RegistrarHuespedData>  $huespedes
     */
    public function __construct(
        public int $reservaDetalleId,
        public array $huespedes = [],
        public ?float $depositoOGarantia = null,
        public ?float $limiteCuenta = null,
        public int $cantidadLlaves = 1,
        public ?string $observaciones = null,
        public ?int $usuarioId = null,
    ) {}
}
