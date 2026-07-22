<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\OrdenCancelada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionOrdenCancelada
{
    public function handle(OrdenCancelada $event): void
    {
        app(NotificadorCompras::class)->ordenCancelada($event->orden);
    }
}
