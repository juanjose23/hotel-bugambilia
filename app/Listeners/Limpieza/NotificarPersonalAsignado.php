<?php

declare(strict_types=1);

namespace App\Listeners\Limpieza;

use App\Events\Limpieza\PersonalLimpiezaAsignado;
use App\Notifications\Limpieza\NotificadorLimpieza;

final class NotificarPersonalAsignado
{
    public function __construct(
        private readonly NotificadorLimpieza $notificador,
    ) {}

    public function handle(PersonalLimpiezaAsignado $event): void
    {
        $this->notificador->personalAsignado($event->solicitud);
    }
}
