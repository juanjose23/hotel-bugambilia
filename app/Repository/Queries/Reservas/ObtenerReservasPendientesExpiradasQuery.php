<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ObtenerReservasPendientesExpiradasQuery
{
    /** @return Collection<int, Reserva> */
    public function ejecutar(DateTimeImmutable $hoy, DateTimeImmutable $ahora): Collection
    {
        $limiteCreacion = $ahora->modify('-24 hours');

        return Reserva::query()
            ->where('estado', EstadoReserva::PENDIENTE)
            ->where(function (Builder $query) use ($hoy, $ahora, $limiteCreacion): void {
                $query->whereDate('fecha_check_in', '<=', $hoy)
                    ->orWhereHas('detalles', fn (Builder $q) => $q->where('hold_expires_at', '<=', $ahora))
                    ->orWhere('created_at', '<=', $limiteCreacion);
            })
            ->get();
    }
}
