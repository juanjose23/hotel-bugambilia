<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Repository\Models\Facturacion\PagoTransaccion;

final class ObtenerTransaccionesPagoReservaQuery
{
    public function ejecutar(int $reservaId): float
    {
        $total = PagoTransaccion::query()
            ->where('reserva_id', $reservaId)
            ->whereIn('estado', [
                EstadoTransaccionPago::Capturada->value,
                EstadoTransaccionPago::Autorizada->value,
                EstadoTransaccionPago::Pendiente->value,
            ])
            ->sum('monto');

        return (float) $total;
    }
}
