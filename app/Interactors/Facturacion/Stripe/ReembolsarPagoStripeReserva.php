<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Actions\Facturacion\StripeMontoMenorUnidad;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Facturacion\PasarelaCodigo;
use App\Exceptions\StripeApiException;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Facturacion\PagoTransaccionPersistencia;
use App\Repository\Queries\Facturacion\PagoTransaccionQuery;
use App\WebServices\Stripe\StripePaymentIntentClient;

final readonly class ReembolsarPagoStripeReserva
{
    public function __construct(
        private StripePaymentIntentClient $stripe,
        private PagoTransaccionQuery $pagoTransaccionQuery,
        private PagoTransaccionPersistencia $pagoTransaccionPersistencia,
        private StripeMontoMenorUnidad $montoMenorUnidad,
    ) {}

    /**
     * @param  bool  $lanzarSiFallaConexion  Lanza StripeApiException si no se puede conectar en lugar de omitir en silencio.
     * @return list<array{pago_cuenta_id: int|null, pago_transaccion_id: int, monto: float, referencia: string|null}>
     */
    public function ejecutar(
        Reserva $reserva,
        float $montoReembolso,
        string $motivo,
        ?int $usuarioId = null,
        bool $lanzarSiFallaConexion = false,
    ): array {
        if ($montoReembolso <= 0.0) {
            return [];
        }

        $transacciones = $this->pagoTransaccionQuery->porReservaParaReembolso(
            $reserva,
            PasarelaCodigo::Stripe,
            [
                EstadoTransaccionPago::Capturada,
                EstadoTransaccionPago::Autorizada,
                EstadoTransaccionPago::Pendiente,
            ],
            incluirTransaccionesDeCuenta: true,
            referenciasPrefijo: ['pi_', 'ch_'],
        );

        if ($transacciones->isEmpty()) {
            $transacciones = $this->pagoTransaccionQuery->porReservaConReferenciaPasarela($reserva);
        }

        $metaDatos = is_array($reserva->meta_datos) ? $reserva->meta_datos : [];
        $stripeData = is_array($metaDatos['stripe'] ?? null) ? $metaDatos['stripe'] : [];
        $paymentIntentFallback = is_string($stripeData['payment_intent_id'] ?? null)
            ? $stripeData['payment_intent_id']
            : (is_string($metaDatos['payment_intent_id'] ?? null) ? $metaDatos['payment_intent_id'] : null);

        if ($paymentIntentFallback === null) {
            $metaJson = json_encode($metaDatos);
            if (is_string($metaJson) && preg_match('/(pi_[a-zA-Z0-9]+)/', $metaJson, $matches)) {
                $paymentIntentFallback = $matches[1];
            }
        }

        if ($transacciones->isEmpty() && is_string($paymentIntentFallback) && trim($paymentIntentFallback) !== '') {
            $monedaCodigo = $reserva->moneda !== null ? (string) $reserva->moneda->codigo : 'USD';
            $idempotencyKey = 'stripe-refund-reserva-fallback-'.$reserva->id.'-'.round($montoReembolso, 2);

            try {
                $refund = $this->stripe->crearRefund(
                    paymentIntentId: trim($paymentIntentFallback),
                    montoMenorUnidad: $this->montoMenorUnidad->ejecutar($montoReembolso, $monedaCodigo),
                    idempotencyKey: $idempotencyKey,
                    metadata: [
                        'reserva_id' => (string) $reserva->id,
                        'codigo_reserva' => (string) $reserva->codigo_reserva,
                        'motivo' => mb_substr($motivo, 0, 120),
                        'usuario_id' => $usuarioId !== null ? (string) $usuarioId : '',
                    ],
                );

                return [[
                    'pago_cuenta_id' => null,
                    'pago_transaccion_id' => 0,
                    'monto' => round($montoReembolso, 2),
                    'referencia' => is_string($refund['id'] ?? null) ? $refund['id'] : null,
                ]];
            } catch (\Throwable $exception) {
                try {
                    $refund = $this->stripe->cancelarPaymentIntent(trim($paymentIntentFallback));

                    return [[
                        'pago_cuenta_id' => null,
                        'pago_transaccion_id' => 0,
                        'monto' => round($montoReembolso, 2),
                        'referencia' => is_string($refund['id'] ?? null) ? $refund['id'] : null,
                    ]];
                } catch (\Throwable $cancelException) {
                    report($exception);

                    if ($lanzarSiFallaConexion && $exception instanceof StripeApiException) {
                        throw $exception;
                    }

                    return [];
                }
            }
        }

        if ($transacciones->isEmpty()) {
            return [];
        }

        $restante = round($montoReembolso, 2);
        $reembolsos = [];

        /** @var PagoTransaccion $transaccion */
        foreach ($transacciones as $transaccion) {
            if ($restante <= 0.0) {
                break;
            }

            $monedaCodigo = $transaccion->moneda !== null
                ? (string) $transaccion->moneda->codigo
                : ($reserva->moneda !== null ? (string) $reserva->moneda->codigo : 'USD');
            $montoTransaccion = (float) $transaccion->monto;
            $montoAReembolsar = min($restante, $montoTransaccion);
            $paymentIntentId = (string) $transaccion->referencia_pasarela;
            $idempotencyKey = 'stripe-refund-reserva-'.$reserva->id.'-'.$transaccion->id.'-'.$montoAReembolsar;

            try {
                $refund = $this->stripe->crearRefund(
                    paymentIntentId: $paymentIntentId,
                    montoMenorUnidad: $this->montoMenorUnidad->ejecutar($montoAReembolsar, $monedaCodigo),
                    idempotencyKey: $idempotencyKey,
                    metadata: [
                        'reserva_id' => (string) $reserva->id,
                        'codigo_reserva' => (string) $reserva->codigo_reserva,
                        'pago_transaccion_id' => (string) $transaccion->id,
                        'motivo' => mb_substr($motivo, 0, 120),
                        'usuario_id' => $usuarioId !== null ? (string) $usuarioId : '',
                    ],
                );
            } catch (StripeApiException $exception) {
                try {
                    $refund = $this->stripe->cancelarPaymentIntent($paymentIntentId);
                } catch (\Throwable $cancelException) {
                    report($exception);

                    if ($lanzarSiFallaConexion) {
                        throw $exception;
                    }

                    $responsePayload = is_array($transaccion->response_payload) ? $transaccion->response_payload : [];
                    $errors = is_array($responsePayload['stripe_errors'] ?? null) ? $responsePayload['stripe_errors'] : [];
                    $errors[] = [
                        'monto' => round($montoAReembolsar, 2),
                        'error' => $exception->getMessage(),
                        'details' => $exception->details(),
                        'cancel_error' => $cancelException->getMessage(),
                        'registrado_at' => now()->toISOString(),
                    ];
                    $responsePayload['stripe_errors'] = $errors;
                    $this->pagoTransaccionPersistencia->actualizar($transaccion, [
                        'response_payload' => $responsePayload,
                    ]);

                    continue;
                }
            }

            $responsePayload = is_array($transaccion->response_payload) ? $transaccion->response_payload : [];
            $refunds = is_array($responsePayload['refunds'] ?? null) ? $responsePayload['refunds'] : [];
            $refunds[] = [
                'monto' => round($montoAReembolsar, 2),
                'moneda' => $monedaCodigo,
                'stripe_refund' => $refund,
                'idempotency_key' => $idempotencyKey,
                'motivo' => $motivo,
                'usuario_id' => $usuarioId,
                'registrado_at' => now()->toISOString(),
            ];
            $responsePayload['refunds'] = $refunds;

            $datosActualizar = [
                'response_payload' => $responsePayload,
            ];

            if ($montoAReembolsar >= $montoTransaccion) {
                $datosActualizar['estado'] = EstadoTransaccionPago::Reembolsada;
                $datosActualizar['reembolsada_at'] = now();
            }

            $this->pagoTransaccionPersistencia->actualizar($transaccion, $datosActualizar);
            $reembolsos[] = [
                'pago_cuenta_id' => $transaccion->pago_cuenta_id,
                'pago_transaccion_id' => $transaccion->id,
                'monto' => round($montoAReembolsar, 2),
                'referencia' => is_string($refund['id'] ?? null) ? $refund['id'] : null,
            ];
            $restante = round($restante - $montoAReembolsar, 2);
        }

        return $reembolsos;
    }
}
