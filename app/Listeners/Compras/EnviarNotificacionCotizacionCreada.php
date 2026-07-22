<?php

declare(strict_types=1);

namespace App\Listeners\Compras;

use App\Events\Compras\CotizacionCreada;
use App\Notifications\Compras\NotificadorCompras;

final class EnviarNotificacionCotizacionCreada
{
    public function handle(CotizacionCreada $event): void
    {
        app(NotificadorCompras::class)->cotizacionCreada($event->cotizacion);
    }
}
