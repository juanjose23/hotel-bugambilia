<?php

declare(strict_types=1);

namespace App\Observers\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\SolicitudLimpieza;
use App\Services\Limpieza\NotificadorLimpieza;

class SolicitudLimpiezaObserver
{
    public function creating(SolicitudLimpieza $solicitud): void
    {
        if ($solicitud->creador_id === null && auth()->check()) {
            $solicitud->creador_id = (int) auth()->id();
        }
    }

    public function created(SolicitudLimpieza $solicitud): void
    {
        app(NotificadorLimpieza::class)->nuevaSolicitudLimpieza($solicitud);

        if ($solicitud->estado === 'pendiente') {
            $solicitud->load('limpiable');
            $limpiable = $solicitud->limpiable;
            if ($limpiable instanceof Habitacion) {
                $limpiable->update([
                    'estado' => EstadoHabitacion::SUCIA,
                ]);
            } elseif ($limpiable instanceof Espacio) {
                $limpiable->update([
                    'estado' => EstadoEspacio::Limpieza,
                ]);
            }
        }
    }

    public function updated(SolicitudLimpieza $solicitud): void
    {
        if ($solicitud->wasChanged('personal_id') && $solicitud->personal_id !== null) {
            app(NotificadorLimpieza::class)->personalAsignado($solicitud);
        }
    }
}
