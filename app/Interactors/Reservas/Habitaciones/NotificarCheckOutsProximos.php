<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\Notifications\Reservas\NotificadorReservas;
use App\Repository\Queries\Reservas\ObtenerDestinatariosRecordatorioReservaQuery;
use App\Repository\Queries\Reservas\ObtenerEstanciasProximasCheckOutQuery;

final readonly class NotificarCheckOutsProximos
{
    public function __construct(
        private ObtenerDestinatariosRecordatorioReservaQuery $obtenerDestinatarios,
        private NotificadorReservas $notificador,
        private ObtenerEstanciasProximasCheckOutQuery $estanciasProximas,
    ) {}

    public function ejecutar(): int
    {
        $limiteHorizonte = now()->addHours(2);

        $estanciasProximas = $this->estanciasProximas->ejecutar($limiteHorizonte);

        $notificados = 0;

        foreach ($estanciasProximas as $estancia) {
            if ($estancia->reserva === null) {
                continue;
            }

            $usuarios = $this->obtenerDestinatarios->ejecutar($estancia->reserva);
            if ($usuarios->isNotEmpty()) {
                $this->notificador->checkOutProximoExpirar($estancia, $usuarios);
                $notificados++;
            }
        }

        return $notificados;
    }
}
