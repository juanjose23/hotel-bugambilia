<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

enum ControlDisponibilidad: int
{
    case FECHAS = 1;
    case HORARIO = 2;
    case CUPOS = 3;
    case SIN_BLOQUEO = 4;
}
