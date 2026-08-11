<?php

declare(strict_types=1);

namespace App\Events\Reservas;

use App\Repository\Models\Reservas\Reserva;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ReservaHabitacionConfirmada
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Reserva $reserva,
    ) {}
}
