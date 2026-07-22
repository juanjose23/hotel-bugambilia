<?php

declare(strict_types=1);

namespace App\Listeners\Limpieza;

use App\Events\Limpieza\FaltanteReposicionDetectado;
use App\Notifications\Limpieza\NotificadorLimpieza;

final class NotificarFaltanteReposicion
{
    public function __construct(
        private readonly NotificadorLimpieza $notificador,
    ) {}

    public function handle(FaltanteReposicionDetectado $event): void
    {
        $this->notificador->faltanteReposicion(
            $event->ejecucion,
            $event->items,
            $event->destinatario,
        );
    }
}
