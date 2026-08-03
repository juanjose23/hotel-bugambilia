<?php

declare(strict_types=1);

namespace App\Notifications\Reservas;

use App\Enums\Notifications\TipoNotificacion;
use App\Notifications\DatosNotificacion;
use App\Notifications\Reservas\Contracts\UrlNotificadorInterface;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use Carbon\CarbonInterface;
use Filament\Actions\Action;

final readonly class MensajesReserva
{
    public function __construct(
        private UrlNotificadorInterface $url,
    ) {}

    public function reservaCreada(Reserva $reserva): DatosNotificacion
    {
        return new DatosNotificacion(
            'Reserva Creada',
            "Se ha creado la reserva $reserva->codigo_reserva para $reserva->nombre_cliente.",
            TipoNotificacion::ReservationCreated,
            [Action::make('view')->label('Ver')->url($this->url->reserva($reserva))->button()],
        );
    }

    public function reservaConfirmada(Reserva $reserva): DatosNotificacion
    {
        return new DatosNotificacion(
            'Reserva Confirmada',
            "La reserva $reserva->codigo_reserva de $reserva->nombre_cliente ha sido confirmada.",
            TipoNotificacion::ReservationConfirmed,
            [Action::make('view')->label('Ver')->url($this->url->reserva($reserva))->button()],
        );
    }

    public function reservaCancelada(Reserva $reserva): DatosNotificacion
    {
        return new DatosNotificacion(
            'Reserva Cancelada',
            "La reserva $reserva->codigo_reserva de $reserva->nombre_cliente ha sido cancelada.",
            TipoNotificacion::ReservationCancelled,
            [Action::make('view')->label('Ver')->url($this->url->reserva($reserva))->button()],
        );
    }

    public function recordatorio(Reserva $reserva, CarbonInterface $inicio): DatosNotificacion
    {
        $recurso = $reserva->espacio->nombre ?? $reserva->habitacion->numero ?? 'recurso reservado';

        return new DatosNotificacion(
            'Reserva próxima',
            "La reserva $reserva->codigo_reserva de $reserva->nombre_cliente inicia a las {$inicio->format('h:i A')} en $recurso.",
            TipoNotificacion::ReservationReminder,
            [Action::make('view')->label('Ver reserva')->url($this->url->reserva($reserva))->button()],
        );
    }

    public function checkInRegistrado(Estancia $estancia): DatosNotificacion
    {
        $reserva = $estancia->reserva;
        $codigo = $reserva->codigo_reserva ?? "Estancia #$estancia->id";
        $cliente = $reserva->nombre_cliente ?? '';

        return new DatosNotificacion(
            'Check-In Completado',
            "Se ha registrado el check-in de $cliente (reserva $codigo).",
            TipoNotificacion::CheckInCompleted,
            $reserva ? [Action::make('view')->label('Ver')->url($this->url->reserva($reserva))->button()] : [],
        );
    }

    public function checkOutRegistrado(Estancia $estancia): DatosNotificacion
    {
        $reserva = $estancia->reserva;
        $codigo = $reserva->codigo_reserva ?? "Estancia #$estancia->id";
        $cliente = $reserva->nombre_cliente ?? '';

        return new DatosNotificacion(
            'Check-Out Completado',
            "Se ha registrado el check-out de $cliente (reserva $codigo).",
            TipoNotificacion::CheckOutCompleted,
            $reserva ? [Action::make('view')->label('Ver')->url($this->url->reserva($reserva))->button()] : [],
        );
    }
}
