<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\RecepcionRechazada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionRecepcionRechazada
{
    public function handle(RecepcionRechazada $event): void
    {
        app(NotificadorCompras::class)->recepcionRechazada($event->recepcion);
    }
}
