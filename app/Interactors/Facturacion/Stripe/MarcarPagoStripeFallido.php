<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Persistencia\Facturacion\PagoTransaccionPersistencia;
use App\Repository\Queries\Facturacion\PagoTransaccionQuery;

final readonly class MarcarPagoStripeFallido
{
    public function __construct(
        private PagoTransaccionQuery $pagoTransaccionQuery,
        private PagoTransaccionPersistencia $pagoTransaccionPersistencia,
    ) {}

    /**
     * @param  array<string, mixed>  $webhookPayload
     */
    public function ejecutar(string $paymentIntentId, array $webhookPayload): ?PagoTransaccion
    {
        $transaccion = $this->pagoTransaccionQuery->porReferenciaPasarela($paymentIntentId);

        if ($transaccion === null) {
            return null;
        }

        $this->pagoTransaccionPersistencia->actualizar($transaccion, [
            'estado' => EstadoTransaccionPago::Fallida,
            'fallida_at' => now(),
            'webhook_payload' => $webhookPayload,
        ]);

        return $transaccion;
    }
}
