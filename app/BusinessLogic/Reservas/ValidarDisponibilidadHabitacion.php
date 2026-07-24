<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

final class ValidarDisponibilidadHabitacion
{
    public function estaDisponible(bool $existeConflicto): bool
    {
        return ! $existeConflicto;
    }
}
