<?php

declare(strict_types=1);

namespace App\Enums\Facturacion;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EventoWebhookStripe: string implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case PaymentIntentSucceeded = 'payment_intent.succeeded';
    case PaymentIntentPaymentFailed = 'payment_intent.payment_failed';
    case PaymentIntentCanceled = 'payment_intent.canceled';
    case ChargeRefunded = 'charge.refunded';
    case RefundCreated = 'refund.created';
    case ChargeRefundUpdated = 'charge.refund.updated';

    public function getLabel(): string
    {
        return match ($this) {
            self::PaymentIntentSucceeded => 'Payment intent confirmado',
            self::PaymentIntentPaymentFailed => 'Pago rechazado',
            self::PaymentIntentCanceled => 'Intento de pago cancelado',
            self::ChargeRefunded => 'Cargo reembolsado',
            self::RefundCreated => 'Reembolso creado',
            self::ChargeRefundUpdated => 'Reembolso actualizado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PaymentIntentSucceeded, self::ChargeRefunded => 'success',
            self::PaymentIntentPaymentFailed, self::PaymentIntentCanceled => 'danger',
            self::RefundCreated, self::ChargeRefundUpdated => 'info',
        };
    }

    public function esDeReembolso(): bool
    {
        return in_array($this, [
            self::ChargeRefunded,
            self::RefundCreated,
            self::ChargeRefundUpdated,
        ], true);
    }

    public function esDeFallo(): bool
    {
        return in_array($this, [
            self::PaymentIntentPaymentFailed,
            self::PaymentIntentCanceled,
        ], true);
    }
}
