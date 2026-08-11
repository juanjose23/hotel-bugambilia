<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

final readonly class ConfirmarReservaHabitacionData
{
    public function __construct(
        public int $reservaId,
        public ?string $observaciones = null,
        public ?int $usuarioId = null,
        public ?float $montoGarantia = null,
    ) {}
}
