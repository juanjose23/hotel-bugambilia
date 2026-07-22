<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\OrdenEmitida;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionOrdenEmitida
{
    public function handle(OrdenEmitida $event): void
    {
        app(NotificadorCompras::class)->ordenEmitida($event->orden);
    }
}
