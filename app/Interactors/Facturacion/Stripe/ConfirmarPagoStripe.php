<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Facturacion\EstadoIntentoStripe;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Facturacion\EventoWebhookStripe;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Facturacion\ConfirmarPagoPasarela;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Persistencia\Facturacion\PagoTransaccionPersistencia;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Cuentas\ObtenerUltimaCuentaActivaReservaQuery;
use App\Repository\Queries\Facturacion\PagoTransaccionQuery;
use App\WebServices\Stripe\StripePaymentIntentClient;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Interactor unificado para confirmar pagos procesados con Stripe
 * tanto para cuentas de estancia (Check-Out/Folio) como para reservas online.
 */
final readonly class ConfirmarPagoStripe
{
    public function __construct(
        private StripePaymentIntentClient $stripeClient,
        private RegistrarPagoCuenta $registrarPagoCuenta,
        private ConfirmarPagoPasarela $confirmarPagoPasarela,
        private PagoTransaccionQuery $pagoTransaccionQuery,
        private PagoTransaccionPersistencia $pagoTransaccionPersistencia,
        private ObtenerUltimaCuentaActivaReservaQuery $ultimaCuentaActivaReservaQuery,
        private ReservaRepositorioInterface $reservaRepositorio,
        private ResolverReservaPagoStripe $resolverReserva,
    ) {}

    public function ejecutarParaCliente(int $reservaId, string $codigoReserva, string $paymentIntentId): PagoTransaccion
    {
        $reserva = $this->resolverReserva->ejecutar($reservaId, $codigoReserva);

        $transaccion = $this->pagoTransaccionQuery->porReservaYReferencia($reserva->id, $paymentIntentId);

        if ($transaccion === null) {
            throw new DomainException("No existe una transacción de pago para el intento {$paymentIntentId}.");
        }

        $intent = $this->stripeClient->obtenerPaymentIntent($paymentIntentId);
        $status = is_string($intent['status'] ?? null) ? $intent['status'] : null;

        if ($status !== 'succeeded') {
            throw new DomainException("El intento de pago en Stripe no está completado (estado: {$status}).");
        }

        return $this->ejecutarParaReserva($paymentIntentId, [
            'id' => "confirmacion-cliente-{$paymentIntentId}",
            'type' => EventoWebhookStripe::PaymentIntentSucceeded->value,
            'data' => [
                'object' => $intent,
            ],
        ]);
    }

    /**
     * Confirma el pago de Stripe para una Cuenta específica.
     *
     * @return array{
     *     cuenta: Cuenta,
     *     pago: PagoCuenta,
     *     transaccion: PagoTransaccion
     * }
     */
    public function ejecutarParaCuenta(
        Cuenta $cuenta,
        string $paymentIntentId,
        ?int $usuarioId = null,
    ): array {
        return DB::transaction(function () use ($cuenta, $paymentIntentId, $usuarioId): array {
            $intent = $this->stripeClient->obtenerPaymentIntent($paymentIntentId);
            $this->validarEstadoAprobado($intent);

            $monto = $this->calcularMontoDecimal($intent);

            // Registrar pago en la cuenta del huésped
            $pago = $this->registrarPagoCuenta->ejecutar(
                cuenta: $cuenta,
                metodoPago: MetodoPago::TARJETA_CREDITO,
                monto: $monto,
                propina: 0.0,
                estado: EstadoPago::APLICADO,
                referenciaTransaccion: $paymentIntentId,
                observaciones: "Pago aprobado en línea vía Stripe (ID: {$paymentIntentId})",
                usuarioId: $usuarioId,
            );

            // Actualizar transacción de pasarela si existe
            $transaccion = $this->pagoTransaccionQuery->porReferenciaPasarela($paymentIntentId);

            if ($transaccion instanceof PagoTransaccion) {
                $transaccion = $this->pagoTransaccionPersistencia->actualizar($transaccion, [
                    'estado' => EstadoTransaccionPago::Capturada,
                    'pago_cuenta_id' => $pago->id,
                    'cuenta_id' => $cuenta->id,
                    'response_payload' => $intent,
                ]);
            }

            return [
                'cuenta' => $cuenta->refresh(),
                'pago' => $pago,
                'transaccion' => $transaccion instanceof PagoTransaccion ? $transaccion : new PagoTransaccion,
            ];
        });
    }

    /**
     * Confirma el pago de Stripe a partir de un webhook o confirmación de reserva.
     *
     * @param  array<string, mixed>  $webhookPayload
     */
    public function ejecutarParaReserva(string $paymentIntentId, array $webhookPayload = []): PagoTransaccion
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
                throw new DomainException('La transacción de Stripe no está vinculada a una reserva.');
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

            $dataObj = is_array($webhookPayload['data'] ?? null) && is_array($webhookPayload['data']['object'] ?? null)
                ? $webhookPayload['data']['object']
                : $transaccion->response_payload;

            $transaccion = $this->pagoTransaccionPersistencia->actualizar($transaccion, [
                'webhook_payload' => $webhookPayload,
                'response_payload' => $dataObj,
            ]);

            $reserva->refresh();
            $cuenta = $transaccion->cuenta?->refresh();
            $totalPagado = round((float) ($cuenta !== null
                ? $cuenta->total_pagado
                : ((float) $reserva->total_pagado + (float) $transaccion->monto)), 2);
            $saldo = round(max(0.0, (float) $reserva->total - $totalPagado), 2);

            $politicaActual = $reserva->ultimaEntradaBitacora('politica_pago') ?? [];
            $reserva->actualizarOCrearEntradaBitacora('politica_pago', array_merge(
                $politicaActual,
                ['estado' => $saldo <= 0.0 ? 'pagado' : 'abono_capturado'],
            ));

            $stripeActual = $reserva->ultimaEntradaBitacora('stripe') ?? [];
            $reserva->actualizarOCrearEntradaBitacora('stripe', array_merge(
                $stripeActual,
                $this->resumenPaymentIntent($webhookPayload),
            ));

            $this->reservaRepositorio->actualizar($reserva, [
                'total_pagado' => $totalPagado,
                'saldo' => $saldo,
                'tipo_pago' => $saldo <= 0.0 ? TipoPagoReserva::PAGO_COMPLETO : TipoPagoReserva::ABONO_50,
                'estado' => EstadoReserva::CONFIRMADA,
            ]);

            return $transaccion->refresh()->load('reserva');
        });
    }

    /**
     * @param  array<string, mixed>  $intent
     */
    private function validarEstadoAprobado(array $intent): void
    {
        $status = is_string($intent['status'] ?? null) ? $intent['status'] : '';

        $estadosAprobados = [
            EstadoIntentoStripe::Exitoso->value,
            'succeeded',
            'processing',
        ];

        if (! in_array($status, $estadosAprobados, true)) {
            throw new DomainException("El intento de pago no se encuentra aprobado en Stripe (estado actual: {$status}).");
        }
    }

    /**
     * @param  array<string, mixed>  $intent
     */
    private function calcularMontoDecimal(array $intent): float
    {
        $montoMenorUnidad = is_numeric($intent['amount_received'] ?? null) && (int) $intent['amount_received'] > 0
            ? (int) $intent['amount_received']
            : (is_numeric($intent['amount'] ?? null) ? (int) $intent['amount'] : 0);

        $monto = round($montoMenorUnidad / 100.0, 2);

        if ($monto <= 0.0) {
            throw new DomainException('El monto recibido por Stripe es inválido.');
        }

        return $monto;
    }

    /**
     * @param  array<string, mixed>  $webhookPayload
     * @return array<string, mixed>
     */
    private function resumenPaymentIntent(array $webhookPayload): array
    {
        $data = $webhookPayload['data'] ?? null;
        $object = is_array($data) && is_array($data['object'] ?? null) ? $data['object'] : [];

        return array_filter([
            'id' => $object['id'] ?? null,
            'status' => $object['status'] ?? null,
            'amount_received' => $object['amount_received'] ?? null,
            'currency' => $object['currency'] ?? null,
            'customer' => $object['customer'] ?? null,
            'payment_method' => $object['payment_method'] ?? null,
        ], fn ($valor): bool => $valor !== null);
    }
}
