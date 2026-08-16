<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\BusinessLogic\Facturacion\Stripe\ReintentarOperacionStripe;
use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Exceptions\StripeApiException;
use App\Interactors\Reservas\Habitaciones\CancelarReservaHabitacion;
use App\Repository\Models\Reservas\Reserva;

final readonly class CancelarReservaPublica
{
    public function __construct(
        private CancelarReservaHabitacion $cancelarReservaHabitacion,
        private ReintentarOperacionStripe $reintentarOperacionStripe,
    ) {}

    /**
     * Cancela la reserva esperando el reembolso de Stripe.
     *
     * Si Stripe no responde tras los reintentos, la reserva se cancela igual
     * y el reembolso queda marcado como pendiente de gestión administrativa.
     *
     * @return array{reserva: Reserva, reembolso_pendiente_administracion: bool, intentos_stripe: int}
     */
    public function ejecutar(CancelarReservaHabitacionData $data): array
    {
        $intentosUsados = 0;

        try {
            $reserva = $this->reintentarOperacionStripe->ejecutar(
                fn (): Reserva => $this->cancelarReservaHabitacion->ejecutar(new CancelarReservaHabitacionData(
                    reservaId: $data->reservaId,
                    motivo: $data->motivo,
                    montoPenalizacion: $data->montoPenalizacion,
                    usuarioId: $data->usuarioId,
                    reembolsoStripeEstricto: true,
                )),
                $intentosUsados,
            );

            return [
                'reserva' => $reserva,
                'reembolso_pendiente_administracion' => false,
                'intentos_stripe' => $intentosUsados,
            ];
        } catch (StripeApiException $exception) {
            report($exception);

            $reserva = $this->cancelarReservaHabitacion->ejecutar(new CancelarReservaHabitacionData(
                reservaId: $data->reservaId,
                motivo: $data->motivo,
                montoPenalizacion: $data->montoPenalizacion,
                usuarioId: $data->usuarioId,
                marcarReembolsoPendiente: true,
            ));

            return [
                'reserva' => $reserva,
                'reembolso_pendiente_administracion' => true,
                'intentos_stripe' => $intentosUsados,
            ];
        }
    }
}
