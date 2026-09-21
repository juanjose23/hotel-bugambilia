<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Actions\Facturacion\AsegurarPasarelaDesdeConfig;
use App\Actions\Facturacion\StripeMontoMenorUnidad;
use App\BusinessLogic\Reservas\Validaciones\ValidarPoliticaPagoReserva;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Facturacion\PasarelaCodigo;
use App\Interactors\Facturacion\RegistrarTransaccionPasarela;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Reservas\Reserva;
use App\WebServices\Stripe\StripePaymentIntentClient;
use DomainException;
use Illuminate\Support\Str;
use Throwable;

/**
 * Interactor unificado para crear PaymentIntents en Stripe
 * tanto para cuentas de huéspedes (Folios) como para reservas directas.
 */
final readonly class CrearIntentoPagoStripe
{
    public function __construct(
        private RegistrarTransaccionPasarela $registrarTransaccion,
        private StripePaymentIntentClient $stripe,
        private AsegurarPasarelaDesdeConfig $asegurarPasarela,
        private StripeMontoMenorUnidad $montoMenorUnidad,
        private ValidarPoliticaPagoReserva $politicaPago,
    ) {}

    /**
     * @return array{
     *     client_secret: string,
     *     payment_intent_id: string,
     *     publishable_key: string,
     *     transaccion: PagoTransaccion,
     *     monto: float,
     *     moneda: string
     * }
     */
    public function ejecutarParaCuenta(Cuenta $cuenta, ?float $montoACobrar = null): array
    {
        $cuenta->loadMissing(['moneda', 'cliente.persona.user', 'reserva']);

        if ($cuenta->moneda === null) {
            throw new DomainException('La cuenta no tiene moneda configurada.');
        }

        $saldoActual = (float) $cuenta->saldo;
        $monto = $montoACobrar !== null && $montoACobrar > 0.0 ? round($montoACobrar, 2) : $saldoActual;

        if ($monto <= 0.0) {
            throw new DomainException('El monto a cobrar mediante Stripe debe ser mayor a cero.');
        }

        $reserva = $cuenta->reserva;
        $emailCliente = is_string($reserva?->email_cliente) && trim($reserva->email_cliente) !== ''
            ? trim($reserva->email_cliente)
            : ($cuenta->cliente?->persona?->user?->email);

        $nombreCliente = is_string($reserva?->nombre_cliente) && trim($reserva->nombre_cliente) !== ''
            ? trim($reserva->nombre_cliente)
            : ($cuenta->cliente?->persona?->nombre_completo);

        $telefonoCliente = is_string($reserva?->telefono_cliente) && trim($reserva->telefono_cliente) !== ''
            ? trim($reserva->telefono_cliente)
            : null;

        $monedaCodigo = (string) $cuenta->moneda->codigo;
        $idempotencyKey = 'stripe-cuenta-'.$cuenta->id.'-'.round($monto, 2).'-'.$monedaCodigo.'-'.Str::random(6);

        $metadata = [
            'cuenta_id' => (string) $cuenta->id,
            'numero_cuenta' => (string) $cuenta->numero_cuenta,
            'reserva_id' => $reserva !== null ? (string) $reserva->id : '',
            'tipo_cuenta' => (string) $cuenta->tipo_cuenta->value,
        ];

        return $this->crearIntento(
            monto: $monto,
            monedaCodigo: $monedaCodigo,
            monedaModel: $cuenta->moneda,
            idempotencyKey: $idempotencyKey,
            description: "Pago de cuenta #{$cuenta->numero_cuenta} - Hotel Bugambilias",
            metadata: $metadata,
            emailCliente: $emailCliente,
            nombreCliente: $nombreCliente,
            telefonoCliente: $telefonoCliente,
            cuenta: $cuenta,
            reserva: $reserva,
            requestPayload: [
                'cuenta_id' => $cuenta->id,
                'monto' => $monto,
                'moneda' => $monedaCodigo,
            ],
        );
    }

    /**
     * @return array{
     *     client_secret: string,
     *     payment_intent_id: string,
     *     publishable_key: string,
     *     transaccion: PagoTransaccion,
     *     monto: float,
     *     moneda: string
     * }
     */
    public function ejecutarParaReserva(Reserva $reserva): array
    {
        $reserva->loadMissing('moneda');

        if ($reserva->moneda === null) {
            throw new DomainException('La reserva no tiene moneda configurada.');
        }

        $monto = $this->politicaPago->obtenerMontoFaltantePolitica($reserva);

        if ($monto <= 0) {
            throw new DomainException('La política de pago de esta reserva no requiere cobro en línea.');
        }

        $monedaCodigo = (string) $reserva->moneda->codigo;
        $idempotencyKey = 'stripe-reserva-'.$reserva->id.'-'.$monto.'-'.$monedaCodigo;
        $cuenta = $reserva->cuentas()->latest('id')->first();

        $emailCliente = is_string($reserva->email_cliente) && trim($reserva->email_cliente) !== ''
            ? trim($reserva->email_cliente)
            : null;

        $metadata = [
            'reserva_id' => (string) $reserva->id,
            'codigo_reserva' => (string) $reserva->codigo_reserva,
            'tipo_pago' => (string) $reserva->tipo_pago->value,
        ];

        return $this->crearIntento(
            monto: $monto,
            monedaCodigo: $monedaCodigo,
            monedaModel: $reserva->moneda,
            idempotencyKey: $idempotencyKey,
            description: "Pago de reservación #{$reserva->codigo_reserva}",
            metadata: $metadata,
            emailCliente: $emailCliente,
            nombreCliente: is_string($reserva->nombre_cliente) ? $reserva->nombre_cliente : null,
            telefonoCliente: is_string($reserva->telefono_cliente) ? $reserva->telefono_cliente : null,
            cuenta: $cuenta,
            reserva: $reserva,
            requestPayload: [
                'reserva_id' => $reserva->id,
                'monto' => $monto,
                'moneda' => $monedaCodigo,
                'politica_pago' => $reserva->tipo_pago->value,
            ],
        );
    }

    /**
     * @param  array<string, string>  $metadata
     * @param  array<string, mixed>  $requestPayload
     * @return array{
     *     client_secret: string,
     *     payment_intent_id: string,
     *     publishable_key: string,
     *     transaccion: PagoTransaccion,
     *     monto: float,
     *     moneda: string
     * }
     */
    private function crearIntento(
        float $monto,
        string $monedaCodigo,
        Moneda $monedaModel,
        string $idempotencyKey,
        string $description,
        array $metadata,
        ?string $emailCliente,
        ?string $nombreCliente,
        ?string $telefonoCliente,
        ?Cuenta $cuenta,
        ?Reserva $reserva,
        array $requestPayload,
    ): array {
        $pasarela = $this->asegurarPasarela->ejecutar(PasarelaCodigo::Stripe);

        $customerId = null;
        if ($emailCliente !== null && trim($emailCliente) !== '') {
            try {
                $stripeCustomer = $this->stripe->crearOBuscarCliente(
                    email: trim($emailCliente),
                    nombre: $nombreCliente,
                    telefono: $telefonoCliente,
                    metadata: $metadata,
                );
                $customerId = is_string($stripeCustomer['id'] ?? null) ? $stripeCustomer['id'] : null;
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $intent = $this->stripe->crearPaymentIntent(
            montoMenorUnidad: $this->montoMenorUnidad->ejecutar($monto, $monedaCodigo),
            moneda: $monedaCodigo,
            idempotencyKey: $idempotencyKey,
            metadata: $metadata,
            receiptEmail: $emailCliente,
            customerId: $customerId,
            description: $description,
        );

        $paymentIntentId = $intent['id'] ?? null;
        $clientSecret = $intent['client_secret'] ?? null;
        $publishableKey = config('services.stripe.key');

        if (! is_string($paymentIntentId) || ! is_string($clientSecret)) {
            throw new DomainException('Stripe no devolvió un intento de pago válido.');
        }

        if (! is_string($publishableKey) || trim($publishableKey) === '') {
            throw new DomainException('Stripe no tiene STRIPE_KEY configurado.');
        }

        $transaccion = $this->registrarTransaccion->ejecutar(
            pasarela: $pasarela,
            monto: $monto,
            moneda: $monedaModel,
            idempotencyKey: $idempotencyKey,
            cuenta: $cuenta,
            reserva: $reserva,
            estado: EstadoTransaccionPago::Pendiente,
            referenciaPasarela: $paymentIntentId,
            requestPayload: $requestPayload,
            responsePayload: $intent,
        );

        return [
            'client_secret' => $clientSecret,
            'payment_intent_id' => $paymentIntentId,
            'publishable_key' => $publishableKey,
            'transaccion' => $transaccion,
            'monto' => $monto,
            'moneda' => $monedaCodigo,
        ];
    }
}
