<?php

declare(strict_types=1);

namespace App\Notifications\Reservas;

use App\Notifications\NotificadorBase;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class NotificadorReservas extends NotificadorBase
{
    public function __construct(
        private readonly DestinatariosReserva $destinatarios,
        private readonly MensajesReserva $mensajes,
    ) {}

    public function reservaCreada(Reserva $reserva): void
    {
        $usuarios = $this->destinatarios->obtener();
        $this->enviar($usuarios, $this->mensajes->reservaCreada($reserva));
    }

    public function reservaConfirmada(Reserva $reserva): void
    {
        $usuarios = $this->destinatarios->obtener();
        $this->enviar($usuarios, $this->mensajes->reservaConfirmada($reserva));
    }

    public function reservaCancelada(Reserva $reserva): void
    {
        $usuarios = $this->destinatarios->obtener();
        $this->enviar($usuarios, $this->mensajes->reservaCancelada($reserva));
    }

    /** @param Collection<int, User> $usuarios */
    public function recordatorio(Reserva $reserva, CarbonInterface $inicio, Collection $usuarios): void
    {
        $this->enviar($usuarios, $this->mensajes->recordatorio($reserva, $inicio));
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

    /** @param Collection<int, User> $usuarios */
    public function reservaNoConfirmadaExpirada(Reserva $reserva, Collection $usuarios): void
    {
        $this->enviar($usuarios, $this->mensajes->reservaNoConfirmadaExpirada($reserva));
    }

    /** @param Collection<int, User> $usuarios */
    public function checkOutProximoExpirar(Estancia $estancia, Collection $usuarios): void
    {
        $this->enviar($usuarios, $this->mensajes->checkOutProximoExpirar($estancia));
    }
}
