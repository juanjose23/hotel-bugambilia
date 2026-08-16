<?php

declare(strict_types=1);

namespace App\Repository\Queries\Politicas;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Politicas\Politica;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Servicios\Servicio;

final class ResolverPoliticaCancelacionReservaQuery
{
    /**
     * Resuelve la política de cancelación asociada al recurso principal de una reserva.
     */
    public function ejecutar(Reserva $reserva): ?Politica
    {
        $reserva->loadMissing([
            'habitacion.politicas.penalizaciones',
            'espacio.politicas.penalizaciones',
            'servicio.politicas.penalizaciones',
            'promocion.politicas.penalizaciones',
        ]);

        if ($reserva->habitacion !== null) {
            $politica = $this->obtenerPoliticaCancelacion($reserva->habitacion);
            if ($politica !== null) {
                return $politica;
            }
        }

        if ($reserva->espacio !== null) {
            $politica = $this->obtenerPoliticaCancelacion($reserva->espacio);
            if ($politica !== null) {
                return $politica;
            }
        }

        if ($reserva->servicio !== null) {
            $politica = $this->obtenerPoliticaCancelacion($reserva->servicio);
            if ($politica !== null) {
                return $politica;
            }
        }

        if ($reserva->promocion !== null) {
            $politica = $this->obtenerPoliticaCancelacion($reserva->promocion);
            if ($politica !== null) {
                return $politica;
            }
        }

        return null;
    }

    private function obtenerPoliticaCancelacion(Habitacion|Espacio|Servicio|Promocion $recurso): ?Politica
    {
        /** @var Politica|null $politica */
        $politica = $recurso->politicas
            ->where('estado', EstadoGeneral::Activo)
            ->where('aplica_penalizacion', true)
            ->first();

        return $politica;
    }
}
