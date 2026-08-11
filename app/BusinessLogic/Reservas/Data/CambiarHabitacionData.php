<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

final readonly class CambiarHabitacionData
{
    public function __construct(
        public int $estanciaId,
        public int $nuevaHabitacionId,
        public int $nuevoRecursoReservableId,
        public string $motivo,
        public ?float $diferenciaTarifa = null,
        public ?int $usuarioId = null,
    ) {}
}
