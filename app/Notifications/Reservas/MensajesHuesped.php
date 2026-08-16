<?php

declare(strict_types=1);

namespace App\Notifications\Reservas;

use App\Enums\Notifications\TipoNotificacion;
use App\Notifications\DatosNotificacion;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\Reserva;
use Carbon\CarbonInterface;

final readonly class MensajesHuesped
{
    public function reservaCreada(Reserva $reserva): DatosNotificacion
    {
        return new DatosNotificacion(
            'Reserva registrada',
            $this->confirmacionCuerpo($reserva, 'hemos registrado tu reserva'),
            TipoNotificacion::ReservationCreated,
        );
    }

    public function reservaConfirmada(Reserva $reserva): DatosNotificacion
    {
        return new DatosNotificacion(
            'Tu reserva está confirmada',
            $this->confirmacionCuerpo($reserva, 'tu reserva ha sido confirmada'),
            TipoNotificacion::ReservationConfirmed,
        );
    }

    public function reservaCancelada(Reserva $reserva): DatosNotificacion
    {
        return new DatosNotificacion(
            'Reserva cancelada',
            "Hola {$reserva->nombre_cliente}, tu reserva {$reserva->codigo_reserva} ha sido cancelada. Si tienes dudas, contáctanos.",
            TipoNotificacion::ReservationCancelled,
        );
    }

    public function recordatorio(Reserva $reserva, CarbonInterface $inicio): DatosNotificacion
    {
        return new DatosNotificacion(
            'Tu reserva inicia pronto',
            "Hola {$reserva->nombre_cliente}, tu reserva {$reserva->codigo_reserva} inicia el {$inicio->format('d/m/Y')} a las {$inicio->format('h:i A')} en {$this->recurso($reserva)}.",
            TipoNotificacion::ReservationReminder,
        );
    }

    public function checkInRegistrado(Estancia $estancia): DatosNotificacion
    {
        return new DatosNotificacion(
            'Check-in completado',
            $this->estanciaCuerpo($estancia, 'tu check-in ha sido registrado. ¡Bienvenido!'),
            TipoNotificacion::CheckInCompleted,
        );
    }

    public function checkOutRegistrado(Estancia $estancia): DatosNotificacion
    {
        return new DatosNotificacion(
            'Check-out completado',
            $this->estanciaCuerpo($estancia, 'tu check-out ha sido registrado. ¡Gracias por hospedarte con nosotros!'),
            TipoNotificacion::CheckOutCompleted,
        );
    }

    private function confirmacionCuerpo(Reserva $reserva, string $verbo): string
    {
        $cuerpo = "Hola {$reserva->nombre_cliente}, {$verbo} {$reserva->codigo_reserva}.";

        $fechas = $this->fechas($reserva);
        if ($fechas !== '') {
            $cuerpo .= " Fechas: {$fechas}.";
        }

        $total = $this->total($reserva);
        if ($total !== '') {
            $cuerpo .= " Total: {$total}.";
        }

        return $cuerpo;
    }

    private function estanciaCuerpo(Estancia $estancia, string $mensaje): string
    {
        $reserva = $estancia->reserva;
        $codigo = $reserva !== null ? $reserva->codigo_reserva : "Estancia #$estancia->id";
        $cliente = $reserva !== null && $reserva->nombre_cliente !== null ? $reserva->nombre_cliente : 'Hola';

        return "Hola {$cliente}, {$mensaje} (reserva {$codigo}).";
    }

    private function recurso(Reserva $reserva): string
    {
        $habitacion = $reserva->habitacion;

        if ($habitacion !== null) {
            if ($habitacion->numero !== null) {
                return (string) $habitacion->numero;
            }

            return $habitacion->nombre;
        }

        $espacio = $reserva->espacio;

        return $espacio !== null ? $espacio->nombre : 'el recurso reservado';
    }

    private function fechas(Reserva $reserva): string
    {
        if ($reserva->fecha_check_in === null || $reserva->fecha_check_out === null) {
            return '';
        }

        return $reserva->fecha_check_in->format('d/m/Y').' al '.$reserva->fecha_check_out->format('d/m/Y');
    }

    private function total(Reserva $reserva): string
    {
        $moneda = $reserva->moneda;
        $codigo = $moneda !== null ? $moneda->codigo : 'USD';

        return number_format((float) $reserva->total, 2).' '.$codigo;
    }
}
