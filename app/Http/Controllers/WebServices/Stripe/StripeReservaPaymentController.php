<?php

declare(strict_types=1);

namespace App\Http\Controllers\WebServices\Stripe;

use App\BusinessLogic\Facturacion\Stripe\VerificarFirmaWebhookStripe;
use App\Enums\Facturacion\EventoWebhookStripe;
use App\Exceptions\StripeApiException;
use App\Http\Controllers\Controller;
use App\Interactors\Facturacion\Stripe\ConfirmarPagoStripeReserva;
use App\Interactors\Facturacion\Stripe\ConfirmarPagoStripeReservaCliente;
use App\Interactors\Facturacion\Stripe\CrearIntentoPagoStripeReserva;
use App\Interactors\Facturacion\Stripe\MarcarPagoStripeFallido;
use App\Interactors\Facturacion\Stripe\ProcesarReembolsoStripeWebhook;
use App\Interactors\Facturacion\Stripe\ResolverReservaPagoStripe;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class StripeReservaPaymentController extends Controller
{
    public function crearIntento(
        Request $request,
        CrearIntentoPagoStripeReserva $crearIntentoPago,
        ResolverReservaPagoStripe $resolverReserva,
    ): JsonResponse {
        $datos = $request->validate([
            'reserva_id' => ['required', 'integer', 'exists:reservas,id'],
            'codigo_reserva' => ['required', 'string', 'max:80'],
        ]);

        $reserva = $resolverReserva->ejecutar(
            reservaId: (int) $datos['reserva_id'],
            codigoReserva: (string) $datos['codigo_reserva'],
        );

        try {
            $resultado = $crearIntentoPago->ejecutar($reserva);
        } catch (StripeApiException $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo conectar con Stripe. Intente nuevamente.',
                'error' => 'stripe_api_error',
                'details' => $exception->details(),
                'debug' => app()->hasDebugModeEnabled()
                    ? [
                        'exception' => $exception::class,
                        'code' => $exception->getCode(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ]
                    : null,
            ], 502);
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => app()->hasDebugModeEnabled()
                    ? $exception->getMessage()
                    : 'No se pudo preparar la conexion con Stripe.',
            ], 500);
        }

        return response()->json([
            'client_secret' => $resultado['client_secret'],
            'publishable_key' => $resultado['publishable_key'],
            'transaccion_id' => $resultado['transaccion']->id,
            'monto' => $resultado['monto'],
            'moneda' => $resultado['moneda'],
        ]);
    }

    public function confirmarCliente(
        Request $request,
        ConfirmarPagoStripeReservaCliente $confirmarPagoCliente,
    ): JsonResponse {
        $datos = $request->validate([
            'reserva_id' => ['required', 'integer', 'exists:reservas,id'],
            'codigo_reserva' => ['required', 'string', 'max:80'],
            'payment_intent_id' => ['required', 'string', 'max:120'],
        ]);

        try {
            $transaccion = $confirmarPagoCliente->ejecutar(
                reservaId: (int) $datos['reserva_id'],
                codigoReserva: (string) $datos['codigo_reserva'],
                paymentIntentId: (string) $datos['payment_intent_id'],
            );
        } catch (StripeApiException $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo confirmar el pago con Stripe. Intente nuevamente.',
                'error' => 'stripe_api_error',
                'details' => app()->hasDebugModeEnabled() ? $exception->details() : null,
            ], 502);
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $transaccion->loadMissing('reserva', 'cuenta', 'pagoCuenta', 'conciliacion');

        return response()->json([
            'message' => 'Pago confirmado y abonado a la cuenta.',
            'payment_intent' => $datos['payment_intent_id'],
            'lifecycle' => [
                'transaccion_id' => $transaccion->id,
                'transaccion_estado' => $transaccion->estado->getLabel(),
                'cuenta_id' => $transaccion->cuenta?->id,
                'numero_cuenta' => $transaccion->cuenta?->numero_cuenta,
                'pago_cuenta_id' => $transaccion->pagoCuenta?->id,
                'reserva_id' => $transaccion->reserva?->id,
                'codigo_reserva' => $transaccion->reserva?->codigo_reserva,
                'estado_reserva' => $transaccion->reserva?->estado?->getLabel(),
                'total_reserva' => $transaccion->reserva?->total,
                'total_pagado_reserva' => $transaccion->reserva?->total_pagado,
                'saldo_reserva' => $transaccion->reserva?->saldo,
                'conciliacion_estado' => $transaccion->conciliacion?->estado?->getLabel(),
            ],
        ]);
    }

    public function webhook(
        Request $request,
        VerificarFirmaWebhookStripe $verificarFirma,
        ConfirmarPagoStripeReserva $confirmarPago,
        MarcarPagoStripeFallido $marcarPagoFallido,
        ProcesarReembolsoStripeWebhook $procesarReembolso,
    ): JsonResponse {
        $payload = $request->getContent();
        $webhookSecret = config('services.stripe.webhook_secret');
        $webhookSecret = is_string($webhookSecret) ? $webhookSecret : null;

        try {
            $verificarFirma->verificar(
                payload: $payload,
                firma: $request->header('Stripe-Signature'),
                secret: $webhookSecret,
            );
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }

        $evento = json_decode($payload, true);

        if (! is_array($evento)) {
            return response()->json(['message' => 'Webhook de Stripe invalido.'], 400);
        }

        $eventType = is_string($evento['type'] ?? null) ? $evento['type'] : '';
        $tipoEvento = EventoWebhookStripe::tryFrom($eventType);
        $data = $evento['data'] ?? null;
        $object = is_array($data) ? ($data['object'] ?? null) : null;

        if ($tipoEvento?->esDeReembolso() === true) {
            $transaccion = $procesarReembolso->ejecutar($evento);

            return response()->json([
                'received' => true,
                'event' => $eventType,
                'reembolsada' => $transaccion !== null,
                'transaccion_id' => $transaccion?->id,
            ]);
        }

        $paymentIntentId = is_array($object)
            ? ($object['payment_intent'] ?? $object['id'] ?? null)
            : null;

        if (! is_string($paymentIntentId)) {
            return response()->json(['message' => 'PaymentIntent no encontrado en webhook.'], 400);
        }

        if ($tipoEvento !== EventoWebhookStripe::PaymentIntentSucceeded) {
            if ($tipoEvento?->esDeFallo() === true) {
                $marcarPagoFallido->ejecutar($paymentIntentId, $evento);
            }

            return response()->json([
                'received' => true,
                'event' => $eventType,
                'payment_intent' => $paymentIntentId,
            ]);
        }

        try {
            $transaccion = $confirmarPago->ejecutar($paymentIntentId, $evento);
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $transaccion->loadMissing('reserva', 'cuenta', 'pagoCuenta', 'conciliacion');

        return response()->json([
            'received' => true,
            'event' => $eventType,
            'payment_intent' => $paymentIntentId,
            'lifecycle' => [
                'transaccion_id' => $transaccion->id,
                'transaccion_estado' => $transaccion->estado->getLabel(),
                'cuenta_id' => $transaccion->cuenta?->id,
                'numero_cuenta' => $transaccion->cuenta?->numero_cuenta,
                'pago_cuenta_id' => $transaccion->pagoCuenta?->id,
                'reserva_id' => $transaccion->reserva?->id,
                'codigo_reserva' => $transaccion->reserva?->codigo_reserva,
                'estado_reserva' => $transaccion->reserva?->estado?->getLabel(),
                'total_reserva' => $transaccion->reserva?->total,
                'total_pagado_reserva' => $transaccion->reserva?->total_pagado,
                'saldo_reserva' => $transaccion->reserva?->saldo,
                'conciliacion_estado' => $transaccion->conciliacion?->estado?->getLabel(),
            ],
        ]);
    }
}
