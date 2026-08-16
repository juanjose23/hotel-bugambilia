<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Actions\Facturacion\AsegurarPasarelaDesdeConfig;
use App\Actions\Facturacion\StripeMontoMenorUnidad;
use App\BusinessLogic\Reservas\ValidarPoliticaPagoReserva;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Facturacion\PasarelaCodigo;
use App\Interactors\Facturacion\RegistrarTransaccionPasarela;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Reservas\Reserva;
use App\WebServices\Stripe\StripePaymentIntentClient;
use DomainException;

final readonly class CrearIntentoPagoStripeReserva
{
    public function __construct(
        private ValidarPoliticaPagoReserva $politicaPago,
        private RegistrarTransaccionPasarela $registrarTransaccion,
        private StripePaymentIntentClient $stripe,
        private AsegurarPasarelaDesdeConfig $asegurarPasarela,
        private StripeMontoMenorUnidad $montoMenorUnidad,
    ) {}

    /**
     * @return array{client_secret: string, publishable_key: string, transaccion: PagoTransaccion, monto: float, moneda: string}
     */
    public function ejecutar(Reserva $reserva): array
    {
        $reserva->loadMissing('moneda');

        if ($reserva->moneda === null) {
            throw new DomainException('La reserva no tiene moneda configurada.');
        }

        $monto = $this->politicaPago->obtenerMontoFaltantePolitica($reserva);

        if ($monto <= 0) {
            throw new DomainException('La politica de pago de esta reserva no requiere cobro en linea.');
        }

        $pasarela = $this->asegurarPasarela->ejecutar(PasarelaCodigo::Stripe);

        $monedaCodigo = (string) $reserva->moneda->codigo;
        $idempotencyKey = 'stripe-reserva-'.$reserva->id.'-'.$monto.'-'.$monedaCodigo;
        $cuenta = $reserva->cuentas()->latest('id')->first();

        $customerId = null;
        $emailCliente = is_string($reserva->email_cliente) && trim($reserva->email_cliente) !== ''
            ? trim($reserva->email_cliente)
            : null;

        if ($emailCliente !== null) {
            try {
                $stripeCustomer = $this->stripe->crearOBuscarCliente(
                    email: $emailCliente,
                    nombre: is_string($reserva->nombre_cliente) ? $reserva->nombre_cliente : null,
                    telefono: is_string($reserva->telefono_cliente) ? $reserva->telefono_cliente : null,
                    metadata: [
                        'reserva_id' => (string) $reserva->id,
                        'codigo_reserva' => (string) $reserva->codigo_reserva,
                    ],
                );
                $customerId = is_string($stripeCustomer['id'] ?? null) ? $stripeCustomer['id'] : null;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $intent = $this->stripe->crearPaymentIntent(
            montoMenorUnidad: $this->montoMenorUnidad->ejecutar($monto, $monedaCodigo),
            moneda: $monedaCodigo,
            idempotencyKey: $idempotencyKey,
            metadata: [
                'reserva_id' => (string) $reserva->id,
                'codigo_reserva' => (string) $reserva->codigo_reserva,
                'tipo_pago' => (string) $reserva->tipo_pago->value,
            ],
            receiptEmail: $emailCliente,
            customerId: $customerId,
            description: "Pago de reservacion #{$reserva->codigo_reserva}",
        );

        $paymentIntentId = $intent['id'] ?? null;
        $clientSecret = $intent['client_secret'] ?? null;
        $publishableKey = config('services.stripe.key');

        if (! is_string($paymentIntentId) || ! is_string($clientSecret)) {
            throw new DomainException('Stripe no devolvio un intento de pago valido.');
        }

        if (! is_string($publishableKey) || trim($publishableKey) === '') {
            throw new DomainException('Stripe no tiene STRIPE_KEY configurado.');
        }

        $transaccion = $this->registrarTransaccion->ejecutar(
            pasarela: $pasarela,
            monto: $monto,
            moneda: $reserva->moneda,
            idempotencyKey: $idempotencyKey,
            cuenta: $cuenta,
            reserva: $reserva,
            estado: EstadoTransaccionPago::Pendiente,
            referenciaPasarela: $paymentIntentId,
            requestPayload: [
                'reserva_id' => $reserva->id,
                'monto' => $monto,
                'moneda' => $monedaCodigo,
                'politica_pago' => $reserva->tipo_pago->value,
            ],
            responsePayload: $intent,
        );

        return [
            'client_secret' => $clientSecret,
            'publishable_key' => $publishableKey,
            'transaccion' => $transaccion,
            'monto' => $monto,
            'moneda' => $monedaCodigo,
        ];
    }
}
