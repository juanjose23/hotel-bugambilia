<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Reservas\Reserva;
use ArrayAccess;

/**
 * @implements ArrayAccess<string, mixed>
 */
final readonly class ReservaPasarelaResultado implements ArrayAccess
{
    /**
     * @param  array{client_secret: string, publishable_key: string, transaccion: PagoTransaccion, monto: float, moneda: string}|null  $stripePago
     */
    public function __construct(
        public Reserva $reserva,
        public bool $requierePagoStripe,
        public ?array $stripePago = null,
    ) {}

    public function offsetExists(mixed $offset): bool
    {
        return in_array($offset, ['reserva', 'requiere_pago_stripe', 'stripe_pago'], true);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            'reserva' => $this->reserva,
            'requiere_pago_stripe' => $this->requierePagoStripe,
            'stripe_pago' => $this->stripePago,
            default => null,
        };
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Inmutable
    }

    public function offsetUnset(mixed $offset): void
    {
        // Inmutable
    }
}
