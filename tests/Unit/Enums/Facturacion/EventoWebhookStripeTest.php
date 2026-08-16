<?php

declare(strict_types=1);

use App\Enums\Facturacion\EventoWebhookStripe;

it('expone los nombres de evento reales de Stripe como valores', function (): void {
    expect(EventoWebhookStripe::PaymentIntentSucceeded->value)->toBe('payment_intent.succeeded')
        ->and(EventoWebhookStripe::PaymentIntentPaymentFailed->value)->toBe('payment_intent.payment_failed')
        ->and(EventoWebhookStripe::PaymentIntentCanceled->value)->toBe('payment_intent.canceled')
        ->and(EventoWebhookStripe::ChargeRefunded->value)->toBe('charge.refunded')
        ->and(EventoWebhookStripe::RefundCreated->value)->toBe('refund.created')
        ->and(EventoWebhookStripe::ChargeRefundUpdated->value)->toBe('charge.refund.updated');
});

it('resuelve el evento desde el string del webhook', function (): void {
    expect(EventoWebhookStripe::tryFrom('payment_intent.succeeded'))
        ->toBe(EventoWebhookStripe::PaymentIntentSucceeded)
        ->and(EventoWebhookStripe::tryFrom('charge.refunded'))
        ->toBe(EventoWebhookStripe::ChargeRefunded)
        ->and(EventoWebhookStripe::tryFrom('evento.desconocido'))->toBeNull();
});

it('clasifica los eventos de reembolso', function (): void {
    expect(EventoWebhookStripe::ChargeRefunded->esDeReembolso())->toBeTrue()
        ->and(EventoWebhookStripe::RefundCreated->esDeReembolso())->toBeTrue()
        ->and(EventoWebhookStripe::ChargeRefundUpdated->esDeReembolso())->toBeTrue()
        ->and(EventoWebhookStripe::PaymentIntentSucceeded->esDeReembolso())->toBeFalse()
        ->and(EventoWebhookStripe::PaymentIntentPaymentFailed->esDeReembolso())->toBeFalse();
});

it('clasifica los eventos de fallo o cancelación', function (): void {
    expect(EventoWebhookStripe::PaymentIntentPaymentFailed->esDeFallo())->toBeTrue()
        ->and(EventoWebhookStripe::PaymentIntentCanceled->esDeFallo())->toBeTrue()
        ->and(EventoWebhookStripe::PaymentIntentSucceeded->esDeFallo())->toBeFalse()
        ->and(EventoWebhookStripe::ChargeRefunded->esDeFallo())->toBeFalse();
});
