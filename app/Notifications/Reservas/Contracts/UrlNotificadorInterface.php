<?php

declare(strict_types=1);

namespace App\Notifications\Reservas\Contracts;

use App\Repository\Models\Reservas\Reserva;

interface UrlNotificadorInterface
{
    public function reserva(Reserva $reserva): string;
}
