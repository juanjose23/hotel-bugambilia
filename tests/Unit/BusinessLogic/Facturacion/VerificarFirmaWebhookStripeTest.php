<?php

declare(strict_types=1);

use App\BusinessLogic\Facturacion\Stripe\VerificarFirmaWebhookStripe;

function firmarWebhook(string $payload, string $timestamp, string $secret): string
{
    $firma = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return "t={$timestamp},v1={$firma}";
}

it('acepta una firma válida con timestamp reciente', function (): void {
    $verificar = new VerificarFirmaWebhookStripe;
    $payload = '{"type":"payment_intent.succeeded"}';

    $verificar->verificar(
        payload: $payload,
        firma: firmarWebhook($payload, (string) time(), 'secret-token'),
        secret: 'secret-token',
    );

    expect(true)->toBeTrue();
});

it('rechaza un webhook con timestamp demasiado antiguo', function (): void {
    $verificar = new VerificarFirmaWebhookStripe;
    $payload = '{"type":"payment_intent.succeeded"}';

    $verificar->verificar(
        payload: $payload,
        firma: firmarWebhook($payload, (string) (time() - 3600), 'secret-token'),
        secret: 'secret-token',
    );
})->throws(DomainException::class, 'demasiado antiguo');

it('rechaza un webhook con timestamp futuro', function (): void {
    $verificar = new VerificarFirmaWebhookStripe;
    $payload = '{"type":"payment_intent.succeeded"}';

    $verificar->verificar(
        payload: $payload,
        firma: firmarWebhook($payload, (string) (time() + 3600), 'secret-token'),
        secret: 'secret-token',
    );
})->throws(DomainException::class);

it('rechaza una firma que no coincide con el secret', function (): void {
    $verificar = new VerificarFirmaWebhookStripe;
    $payload = '{"type":"payment_intent.succeeded"}';

    $verificar->verificar(
        payload: $payload,
        firma: firmarWebhook($payload, (string) time(), 'otro-secret'),
        secret: 'secret-token',
    );
})->throws(DomainException::class, 'no coincide');

it('rechaza un webhook sin cabecera de firma', function (): void {
    $verificar = new VerificarFirmaWebhookStripe;

    $verificar->verificar(
        payload: '{"type":"payment_intent.succeeded"}',
        firma: null,
        secret: 'secret-token',
    );
})->throws(DomainException::class, 'no incluye firma');

it('rechaza un webhook sin secret configurado', function (): void {
    $verificar = new VerificarFirmaWebhookStripe;

    $verificar->verificar(
        payload: '{"type":"payment_intent.succeeded"}',
        firma: 't=1,v1=abc',
        secret: null,
    );
})->throws(DomainException::class, 'STRIPE_WEBHOOK_SECRET');
