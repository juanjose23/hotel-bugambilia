<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Enums\Cuentas\MetodoPago;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Interactors\Facturacion\ConfirmarPagoPasarela;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Persistencia\Facturacion\PagoTransaccionPersistencia;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Cuentas\ObtenerUltimaCuentaActivaReservaQuery;
use App\Repository\Queries\Facturacion\PagoTransaccionQuery;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ConfirmarPagoStripeReserva
{
    public function __construct(
        private ConfirmarPagoPasarela $confirmarPagoPasarela,
        private PagoTransaccionQuery $pagoTransaccionQuery,
        private PagoTransaccionPersistencia $pagoTransaccionPersistencia,
        private ObtenerUltimaCuentaActivaReservaQuery $ultimaCuentaActivaReservaQuery,
        private ReservaRepositorioInterface $reservaRepositorio,
    ) {}

    /**
     * @param  array<string, mixed>  $webhookPayload
     */
    public function ejecutar(string $paymentIntentId, array $webhookPayload): PagoTransaccion
    {
        return DB::transaction(function () use ($paymentIntentId, $webhookPayload): PagoTransaccion {
            $transaccion = $this->pagoTransaccionQuery->porReferenciaPasarelaRequerida(
                $paymentIntentId,
                ['cuenta', 'reserva'],
            );

            if ($transaccion->estado === EstadoTransaccionPago::Capturada) {
                return $transaccion;
            }

            if ($transaccion->reserva === null) {
                throw new DomainException('La transaccion de Stripe no esta vinculada a una reserva.');
            }

            $reserva = $transaccion->reserva;
            if ($transaccion->cuenta === null) {
                $cuenta = $this->ultimaCuentaActivaReservaQuery->ejecutar($reserva);

                if ($cuenta === null) {
                    throw new DomainException('La reserva no tiene una cuenta abierta para abonar el pago de Stripe.');
                }

                $transaccion = $this->pagoTransaccionPersistencia->actualizar($transaccion, [
                    'cuenta_id' => $cuenta->id,
                ]);
            }

            $transaccion = $this->confirmarPagoPasarela->ejecutar(
                transaccion: $transaccion,
                referenciaPasarela: $paymentIntentId,
                webhookPayload: $webhookPayload,
                metodoPago: MetodoPago::TARJETA_CREDITO,
            );

            $transaccion = $this->guardarResultadoStripe($transaccion, $webhookPayload);

            $reserva->refresh();
            $cuenta = $transaccion->cuenta?->refresh();
            $totalPagado = round((float) ($cuenta !== null
                ? $cuenta->total_pagado
                : ((float) $reserva->total_pagado + (float) $transaccion->monto)), 2);
            $saldo = round(max(0.0, (float) $reserva->total - $totalPagado), 2);
            $metaDatos = is_array($reserva->meta_datos) ? $reserva->meta_datos : [];
            $metaDatos['politica_pago'] = array_merge(
                is_array($metaDatos['politica_pago'] ?? null) ? $metaDatos['politica_pago'] : [],
                ['estado' => $saldo <= 0.0 ? 'pagado' : 'abono_capturado'],
            );
            $metaDatos['stripe'] = array_merge(
                is_array($metaDatos['stripe'] ?? null) ? $metaDatos['stripe'] : [],
                $this->resumenPaymentIntent($webhookPayload),
            );

            $this->reservaRepositorio->actualizar($reserva, [
                'total_pagado' => $totalPagado,
                'saldo' => $saldo,
                'tipo_pago' => $saldo <= 0.0 ? TipoPagoReserva::PAGO_COMPLETO : TipoPagoReserva::ABONO_50,
                'estado' => EstadoReserva::CONFIRMADA,
                'meta_datos' => $metaDatos,
            ]);

            return $transaccion->refresh()->load('reserva');
        });
    }

    /**
     * @param  array<string, mixed>  $webhookPayload
     */
    private function guardarResultadoStripe(PagoTransaccion $transaccion, array $webhookPayload): PagoTransaccion
    {
        $payloadActual = is_array($transaccion->response_payload) ? $transaccion->response_payload : [];
        $payloadActual['stripe_confirmacion'] = $this->resumenPaymentIntent($webhookPayload);

        return $this->pagoTransaccionPersistencia->actualizar($transaccion, [
            'response_payload' => $payloadActual,
        ]);
    }

    /**
     * @param  array<string, mixed>  $webhookPayload
     * @return array<string, mixed>
     */
    private function resumenPaymentIntent(array $webhookPayload): array
    {
        $data = $webhookPayload['data'] ?? null;
        $paymentIntent = is_array($data) ? ($data['object'] ?? []) : [];
        $paymentIntent = is_array($paymentIntent) ? $paymentIntent : [];

        return [
            'payment_intent_id' => $this->stringOrNull($paymentIntent['id'] ?? null),
            'status' => $this->stringOrNull($paymentIntent['status'] ?? null),
            'amount' => $this->intOrNull($paymentIntent['amount'] ?? null),
            'amount_received' => $this->intOrNull($paymentIntent['amount_received'] ?? null),
            'currency' => $this->stringOrNull($paymentIntent['currency'] ?? null),
            'latest_charge' => $this->stringOrNull($paymentIntent['latest_charge'] ?? null),
            'payment_method' => $this->stringOrNull($paymentIntent['payment_method'] ?? null),
            'receipt_email' => $this->stringOrNull($paymentIntent['receipt_email'] ?? null),
            'livemode' => is_bool($paymentIntent['livemode'] ?? null) ? $paymentIntent['livemode'] : null,
            'metadata' => is_array($paymentIntent['metadata'] ?? null) ? $paymentIntent['metadata'] : [],
            'webhook_event_id' => $this->stringOrNull($webhookPayload['id'] ?? null),
            'webhook_event_type' => $this->stringOrNull($webhookPayload['type'] ?? null),
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
