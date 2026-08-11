<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Events\Reservas\HabitacionPendienteDeLimpieza;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Contracts\Queue\ShouldQueue;

final class CrearSolicitudLimpiezaAlCheckOut implements ShouldQueue
{
    public function handle(HabitacionPendienteDeLimpieza $event): void
    {
        SolicitudLimpieza::query()->create([
            'limpiable_type' => $event->habitacion->getMorphClass(),
            'limpiable_id' => $event->habitacion->id,
            'estado' => EstadoLimpieza::Pendiente,
            'notas' => $event->motivo,
            'prioridad' => 'alta',
        ]);
    }
}
