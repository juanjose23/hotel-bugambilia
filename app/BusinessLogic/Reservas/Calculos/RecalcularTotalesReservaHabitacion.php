<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Calculos;

use App\Enums\Reservas\EstadoReservaDetalle;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;

final readonly class RecalcularTotalesReservaHabitacion
{
    public function __construct(
        private ReservaRepositorioInterface $reservaRepositorio,
    ) {}

    public function ejecutar(Reserva $reserva): void
    {
        $detalles = $this->reservaRepositorio->detallesDe($reserva);

        $detallesActivos = $detalles->filter(fn (ReservaDetalle $d) => $d->estado !== EstadoReservaDetalle::CANCELADO);

        $subtotal = (float) $detallesActivos->sum(fn (ReservaDetalle $d) => (float) $d->subtotal);
        $descuento = (float) $detallesActivos->sum(fn (ReservaDetalle $d) => (float) $d->descuento);
        $impuestos = (float) $detallesActivos->sum(fn (ReservaDetalle $d) => (float) $d->impuestos);
        $total = ($subtotal - $descuento) + $impuestos;

        $totalPagado = (float) $reserva->total_pagado;
        $saldo = max(0.0, $total - $totalPagado);

        $this->reservaRepositorio->actualizarDatosGenerales($reserva, [
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'total' => $total,
            'saldo' => $saldo,
        ]);

        $reserva->subtotal = $subtotal;
        $reserva->descuento = $descuento;
        $reserva->total = $total;
        $reserva->saldo = $saldo;
    }
}
