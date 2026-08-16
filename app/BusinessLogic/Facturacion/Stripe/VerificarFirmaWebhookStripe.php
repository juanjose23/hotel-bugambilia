<?php

declare(strict_types=1);

namespace App\BusinessLogic\Facturacion\Stripe;

use DomainException;

final readonly class VerificarFirmaWebhookStripe
{
    public function verificar(string $payload, ?string $firma, ?string $secret): void
    {
        if (! is_string($firma) || $firma === '') {
            throw new DomainException('El webhook de Stripe no incluye firma.');
        }

        if (! is_string($secret) || trim($secret) === '') {
            throw new DomainException('Stripe no tiene STRIPE_WEBHOOK_SECRET configurado.');
        }

        $partes = [];
        foreach (explode(',', $firma) as $segmento) {
            [$clave, $valor] = array_pad(explode('=', $segmento, 2), 2, null);
            if (is_string($clave) && is_string($valor)) {
                $partes[$clave][] = $valor;
            }
        }

        $timestamp = $partes['t'][0] ?? null;
        $firmas = $partes['v1'] ?? [];

        if (! is_string($timestamp) || $firmas === []) {
            throw new DomainException('La firma del webhook de Stripe no es valida.');
        }

        if (abs(time() - (int) $timestamp) > 300) {
            throw new DomainException('El webhook de Stripe es demasiado antiguo o esta fuera de tiempo.');
        }

        $firmaEsperada = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach ($firmas as $firmaRecibida) {
            if (hash_equals($firmaEsperada, $firmaRecibida)) {
                return;
            }
        }

        throw new DomainException('La firma del webhook de Stripe no coincide.');
    }
}
