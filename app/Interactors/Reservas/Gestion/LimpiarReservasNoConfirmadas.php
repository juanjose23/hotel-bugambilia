<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\Notifications\Reservas\NotificadorReservas;
use App\Repository\Queries\Reservas\ObtenerDestinatariosRecordatorioReservaQuery;
use App\Repository\Queries\Reservas\ObtenerReservasPendientesExpiradasQuery;
use Illuminate\Support\Facades\DB;

final readonly class LimpiarReservasNoConfirmadas
{
    public function __construct(
        private CancelarReserva $cancelarReserva,
        private ObtenerDestinatariosRecordatorioReservaQuery $obtenerDestinatarios,
        private NotificadorReservas $notificador,
        private ObtenerReservasPendientesExpiradasQuery $pendientesExpiradas,
    ) {}

    public function ejecutar(): int
    {
        $hoy = now()->startOfDay();

        $pendientesExpiradas = $this->pendientesExpiradas->ejecutar($hoy, now());

        $procesadas = 0;

        foreach ($pendientesExpiradas as $reserva) {
            DB::transaction(function () use ($reserva): void {
                $this->cancelarReserva->ejecutar($reserva, null, 'Cancelación automática por tiempo de confirmación expirado');

                $usuarios = $this->obtenerDestinatarios->ejecutar($reserva);
                if ($usuarios->isNotEmpty()) {
                    $this->notificador->reservaNoConfirmadaExpirada($reserva, $usuarios);
                }
            });

            $procesadas++;
        }

        return $procesadas;
    }
}
