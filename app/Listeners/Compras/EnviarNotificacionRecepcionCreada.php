<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\RecepcionCreada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionRecepcionCreada
{
    public function handle(RecepcionCreada $event): void
    {
        app(NotificadorCompras::class)->recepcionCreada($event->recepcion);
    }
}
