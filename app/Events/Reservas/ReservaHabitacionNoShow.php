<?php

declare(strict_types=1);

namespace App\Events\Reservas;

use App\Repository\Models\Reservas\Reserva;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ReservaHabitacionNoShow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Reserva $reserva,
        public ?string $motivo = null,
    ) {}
}
