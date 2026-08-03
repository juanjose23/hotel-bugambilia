<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Mesas;

use App\Enums\Reservas\EstadoReserva;
use App\Repository\Models\Reservas\Reserva;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class ObtenerReservasVigentesMesaQuery
{
    private const VENTANA_PREVIA_MINUTOS = 30;

    /**
     * @param  array<int, int>  $mesaIds
     * @return Collection<int, Reserva>
     */
    public function paraMesas(array $mesaIds, ?CarbonInterface $momento = null): Collection
    {
        $momento ??= now();
        $limiteLlegada = $momento->copy()->addMinutes(self::VENTANA_PREVIA_MINUTOS);

        return Reserva::query()
            ->whereIn('espacio_id', $mesaIds)
            ->whereIn('estado', [EstadoReserva::PENDIENTE, EstadoReserva::CONFIRMADA])
            ->whereHas('detalles', fn ($query) => $query
                ->whereNull('parent_id')
                ->where('fecha_inicio', '<=', $limiteLlegada)
                ->where('fecha_fin', '>', $momento))
            ->orderBy('fecha_check_in')
            ->orderBy('hora_reserva')
            ->get()
            ->keyBy(fn (Reserva $reserva): int => (int) $reserva->espacio_id);
    }

    public function paraMesa(int $mesaId, ?CarbonInterface $momento = null): ?Reserva
    {
        return $this->paraMesas([$mesaId], $momento)->get($mesaId);
    }
}
