<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use DomainException;

final readonly class ResolverReservaPagoStripe
{
    public function ejecutar(int $reservaId, string $codigoReserva): Reserva
    {
        /** @var Reserva $reserva */
        $reserva = Reserva::query()
            ->with(['cuentas', 'moneda'])
            ->where('id', $reservaId)
            ->where('codigo_reserva', $codigoReserva)
            ->firstOrFail();

        if (in_array($reserva->estado, [
            EstadoReserva::PARCIALMENTE_CHECKED_IN,
            EstadoReserva::CHECKED_IN,
            EstadoReserva::PARCIALMENTE_CHECKED_OUT,
        ], true)) {
            throw new DomainException("La reserva #{$codigoReserva} no se encuentra en un estado que permita cobro por pasarela.");
        }

        return $reserva;
    }
}
