<?php

declare(strict_types=1);

namespace App\WebServices\Stripe;

use App\Exceptions\StripeApiException;
use DomainException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final readonly class StripePaymentIntentClient
{
    /**
     * @return array<string, mixed>
     */
    public function obtenerPaymentIntent(string $paymentIntentId): array
    {
        $secret = config('services.stripe.secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw new DomainException('Stripe no tiene STRIPE_SECRET configurado.');
        }

        try {
            $response = Http::asForm()
                ->withToken($secret)
                ->timeout(10)
                ->connectTimeout(3)
                ->get("https://api.stripe.com/v1/payment_intents/{$paymentIntentId}")
                ->throw();
        } catch (RequestException $exception) {
            $response = $exception->response;
            $stripeError = $response->json('error');
            $mensaje = is_array($stripeError)
                ? ($stripeError['message'] ?? null)
                : null;

            throw new StripeApiException(
                is_string($mensaje) ? $mensaje : 'Stripe no pudo consultar el intento de pago.',
                [
                    'http_status' => $response->status(),
                    'stripe_type' => is_array($stripeError) ? ($stripeError['type'] ?? null) : null,
                    'stripe_code' => is_array($stripeError) ? ($stripeError['code'] ?? null) : null,
                    'decline_code' => is_array($stripeError) ? ($stripeError['decline_code'] ?? null) : null,
                    'param' => is_array($stripeError) ? ($stripeError['param'] ?? null) : null,
                    'request_id' => $response->header('Request-Id'),
                    'raw' => app()->hasDebugModeEnabled() ? $response->json() : null,
                ],
                $response->status(),
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @param  array<string, string>  $metadata
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, string>  $metadata
     * @return array<string, mixed>
     */
    public function crearPaymentIntent(
        int $montoMenorUnidad,
        string $moneda,
        string $idempotencyKey,
        array $metadata,
        ?string $receiptEmail = null,
        ?string $customerId = null,
        ?string $description = null,
    ): array {
        $secret = config('services.stripe.secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw new DomainException('Stripe no tiene STRIPE_SECRET configurado.');
        }

        $params = array_filter([
            'amount' => $montoMenorUnidad,
            'currency' => mb_strtolower($moneda),
            'automatic_payment_methods' => ['enabled' => 'true'],
            'metadata' => $metadata,
            'receipt_email' => $receiptEmail,
            'customer' => $customerId,
            'description' => $description,
        ], fn ($val) => $val !== null && $val !== '');

        try {
            $response = Http::asForm()
                ->withToken($secret)
                ->withHeaders(['Idempotency-Key' => $idempotencyKey])
                ->timeout(10)
                ->connectTimeout(3)
                ->post('https://api.stripe.com/v1/payment_intents', $params)
                ->throw();
        } catch (RequestException $exception) {
            $response = $exception->response;
            $stripeError = $response->json('error');
            $mensaje = is_array($stripeError)
                ? ($stripeError['message'] ?? null)
                : null;

            throw new StripeApiException(
                is_string($mensaje) ? $mensaje : 'Stripe no pudo crear el intento de pago.',
                [
                    'http_status' => $response->status(),
                    'stripe_type' => is_array($stripeError) ? ($stripeError['type'] ?? null) : null,
                    'stripe_code' => is_array($stripeError) ? ($stripeError['code'] ?? null) : null,
                    'decline_code' => is_array($stripeError) ? ($stripeError['decline_code'] ?? null) : null,
                    'param' => is_array($stripeError) ? ($stripeError['param'] ?? null) : null,
                    'request_id' => $response->header('Request-Id'),
                    'raw' => app()->hasDebugModeEnabled() ? $response->json() : null,
                ],
                $response->status(),
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @param  array<string, string>  $metadata
     * @return array<string, mixed>
     */
    public function crearOBuscarCliente(
        string $email,
        ?string $nombre = null,
        ?string $telefono = null,
        array $metadata = [],
    ): array {
        $secret = config('services.stripe.secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw new DomainException('Stripe no tiene STRIPE_SECRET configurado.');
        }

        try {
            $search = Http::asForm()
                ->withToken($secret)
                ->get('https://api.stripe.com/v1/customers', [
                    'email' => $email,
                    'limit' => 1,
                ]);

            if ($search->successful()) {
                $data = $search->json('data');
                if (is_array($data) && count($data) > 0 && is_array($data[0])) {
                    /** @var array<string, mixed> $firstCustomer */
                    $firstCustomer = $data[0];

                    return $firstCustomer;
                }
            }

            $params = array_filter([
                'email' => $email,
                'name' => $nombre,
                'phone' => $telefono,
                'metadata' => $metadata,
            ], fn ($val) => $val !== null && $val !== '');

            $response = Http::asForm()
                ->withToken($secret)
                ->post('https://api.stripe.com/v1/customers', $params)
                ->throw();

            /** @var array<string, mixed> $newCustomer */
            $newCustomer = $response->json();

            return $newCustomer;
        } catch (RequestException $exception) {
            $response = $exception->response;
            $stripeError = $response->json('error');
            $mensaje = is_array($stripeError) ? ($stripeError['message'] ?? null) : null;

            throw new StripeApiException(
                is_string($mensaje) ? $mensaje : 'Stripe no pudo registrar o buscar al cliente.',
                [
                    'http_status' => $response->status(),
                    'raw' => app()->hasDebugModeEnabled() ? $response->json() : null,
                ],
                $response->status(),
            );
        }
    }

    /**
     * @param  array<string, string>  $metadata
     * @return array<string, mixed>
     */
    public function crearRefund(
        string $paymentIntentId,
        int $montoMenorUnidad,
        string $idempotencyKey,
        string $reason = 'requested_by_customer',
        array $metadata = [],
    ): array {
        $secret = config('services.stripe.secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw new DomainException('Stripe no tiene STRIPE_SECRET configurado.');
        }

        try {
            $response = Http::asForm()
                ->withToken($secret)
                ->withHeaders(['Idempotency-Key' => $idempotencyKey])
                ->timeout(10)
                ->connectTimeout(3)
                ->post('https://api.stripe.com/v1/refunds', [
                    'payment_intent' => $paymentIntentId,
                    'amount' => $montoMenorUnidad,
                    'reason' => $reason,
                    'metadata' => $metadata,
                ])
                ->throw();
        } catch (RequestException $exception) {
            $response = $exception->response;
            $stripeError = $response->json('error');
            $mensaje = is_array($stripeError)
                ? ($stripeError['message'] ?? null)
                : null;

            throw new StripeApiException(
                is_string($mensaje) ? $mensaje : 'Stripe no pudo crear el reembolso.',
                [
                    'http_status' => $response->status(),
                    'stripe_type' => is_array($stripeError) ? ($stripeError['type'] ?? null) : null,
                    'stripe_code' => is_array($stripeError) ? ($stripeError['code'] ?? null) : null,
                    'decline_code' => is_array($stripeError) ? ($stripeError['decline_code'] ?? null) : null,
                    'param' => is_array($stripeError) ? ($stripeError['param'] ?? null) : null,
                    'request_id' => $response->header('Request-Id'),
                    'raw' => app()->hasDebugModeEnabled() ? $response->json() : null,
                ],
                $response->status(),
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function cancelarPaymentIntent(
        string $paymentIntentId,
        string $cancellationReason = 'requested_by_customer',
    ): array {
        $secret = config('services.stripe.secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw new DomainException('Stripe no tiene STRIPE_SECRET configurado.');
        }

        try {
            $response = Http::asForm()
                ->withToken($secret)
                ->timeout(10)
                ->connectTimeout(3)
                ->post("https://api.stripe.com/v1/payment_intents/{$paymentIntentId}/cancel", [
                    'cancellation_reason' => $cancellationReason,
                ])
                ->throw();
        } catch (RequestException $exception) {
            $response = $exception->response;
            $stripeError = $response->json('error');
            $mensaje = is_array($stripeError) ? ($stripeError['message'] ?? null) : null;

            throw new StripeApiException(
                is_string($mensaje) ? $mensaje : 'Stripe no pudo cancelar el intento de pago.',
                [
                    'http_status' => $response->status(),
                    'raw' => app()->hasDebugModeEnabled() ? $response->json() : null,
                ],
                $response->status(),
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return $payload;
    }
}
