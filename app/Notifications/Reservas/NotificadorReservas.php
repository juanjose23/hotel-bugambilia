<?php

declare(strict_types=1);

namespace App\Notifications\Reservas;

use App\Notifications\NotificadorBase;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;

final class NotificadorReservas extends NotificadorBase
{
    public function __construct(
        private readonly DestinatariosReserva $destinatarios,
        private readonly MensajesReserva $mensajes,
    ) {}

    public function reservaCreada(Reserva $reserva): void
    {
        $usuarios = $this->destinatarios->obtener($reserva->cliente);
        $this->enviar($usuarios, $this->mensajes->reservaCreada($reserva));
    }

    public function reservaConfirmada(Reserva $reserva): void
    {
        $usuarios = $this->destinatarios->obtener($reserva->cliente);
        $this->enviar($usuarios, $this->mensajes->reservaConfirmada($reserva));
    }

    public function reservaCancelada(Reserva $reserva): void
    {
        $usuarios = $this->destinatarios->obtener($reserva->cliente);
        $this->enviar($usuarios, $this->mensajes->reservaCancelada($reserva));
    }

    public function checkInRegistrado(Estancia $estancia): void
    {
        $usuarios = $this->destinatarios->obtener();
        $this->enviar($usuarios, $this->mensajes->checkInRegistrado($estancia));
    }

    public function checkOutRegistrado(Estancia $estancia): void
    {
        $usuarios = $this->destinatarios->obtener();
        $this->enviar($usuarios, $this->mensajes->checkOutRegistrado($estancia));
    }
}
