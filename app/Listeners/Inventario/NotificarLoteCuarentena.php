<?php

declare(strict_types=1);

namespace App\Listeners\Inventario;

use App\Events\Inventario\LoteEnviadoACuarentena;
use App\Notifications\Inventario\NotificadorInventario;

final class NotificarLoteCuarentena
{
    public function __construct(
        private readonly NotificadorInventario $notificador,
    ) {}

    public function handle(LoteEnviadoACuarentena $event): void
    {
        $this->notificador->loteEnCuarentena($event->lote, $event->motivo);
    }
}
