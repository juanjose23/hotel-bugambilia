<?php

declare(strict_types=1);

namespace App\BusinessLogic\Huespedes;

use App\Enums\Reservas\TipoHuesped;
use App\Repository\Models\Habitaciones\DetalleHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use DomainException;

final class ValidarCapacidadEstancia
{
    public function validar(Reserva $reserva, ?ReservaDetalle $detalleEspecifico = null): void
    {
        $reserva->loadMissing(['habitacion.detalle', 'detalles.huespedes', 'detalles.reservable']);

        $detalles = $detalleEspecifico !== null
            ? collect([$detalleEspecifico])
            : $reserva->detalles->whereNull('parent_id');

        foreach ($detalles as $detalle) {
            $this->validarDetalle($reserva, $detalle);
        }
    }

    private function validarDetalle(Reserva $reserva, ReservaDetalle $reservaDetalle): void
    {
        $habitacion = Habitacion::query()
            ->where('reservable_id', $reservaDetalle->reservable_id)
            ->with('detalle')
            ->first();

        if ($habitacion === null) {
            $habitacion = $reserva->habitacion;
        }

        if ($habitacion === null) {
            return;
        }

        /** @var DetalleHabitacion|null $detalleHabitacion */
        $detalleHabitacion = $habitacion->getRelation('detalle') ?? $habitacion->detalle;
        if (! $detalleHabitacion instanceof DetalleHabitacion) {
            return;
        }

        $capacidadAdultos = (int) ($detalleHabitacion->capacidad_adultos ?? 0);
        $capacidadNinos = (int) ($detalleHabitacion->capacidad_ninos ?? 0);

        // Contar adultos registrados para este detalle específico
        $huespedes = $reservaDetalle->huespedes;

        $adultosRegistrados = $huespedes
            ->filter(fn ($h) => $h->tipo_huesped === TipoHuesped::ADULTO)
            ->count();

        // Si hay huéspedes registrados para este detalle, usamos la cantidad real registrada; si no hay, usamos la declarada en el detalle
        $adultosEnHabitacion = $huespedes->isNotEmpty()
            ? $adultosRegistrados
            : (int) ($reservaDetalle->adultos ?? 1);

        $ninosRegistrados = $huespedes
            ->filter(fn ($h) => $h->tipo_huesped === TipoHuesped::NINO)
            ->count();

        $ninosEnHabitacion = $huespedes->isNotEmpty()
            ? $ninosRegistrados
            : (int) ($reservaDetalle->ninos ?? 0);

        $numHab = $habitacion->numero ?? $habitacion->nombre ?? 'asignada';

        if ($capacidadAdultos > 0 && $adultosEnHabitacion > $capacidadAdultos) {
            throw new DomainException("La habitación {$numHab} admite un máximo de {$capacidadAdultos} adulto(s). Se intentó registrar {$adultosEnHabitacion} adulto(s).");
        }

        if ($capacidadNinos >= 0 && $ninosEnHabitacion > $capacidadNinos) {
            throw new DomainException("La habitación {$numHab} admite un máximo de {$capacidadNinos} niño(s). Se intentó registrar {$ninosEnHabitacion} niño(s).");
        }
    }
}
