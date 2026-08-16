<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Enums\Facturacion\EventoWebhookStripe;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Queries\Facturacion\PagoTransaccionQuery;
use App\WebServices\Stripe\StripePaymentIntentClient;
use DomainException;

final readonly class ConfirmarPagoStripeReservaCliente
{
    public function __construct(
        private ResolverReservaPagoStripe $resolverReserva,
        private StripePaymentIntentClient $stripe,
        private ConfirmarPagoStripeReserva $confirmarPago,
        private PagoTransaccionQuery $pagoTransaccionQuery,
    ) {}

    public function ejecutar(int $reservaId, string $codigoReserva, string $paymentIntentId): PagoTransaccion
    {
        $reserva = $this->resolverReserva->ejecutar($reservaId, $codigoReserva);

        $transaccion = $this->pagoTransaccionQuery->porReservaYReferencia($reserva->id, $paymentIntentId);

        if ($transaccion === null) {
            throw new DomainException("No existe una transaccion de pago para el intento {$paymentIntentId}.");
        }

        $intent = $this->stripe->obtenerPaymentIntent($paymentIntentId);
        $status = is_string($intent['status'] ?? null) ? $intent['status'] : null;

        if ($status !== 'succeeded') {
            throw new DomainException("El intento de pago en Stripe no esta completado (estado: {$status}).");
        }

        return $this->confirmarPago->ejecutar($paymentIntentId, [
            'id' => "confirmacion-cliente-{$paymentIntentId}",
            'type' => EventoWebhookStripe::PaymentIntentSucceeded->value,
            'data' => [
                'object' => $intent,
            ],
        ]);
    }
}
