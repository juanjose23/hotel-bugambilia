<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion\Stripe;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use DomainException;

final readonly class ResolverReservaPagoStripe
{
    public function __construct(
        private ReservaRepositorioInterface $reservaRepositorio,
    ) {}

    public function ejecutar(int $reservaId, string $codigoReserva): Reserva
    {
        $reserva = $this->reservaRepositorio->obtenerPorIdYCodigoConCuentasYMoneda($reservaId, $codigoReserva);

        if (in_array($reserva->estado, [
            EstadoReserva::CANCELADA,
            EstadoReserva::NO_SHOW,
        ], true)) {
            throw new DomainException("La reserva #{$codigoReserva} se encuentra cancelada o en no-show y no permite cobros por pasarela.");
        }

        if ((float) $reserva->saldo <= 0.0) {
            throw new DomainException("La reserva #{$codigoReserva} ya no cuenta con saldo pendiente por liquidar.");
        }

        return $reserva;
    }
}
