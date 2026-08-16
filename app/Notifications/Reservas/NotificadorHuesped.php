<?php

declare(strict_types=1);

namespace App\Notifications\Reservas;

use App\Notifications\DatosNotificacion;
use App\Notifications\NotificadorBase;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use Carbon\CarbonInterface;

final class NotificadorHuesped extends NotificadorBase
{
    public function __construct(
        private readonly MensajesHuesped $mensajes,
    ) {}

    public function reservaCreada(Reserva $reserva): void
    {
        $this->enviarAReserva($reserva, $this->mensajes->reservaCreada($reserva));
    }

    public function reservaConfirmada(Reserva $reserva): void
    {
        $this->enviarAReserva($reserva, $this->mensajes->reservaConfirmada($reserva));
    }

    public function reservaCancelada(Reserva $reserva): void
    {
        $this->enviarAReserva($reserva, $this->mensajes->reservaCancelada($reserva));
    }

    public function recordatorio(Reserva $reserva, CarbonInterface $inicio): void
    {
        $this->enviarAReserva($reserva, $this->mensajes->recordatorio($reserva, $inicio));
    }

    public function checkInRegistrado(Estancia $estancia): void
    {
        $reserva = $estancia->reserva;
        if (! $reserva instanceof Reserva) {
            return;
        }

        $this->enviarAReserva($reserva, $this->mensajes->checkInRegistrado($estancia));
    }

    public function checkOutRegistrado(Estancia $estancia): void
    {
        $reserva = $estancia->reserva;
        if (! $reserva instanceof Reserva) {
            return;
        }

        $this->enviarAReserva($reserva, $this->mensajes->checkOutRegistrado($estancia));
    }

    private function enviarAReserva(Reserva $reserva, DatosNotificacion $data): void
    {
        $email = $reserva->email_cliente;
        if (! is_string($email) || trim($email) === '') {
            return;
        }

        $this->enviarCorreoA($email, $data);
    }
}
