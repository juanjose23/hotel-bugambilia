<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\RecepcionConDiscrepancia;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionRecepcionConDiscrepancia
{
    public function handle(RecepcionConDiscrepancia $event): void
    {
        app(NotificadorCompras::class)->recepcionConDiscrepancia($event->recepcion);
    }
}
