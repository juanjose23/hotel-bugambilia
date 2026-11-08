<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Actions\Facturacion\AsegurarPasarelaDesdeConfig;
use App\Actions\Facturacion\StripeMontoMenorUnidad;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Facturacion\PasarelaCodigo;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\WebServices\Stripe\StripePaymentIntentClient;
use DomainException;
use Illuminate\Support\Str;
use Throwable;

/**
 * Interactor para procesar cobros de tarjetas directamente en Stripe (Virtual Terminal)
 * y acreditarlos en la cuenta/folio del huésped.
 */
final readonly class CobrarCuentaConTarjetaStripe
{
    public function __construct(
        private StripePaymentIntentClient $stripeClient,
        private StripeMontoMenorUnidad $montoMenorUnidad,
        private AsegurarPasarelaDesdeConfig $asegurarPasarela,
        private RegistrarPagoCuenta $registrarPagoCuenta,
    ) {}

    /**
     * @param  array{
     *     numero: string,
     *     exp_mes: int|string,
     *     exp_anio: int|string,
     *     cvc: string,
     *     titular?: ?string,
     * }  $datosTarjeta
     * @return array{
     *     pago: PagoCuenta,
     *     payment_intent_id: string,
     *     ultimos4: string,
     *     marca: string,
     *     monto: float
     * }
     */
    public function ejecutar(
        Cuenta $cuenta,
        float $monto,
        array $datosTarjeta,
        ?int $usuarioId = null,
    ): array {
        if ($monto <= 0.0) {
            throw new DomainException('El monto a cobrar mediante Stripe debe ser mayor a cero.');
        }

        $cuenta->loadMissing(['moneda', 'cliente.persona', 'reserva']);

        if ($cuenta->moneda === null) {
            throw new DomainException('La cuenta no tiene moneda configurada.');
        }

        $numeroLimpio = preg_replace('/\D/', '', $datosTarjeta['numero']) ?? '';
        if (strlen($numeroLimpio) < 13 || strlen($numeroLimpio) > 19) {
            throw new DomainException('El número de tarjeta debe tener entre 13 y 19 dígitos.');
        }

        $cvcLimpio = preg_replace('/\D/', '', $datosTarjeta['cvc']) ?? '';
        if (strlen($cvcLimpio) < 3 || strlen($cvcLimpio) > 4) {
            throw new DomainException('El código de seguridad (CVC) debe tener 3 o 4 dígitos.');
        }

        $ultimos4 = substr($numeroLimpio, -4);
        $titular = is_string($datosTarjeta['titular'] ?? null) && trim($datosTarjeta['titular']) !== ''
            ? trim($datosTarjeta['titular'])
            : ($cuenta->reserva->nombre_cliente ?? $cuenta->cliente?->persona->nombre_completo ?? 'Cliente');

        $monedaCodigo = (string) $cuenta->moneda->codigo;
        $montoMenor = $this->montoMenorUnidad->ejecutar($monto, $monedaCodigo);

        $idempotencyKey = 'stripe-terminal-'.$cuenta->id.'-'.round($monto, 2).'-'.$monedaCodigo.'-'.Str::random(8);

        $metadata = [
            'cuenta_id' => (string) $cuenta->id,
            'numero_cuenta' => (string) $cuenta->numero_cuenta,
            'reserva_id' => $cuenta->reserva !== null ? (string) $cuenta->reserva->id : '',
            'titular' => $titular,
            'origen' => 'Virtual Terminal Recepcion Check-Out',
        ];

        // Asegurar que la pasarela Stripe esté registrada en base de datos
        $this->asegurarPasarela->ejecutar(PasarelaCodigo::Stripe);

        $paymentIntentId = 'pi_sim_'.Str::lower(Str::random(24));
        $marca = 'Tarjeta';

        try {
            $resultadoStripe = $this->stripeClient->cobrarConTarjetaDirecta(
                montoMenorUnidad: $montoMenor,
                moneda: $monedaCodigo,
                tarjeta: [
                    'numero' => $numeroLimpio,
                    'exp_mes' => $datosTarjeta['exp_mes'],
                    'exp_anio' => $datosTarjeta['exp_anio'],
                    'cvc' => $cvcLimpio,
                    'titular' => $titular,
                ],
                idempotencyKey: $idempotencyKey,
                metadata: $metadata,
                receiptEmail: $cuenta->reserva?->email_cliente,
                description: "Cobro Terminal Check-Out cuenta #{$cuenta->numero_cuenta} - Hotel Bugambilias",
            );

            if (isset($resultadoStripe['id']) && is_string($resultadoStripe['id'])) {
                $paymentIntentId = $resultadoStripe['id'];
            }

            // Extraer marca si viene disponible en el objeto
            $charges = $resultadoStripe['charges'] ?? null;
            if (is_array($charges)) {
                $dataList = $charges['data'] ?? null;
                if (is_array($dataList) && isset($dataList[0]) && is_array($dataList[0])) {
                    $firstCharge = $dataList[0];
                    $pmDetails = $firstCharge['payment_method_details'] ?? null;
                    if (is_array($pmDetails)) {
                        $cardDetails = $pmDetails['card'] ?? null;
                        if (is_array($cardDetails) && isset($cardDetails['brand']) && is_string($cardDetails['brand'])) {
                            $marca = $cardDetails['brand'];
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            // Si el error proviene de Stripe con tarjeta declinada o datos inválidos, propagamos el mensaje exacto
            if (str_contains(strtolower($e->getMessage()), 'card') || str_contains(strtolower($e->getMessage()), 'decline') || str_contains(strtolower($e->getMessage()), 'stripe')) {
                throw new DomainException("Error de pasarela Stripe: {$e->getMessage()}");
            }

            // Fallback para entornos locales de prueba sin conexión externa
            if (! app()->isProduction()) {
                report($e);
            } else {
                throw $e;
            }
        }

        // Registrar el abono contable en la cuenta del huésped
        $pago = $this->registrarPagoCuenta->ejecutar(
            cuenta: $cuenta,
            metodoPago: MetodoPago::TARJETA_CREDITO,
            monto: $monto,
            referenciaTransaccion: $paymentIntentId,
            observaciones: "Cobro Stripe Terminal (•••• {$ultimos4}, Titular: {$titular})",
            usuarioId: $usuarioId,
        );

        return [
            'pago' => $pago,
            'payment_intent_id' => $paymentIntentId,
            'ultimos4' => $ultimos4,
            'marca' => ucfirst($marca),
            'monto' => $monto,
        ];
    }
}
