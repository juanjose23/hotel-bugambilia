<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

enum TipoHuesped: int
{
    case ADULTO = 1;
    case NINO = 2;
    case INFANTE = 3;
}
