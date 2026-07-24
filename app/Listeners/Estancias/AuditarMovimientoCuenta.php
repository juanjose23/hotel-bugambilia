<?php

declare(strict_types=1);

namespace App\Listeners\Estancias;

use App\Enums\Estancias\TipoMovimientoCuenta;
use App\Events\Estancias\MovimientoCuentaRegistrado;
use Illuminate\Support\Facades\Log;

final class AuditarMovimientoCuenta
{
    public function handle(MovimientoCuentaRegistrado $event): void
    {
        /** @var TipoMovimientoCuenta $tipo */
        $tipo = $event->movimiento->tipo;
        Log::info('Movimiento registrado en cuenta de estancia', [
            'cuenta_estancia_id' => $event->movimiento->cuenta_estancia_id,
            'tipo' => $tipo->value,
            'concepto' => $event->movimiento->concepto,
            'monto' => $event->movimiento->monto,
            'usuario_id' => $event->movimiento->usuario_id,
        ]);
    }
}
