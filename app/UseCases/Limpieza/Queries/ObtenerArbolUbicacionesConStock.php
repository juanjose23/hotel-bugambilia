<?php

declare(strict_types=1);

namespace App\UseCases\Limpieza\Queries;

use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use Illuminate\Support\Collection;

class ObtenerArbolUbicacionesConStock
{
    /**
     * @return Collection<int, array{ubicacion: Ubicacion, espacios: Collection<int, array{espacio: Espacio, hijos: Collection<int, mixed>, stocks: Collection<int, mixed>}>, habitaciones: Collection<int, array{habitacion: Habitacion, stocks: Collection<int, mixed>}>}>
     */
    public function execute(): Collection
    {
        /** @var Collection<int, array{ubicacion: Ubicacion, espacios: Collection<int, array{espacio: Espacio, hijos: Collection<int, mixed>, stocks: Collection<int, mixed>}>, habitaciones: Collection<int, array{habitacion: Habitacion, stocks: Collection<int, mixed>}>}> $result */
        $result = Ubicacion::query()
            ->with([
                'padre',
            ])
            ->get()
            ->map(fn (Ubicacion $ubicacion) => [
                'ubicacion' => $ubicacion,
                'espacios' => $this->getEspaciosConStock($ubicacion->id),
                'habitaciones' => $this->getHabitacionesConStock($ubicacion->id),
            ]);

        return $result;
    }

    /**
     * @return Collection<int, array{espacio: Espacio, hijos: Collection<int, array{espacio: Espacio, stocks: Collection<int, mixed>}>, stocks: Collection<int, mixed>}>
     */
    private function getEspaciosConStock(int $ubicacionId): Collection
    {
        /** @var Collection<int, array{espacio: Espacio, hijos: Collection<int, array{espacio: Espacio, stocks: Collection<int, mixed>}>, stocks: Collection<int, mixed>}> $result */
        $result = Espacio::query()
            ->where('ubicacion_id', $ubicacionId)
            ->whereNull('padre_id')
            ->with([
                'hijos' => fn ($q) => $q->with('espacioStocks.variante.producto'),
                'espacioStocks.variante.producto',
            ])
            ->get()
            ->map(fn (Espacio $espacio) => [
                'espacio' => $espacio,
                'hijos' => $espacio->hijos->map(fn (Espacio $hijo) => [
                    'espacio' => $hijo,
                    'stocks' => $hijo->espacioStocks->collect(),
                ])->collect(),
                'stocks' => $espacio->espacioStocks->collect(),
            ]);

        return $result;
    }

    /**
     * @return Collection<int, array{habitacion: Habitacion, stocks: Collection<int, mixed>}>
     */
    private function getHabitacionesConStock(int $ubicacionId): Collection
    {
        /** @var Collection<int, array{habitacion: Habitacion, stocks: Collection<int, mixed>}> $result */
        $result = Habitacion::query()
            ->where('ubicacion_id', $ubicacionId)
            ->with('habitacionStocks.variante.producto')
            ->get()
            ->map(fn (Habitacion $habitacion) => [
                'habitacion' => $habitacion,
                'stocks' => $habitacion->habitacionStocks->collect(),
            ]);

        return $result;
    }
}
