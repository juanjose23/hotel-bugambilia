<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

enum EstadoRecursoReservable: int
{
    case ACTIVO = 1;
    case INACTIVO = 2;
    case MANTENIMIENTO = 3;
}
