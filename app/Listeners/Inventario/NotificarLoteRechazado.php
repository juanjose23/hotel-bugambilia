<?php

declare(strict_types=1);

namespace App\Listeners\Inventario;

use App\Events\Inventario\LoteRechazadoCuarentena;
use App\Notifications\Inventario\NotificadorInventario;

final class NotificarLoteRechazado
{
    public function __construct(
        private readonly NotificadorInventario $notificador,
    ) {}

    public function handle(LoteRechazadoCuarentena $event): void
    {
        $this->notificador->loteRechazado($event->lote, $event->motivo);
    }
}
