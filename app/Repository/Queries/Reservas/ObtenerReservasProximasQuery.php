<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

final class ObtenerReservasProximasQuery
{
    /** @return Collection<int, Reserva> */
    public function ejecutar(CarbonInterface $desde, CarbonInterface $hasta): Collection
    {
        return Reserva::query()
            ->with([
                'detalles' => fn ($query) => $query
                    ->whereNull('parent_id')
                    ->whereBetween('fecha_inicio', [$desde, $hasta])
                    ->orderBy('fecha_inicio'),
                'espacio',
                'habitacion',
            ])
            ->whereIn('estado', [EstadoReserva::PENDIENTE->value, EstadoReserva::CONFIRMADA->value])
            ->whereHas('detalles', fn ($query) => $query
                ->whereNull('parent_id')
                ->whereBetween('fecha_inicio', [$desde, $hasta]))
            ->orderBy('fecha_check_in')
            ->get();
    }
}
