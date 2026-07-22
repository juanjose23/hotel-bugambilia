<?php

declare(strict_types=1);

namespace App\Listeners\Inventario;

use App\Events\Inventario\MermaRegistrada;
use App\Notifications\Inventario\NotificadorInventario;

final class NotificarMerma
{
    public function __construct(
        private readonly NotificadorInventario $notificador,
    ) {}

    public function handle(MermaRegistrada $event): void
    {
        $this->notificador->loteRechazado($event->lote, $event->motivo);
    }
}
