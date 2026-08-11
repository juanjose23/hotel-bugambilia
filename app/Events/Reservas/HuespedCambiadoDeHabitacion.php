<?php

declare(strict_types=1);

namespace App\Events\Reservas;

use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class HuespedCambiadoDeHabitacion
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Estancia $estancia,
        public Habitacion $habitacionAnterior,
        public Habitacion $habitacionNueva,
        public string $motivo,
    ) {}
}
