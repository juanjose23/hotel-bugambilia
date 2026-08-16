<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reportes;

use App\Repository\Models\Reservas\Reserva;
use Illuminate\Database\Eloquent\Collection;

final class ReservasOcupacionQuery
{
    /**
     * @return Collection<int, Reserva>
     */
    public function paraOcupacion(string $fechaInicio, string $fechaFin, ?string $estado): Collection
    {
        $query = Reserva::with(['habitacion', 'cliente.persona'])
            ->whereDate('fecha_check_in', '>=', $fechaInicio)
            ->whereDate('fecha_check_in', '<=', $fechaFin);

        if ($estado !== null && $estado !== '') {
            $query->where('estado', $estado);
        }

        return $query->orderBy('fecha_check_in', 'desc')->get();
    }

    /**
     * @return Collection<int, Reserva>
     */
    public function paraEstados(string $fechaInicio, string $fechaFin, ?string $estado): Collection
    {
        $query = Reserva::with(['habitacion', 'cliente.persona'])
            ->whereDate('fecha_check_in', '>=', $fechaInicio)
            ->whereDate('fecha_check_in', '<=', $fechaFin);

        if ($estado !== null && $estado !== '') {
            $query->where('estado', $estado);
        }

        return $query->orderBy('estado')->orderBy('fecha_check_in', 'desc')->get();
    }

    /**
     * @return Collection<int, Reserva>
     */
    public function paraVentas(string $fechaInicio, string $fechaFin, ?string $tipoPago): Collection
    {
        $query = Reserva::with(['cliente.persona'])
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin);

        if ($tipoPago !== null && $tipoPago !== '') {
            $query->where('tipo_pago_reserva', $tipoPago);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
