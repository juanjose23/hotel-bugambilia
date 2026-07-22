<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\GanadorSeleccionado;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionGanadorSeleccionado
{
    public function handle(GanadorSeleccionado $event): void
    {
        app(NotificadorCompras::class)->ganadorSeleccionado($event->cotizacion);
    }
}
