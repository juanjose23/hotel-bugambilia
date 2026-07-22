<?php

declare(strict_types=1);

namespace App\Listeners\Inventario;

use App\Events\Inventario\LoteTrasladado;
use App\Notifications\Inventario\NotificadorInventario;

final class NotificarTraslado
{
    public function __construct(
        private readonly NotificadorInventario $notificador,
    ) {}

    public function handle(LoteTrasladado $event): void
    {
        $this->notificador->loteLiberado($event->lote);
    }
}
