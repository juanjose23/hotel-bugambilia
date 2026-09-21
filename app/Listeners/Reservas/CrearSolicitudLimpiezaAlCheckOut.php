<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\HabitacionPendienteDeLimpieza;
use App\Interactors\Limpieza\Ejecucion\RegistrarSolicitudLimpieza;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class CrearSolicitudLimpiezaAlCheckOut implements ShouldQueue
{
    public function __construct(
        private RegistrarSolicitudLimpieza $registrarSolicitudLimpieza,
    ) {}

    public function handle(HabitacionPendienteDeLimpieza $event): void
    {
        $this->registrarSolicitudLimpieza->execute(
            limpiable: $event->habitacion,
            prioridad: 'alta',
            notas: $event->motivo,
        );
    }
}
