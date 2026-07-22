<?php

declare(strict_types=1);

namespace App\Repository\Observers\Limpieza;

use App\Events\Limpieza\PersonalLimpiezaAsignado;
use App\Events\Limpieza\SolicitudLimpiezaCreada;
use App\Repository\Models\Limpieza\SolicitudLimpieza;

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
        event(new SolicitudLimpiezaCreada($solicitud));
    }

    public function updated(SolicitudLimpieza $solicitud): void
    {
        if ($solicitud->wasChanged('personal_id') && $solicitud->personal_id !== null) {
            event(new PersonalLimpiezaAsignado($solicitud));
        }
    }
}
