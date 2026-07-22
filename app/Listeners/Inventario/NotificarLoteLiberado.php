<?php

declare(strict_types=1);

namespace App\Listeners\Inventario;

use App\Events\Inventario\LoteLiberadoCuarentena;
use App\Notifications\Inventario\NotificadorInventario;

final class NotificarLoteLiberado
{
    public function __construct(
        private readonly NotificadorInventario $notificador,
    ) {}

    public function handle(LoteLiberadoCuarentena $event): void
    {
        $this->notificador->loteLiberado($event->lote);
    }
}
