<?php

declare(strict_types=1);

namespace App\Listeners\Estancias;

use App\Events\Estancias\CuentaEstanciaCerrada;
use Illuminate\Support\Facades\Log;

final class AuditarCierreCuenta
{
    public function handle(CuentaEstanciaCerrada $event): void
    {
        Log::info('Cuenta de estancia cerrada', [
            'cuenta_estancia_id' => $event->cuenta->id,
            'estancia_id' => $event->cuenta->estancia_id,
            'numero_folio' => $event->cuenta->numero_folio,
            'saldo_final' => $event->cuenta->saldo,
            'cerrada_por' => $event->cuenta->cerrada_por,
        ]);
    }
}
