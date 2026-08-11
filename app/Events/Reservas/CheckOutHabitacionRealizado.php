<?php

declare(strict_types=1);

namespace App\Events\Reservas;

use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\ReservaDetalle;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CheckOutHabitacionRealizado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Estancia $estancia,
        public ReservaDetalle $reservaDetalle,
    ) {}
}
