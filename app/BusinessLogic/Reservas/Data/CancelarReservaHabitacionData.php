<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

final readonly class CancelarReservaHabitacionData
{
    public function __construct(
        public int $reservaId,
        public string $motivo,
        public ?float $montoPenalizacion = null,
        public ?int $usuarioId = null,
        public bool $reembolsoStripeEstricto = false,
        public bool $marcarReembolsoPendiente = false,
    ) {}
}
