<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\TipoPagoReserva;
use App\Repository\Models\Reservas\Reserva;
use DomainException;

final class ValidarPoliticaPagoReserva
{
    /**
     * Valida si el monto acumulado pagado o a ingresar satisface la política de pago exigida por la reserva.
     */
    public function validarMontoParaConfirmacion(Reserva $reserva, float $montoAAbonar = 0.0): void
    {
        $tipoPago = $reserva->tipo_pago ?? TipoPagoReserva::SIN_PAGO;
        $totalReserva = (float) $reserva->total;
        $pagadoActual = (float) $reserva->total_pagado + $montoAAbonar;
        $montoMinimoRequerido = $tipoPago->monto($totalReserva);

        if ($pagadoActual < $montoMinimoRequerido) {
            $falta = $montoMinimoRequerido - $pagadoActual;
            throw new DomainException(
                "La política de pago '{$tipoPago->getLabel()}' requiere un abono/pago acumulado mínimo de "
                .number_format($montoMinimoRequerido, 2).' para procesar la confirmación. Faltan '
                .number_format($falta, 2).'.'
            );
        }
    }

    /**
     * Calcula dinámicamente el saldo pendiente requerido por la política de pago o el saldo restante para liquidación.
     */
    public function obtenerMontoFaltantePolitica(Reserva $reserva): float
    {
        $tipoPago = $reserva->tipo_pago ?? TipoPagoReserva::SIN_PAGO;
        $montoMinimoRequerido = $tipoPago->monto((float) $reserva->total);
        $pagadoActual = (float) $reserva->total_pagado;
        $faltantePolitica = max(0.0, $montoMinimoRequerido - $pagadoActual);

        // Si la política de anticipo ya fue satisfecha pero aún existe saldo pendiente para liquidar la reserva
        if ($faltantePolitica <= 0.0 && (float) $reserva->saldo > 0.0) {
            return (float) $reserva->saldo;
        }

        return $faltantePolitica;
    }
}
