<?php

declare(strict_types=1);

namespace App\Listeners\Inventario;

use App\Events\Inventario\InventarioFisicoProcesado;
use App\Notifications\Inventario\NotificadorInventario;

final class NotificarInventarioFisico
{
    public function handle(InventarioFisicoProcesado $event): void
    {
        $lote = $event->inventarioFisico->lotes()->first();
        if ($lote !== null) {
            app(NotificadorInventario::class)
                ->loteLiberado($lote);
        }
    }
}
