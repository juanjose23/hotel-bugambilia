<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\BusinessLogic\Facturacion\Stripe\ReintentarOperacionStripe;
use App\BusinessLogic\Reservas\Data\CancelarReservaHabitacionData;
use App\Exceptions\StripeApiException;
use App\Interactors\Reservas\Habitaciones\CancelarReservaHabitacion;
use App\Repository\Models\Reservas\Reserva;

final readonly class CancelarReserva
{
    public function __construct(
        private CancelarReservaHabitacion $cancelarReservaHabitacion,
        private ReintentarOperacionStripe $reintentarOperacionStripe,
    ) {}

    /**
     * @return array{reserva: Reserva, reembolso_pendiente_administracion: bool, intentos_stripe: int}
     */
    public function ejecutar(
        Reserva|CancelarReservaHabitacionData $data,
        ?int $usuarioId = null,
        string $motivo = 'Reserva cancelada',
    ): array {
        $cancelarData = $data instanceof CancelarReservaHabitacionData
            ? $data
            : new CancelarReservaHabitacionData(
                reservaId: $data->id,
                motivo: $motivo,
                usuarioId: $usuarioId,
            );

        $intentosUsados = 0;

        try {
            $reserva = $this->reintentarOperacionStripe->ejecutar(
                fn (): Reserva => $this->cancelarReservaHabitacion->ejecutar(new CancelarReservaHabitacionData(
                    reservaId: $cancelarData->reservaId,
                    motivo: $cancelarData->motivo,
                    montoPenalizacion: $cancelarData->montoPenalizacion,
                    usuarioId: $cancelarData->usuarioId,
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
                reservaId: $cancelarData->reservaId,
                motivo: $cancelarData->motivo,
                montoPenalizacion: $cancelarData->montoPenalizacion,
                usuarioId: $cancelarData->usuarioId,
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
