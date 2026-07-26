<?php

declare(strict_types=1);

namespace App\Listeners\Cuentas;

use App\Events\Cuentas\CuentaCerrada;
use Illuminate\Support\Facades\Log;

/**
 * Audita el cierre de cualquier cuenta del hotel.
 * Migrado desde App\Listeners\Estancias\AuditarCierreCuenta.
 */
final class AuditarCierreCuenta
{
    public function handle(CuentaCerrada $event): void
    {
        Log::info('Cuenta cerrada', [
            'cuenta_id' => $event->cuenta->id,
            'numero_cuenta' => $event->cuenta->numero_cuenta,
            'tipo_cuenta' => $event->cuenta->tipo_cuenta->getLabel(),
            'estancia_id' => $event->cuenta->estancia_id,
            'reserva_id' => $event->cuenta->reserva_id,
            'total' => $event->cuenta->total,
            'saldo_final' => $event->cuenta->saldo,
            'cerrada_por' => $event->cuenta->cerrada_por,
            'cerrada_at' => $event->cuenta->cerrada_at,
        ]);
    }
}
