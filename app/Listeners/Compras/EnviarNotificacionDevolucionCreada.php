<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\DevolucionCreada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionDevolucionCreada
{
    public function handle(DevolucionCreada $event): void
    {
        app(NotificadorCompras::class)->devolucionCreada($event->devolucion);
    }
}
