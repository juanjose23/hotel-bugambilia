<?php

declare(strict_types=1);

namespace App\BusinessLogic\Huespedes;

use App\Repository\Models\Reservas\Reserva;
use DomainException;

final class ValidarTitularUnico
{
    /**
     * Valida que no exista ya un titular en la reserva si el nuevo huésped es titular.
     *
     * @param  array<string, mixed>  $datosHuesped
     */
    public function validarRegistroNuevo(Reserva $reserva, array $datosHuesped): void
    {
        $esTitular = (bool) ($datosHuesped['es_titular'] ?? false);
        if (! $esTitular) {
            return;
        }

        $titularExistente = $reserva->huespedes()
            ->where('es_titular', true)
            ->exists();

        if ($titularExistente) {
            throw new DomainException('La reserva ya cuenta con un huésped titular asignado. Solo se permite un titular por reserva.');
        }
    }

    /**
     * Valida que al finalizar el registro de huéspedes exista exactamente 1 titular.
     */
    public function validarEstructuraCompleta(Reserva $reserva): void
    {
        $totalHuespedes = $reserva->huespedes()->count();

        if ($totalHuespedes === 0) {
            if ($reserva->nombre_cliente === null || trim($reserva->nombre_cliente) === '') {
                throw new DomainException('Debe registrarse un titular o especificar el nombre del cliente en la reserva.');
            }

            return;
        }

        $cantidadTitulares = $reserva->huespedes()->where('es_titular', true)->count();

        if ($cantidadTitulares === 0) {
            throw new DomainException('Debe registrarse exactamente un huésped titular entre los huéspedes asignados.');
        }

        if ($cantidadTitulares > 1) {
            throw new DomainException('Se han detectado múltiples huéspedes titulares. Solo puede haber uno.');
        }
    }
}
