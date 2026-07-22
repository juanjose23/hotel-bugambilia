<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\DevolucionConfirmada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionDevolucionConfirmada
{
    public function handle(DevolucionConfirmada $event): void
    {
        app(NotificadorCompras::class)->devolucionConfirmada($event->devolucion);
    }
}
