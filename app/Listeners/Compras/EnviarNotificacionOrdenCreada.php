<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\OrdenCreada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionOrdenCreada
{
    public function handle(OrdenCreada $event): void
    {
        app(NotificadorCompras::class)->ordenCreada($event->orden);
    }
}
