<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Repository\Models\Facturacion\PagoTransaccion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ObtenerTransaccionesStripePendientesReservaQuery
{
    /** @return Collection<int, PagoTransaccion> */
    public function ejecutar(int $reservaId): Collection
    {
        return PagoTransaccion::query()
            ->where('reserva_id', $reservaId)
            ->whereIn('estado', [
                EstadoTransaccionPago::Pendiente->value,
                EstadoTransaccionPago::Autorizada->value,
            ])
            ->whereNotNull('referencia_pasarela')
            ->where(function (Builder $query): void {
                $query->whereHas('pasarela', fn (Builder $q) => $q->where('codigo', 'stripe'))
                    ->orWhere('referencia_pasarela', 'like', 'pi_%')
                    ->orWhere('referencia_pasarela', 'like', 'ch_%');
            })
            ->get();
    }
}
