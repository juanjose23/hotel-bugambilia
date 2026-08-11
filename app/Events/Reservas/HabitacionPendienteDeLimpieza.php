<?php

declare(strict_types=1);

namespace App\Events\Reservas;

use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class HabitacionPendienteDeLimpieza
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Habitacion $habitacion,
        public ?string $motivo = 'Check-out realizado',
    ) {}
}
