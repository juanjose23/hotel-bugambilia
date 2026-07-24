<?php

declare(strict_types=1);

namespace App\BusinessLogic\Huespedes;

use App\Repository\Models\Habitaciones\DetalleHabitacion;
use App\Repository\Models\Reservas\Reserva;
use DomainException;

final class ValidarCapacidadEstancia
{
    public function validar(Reserva $reserva): void
    {
        $reserva->loadMissing('habitacion.detalle', 'detalles.huespedes');
        $habitacion = $reserva->habitacion;

        if ($habitacion === null) {
            return;
        }

        $detalle = $habitacion->getRelation('detalle');
        if (! $detalle instanceof DetalleHabitacion) {
            return;
        }

        $capacidadAdultos = (int) ($detalle->capacidad_adultos ?? 0);
        $capacidadNinos = (int) ($detalle->capacidad_ninos ?? 0);
        $adultos = (int) $reserva->adultos;
        $ninos = (int) $reserva->ninos;

        if ($capacidadAdultos > 0 && $adultos > $capacidadAdultos) {
            throw new DomainException("La habitación admite un máximo de {$capacidadAdultos} adultos.");
        }

        if ($capacidadNinos >= 0 && $ninos > $capacidadNinos) {
            throw new DomainException("La habitación admite un máximo de {$capacidadNinos} niños.");
        }

        $registrados = $reserva->detalles->flatMap->huespedes->count();
        $declarados = $adultos + $ninos;

        if ($registrados > $declarados) {
            throw new DomainException('La cantidad de huéspedes registrados supera la cantidad declarada en la reserva.');
        }
    }
}
