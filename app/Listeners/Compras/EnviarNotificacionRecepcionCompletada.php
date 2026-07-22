<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\RecepcionCompletada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionRecepcionCompletada
{
    public function handle(RecepcionCompletada $event): void
    {
        app(NotificadorCompras::class)->recepcionCompletada($event->recepcion);
    }
}
