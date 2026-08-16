<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Interactors\Cuentas\Cobros\ReembolsarPagoCuenta;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Persistencia\Facturacion\PagoTransaccionPersistencia;
use App\Repository\Queries\Facturacion\PagoTransaccionQuery;
use Illuminate\Support\Facades\DB;

final readonly class ProcesarReembolsoStripeWebhook
{
    public function __construct(
        private ReembolsarPagoCuenta $reembolsarPagoCuenta,
        private PagoTransaccionQuery $pagoTransaccionQuery,
        private PagoTransaccionPersistencia $pagoTransaccionPersistencia,
    ) {}

    /**
     * @param  array<string, mixed>  $webhookPayload
     */
    public function ejecutar(array $webhookPayload): ?PagoTransaccion
    {
        $data = $webhookPayload['data'] ?? null;
        $object = is_array($data) ? ($data['object'] ?? null) : null;
        if (! is_array($object)) {
            return null;
        }

        $paymentIntentId = null;

        if (is_string($object['payment_intent'] ?? null)) {
            $paymentIntentId = (string) $object['payment_intent'];
        } elseif (is_string($object['id'] ?? null) && str_starts_with((string) $object['id'], 'pi_')) {
            $paymentIntentId = (string) $object['id'];
        } elseif (is_string($object['charge'] ?? null)) {
            $chargeId = (string) $object['charge'];
            $transaccionCharge = $this->pagoTransaccionQuery->porReferenciaOPayload($chargeId);
            if ($transaccionCharge !== null) {
                $paymentIntentId = (string) $transaccionCharge->referencia_pasarela;
            }
        }

        if ($paymentIntentId === null && is_string($object['id'] ?? null)) {
            $refundId = (string) $object['id'];
            $transaccionEnc = $this->pagoTransaccionQuery->porPayloadConteniendo($refundId);
            if ($transaccionEnc !== null) {
                $paymentIntentId = (string) $transaccionEnc->referencia_pasarela;
            }
        }

        if ($paymentIntentId === null) {
            return null;
        }

        return DB::transaction(function () use ($paymentIntentId, $webhookPayload, $object): ?PagoTransaccion {
            $transaccion = $this->pagoTransaccionQuery->porReferenciaPasarela(
                $paymentIntentId,
                ['cuenta', 'reserva'],
                lock: true,
            );

            if ($transaccion === null) {
                return null;
            }

            $montoReembolsado = 0.0;
            if (isset($object['amount_refunded']) && is_numeric($object['amount_refunded'])) {
                $montoReembolsado = (float) $object['amount_refunded'] / 100;
            } elseif (isset($object['amount']) && is_numeric($object['amount'])) {
                $montoReembolsado = (float) $object['amount'] / 100;
            } else {
                $montoReembolsado = (float) $transaccion->monto;
            }

            $montoTransaccion = (float) $transaccion->monto;

            $payloadActual = is_array($transaccion->response_payload) ? $transaccion->response_payload : [];
            $refunds = is_array($payloadActual['refunds'] ?? null) ? $payloadActual['refunds'] : [];

            $refundEventId = is_string($webhookPayload['id'] ?? null) ? $webhookPayload['id'] : null;
            $yaRegistrado = false;
            foreach ($refunds as $ref) {
                if (is_array($ref) && ($ref['webhook_event_id'] ?? null) === $refundEventId && $refundEventId !== null) {
                    $yaRegistrado = true;
                    break;
                }
            }

            if (! $yaRegistrado) {
                $refunds[] = [
                    'monto' => $montoReembolsado,
                    'stripe_event' => $webhookPayload['type'] ?? null,
                    'webhook_event_id' => $refundEventId,
                    'object' => $object,
                    'registrado_at' => now()->toISOString(),
                ];
                $payloadActual['refunds'] = $refunds;
            }

            $datosActualizar = [
                'response_payload' => $payloadActual,
                'webhook_payload' => $webhookPayload,
            ];

            if ($montoReembolsado >= $montoTransaccion) {
                $datosActualizar['estado'] = EstadoTransaccionPago::Reembolsada;
                $datosActualizar['reembolsada_at'] = now();
            }

            $transaccion = $this->pagoTransaccionPersistencia->actualizar($transaccion, $datosActualizar);

            if ($transaccion->cuenta !== null && $montoReembolsado > 0.0) {
                $pagoCuentaId = $transaccion->pago_cuenta_id !== null ? [(int) $transaccion->pago_cuenta_id] : null;
                $this->reembolsarPagoCuenta->ejecutar(
                    cuenta: $transaccion->cuenta,
                    montoReembolso: min($montoReembolsado, (float) $transaccion->monto),
                    motivo: 'Reembolso procesado por Webhook de Stripe',
                    pagoCuentaIds: $pagoCuentaId,
                );
            }

            return $transaccion->refresh();
        });
    }
}
