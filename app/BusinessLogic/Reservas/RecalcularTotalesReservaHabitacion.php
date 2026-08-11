<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\EstadoReservaDetalle;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;

final class RecalcularTotalesReservaHabitacion
{
    public function ejecutar(Reserva $reserva): void
    {
        $detallesActivos = $reserva->detalles()
            ->where('estado', '!=', EstadoReservaDetalle::CANCELADO)
            ->get();

        $subtotal = $detallesActivos->sum(fn (ReservaDetalle $d) => (float) $d->subtotal);
        $descuento = $detallesActivos->sum(fn (ReservaDetalle $d) => (float) $d->descuento);
        $impuestos = $detallesActivos->sum(fn (ReservaDetalle $d) => (float) $d->impuestos);
        $total = ($subtotal - $descuento) + $impuestos;

        $totalPagado = (float) $reserva->total_pagado;
        $saldo = max(0.0, $total - $totalPagado);

        $reserva->update([
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'total' => $total,
            'saldo' => $saldo,
        ]);
    }
}
