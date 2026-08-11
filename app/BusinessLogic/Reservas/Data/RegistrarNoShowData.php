<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

final readonly class RegistrarNoShowData
{
    public function __construct(
        public int $reservaId,
        public ?string $motivo = null,
        public ?float $cargoNoShow = null,
        public ?int $usuarioId = null,
    ) {}
}
