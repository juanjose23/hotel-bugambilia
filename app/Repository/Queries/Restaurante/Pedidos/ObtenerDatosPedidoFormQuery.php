<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Plato;
use Illuminate\Support\Collection;

final class ObtenerDatosPedidoFormQuery
{
    /**
     * @return array<int|string, string>
     */
    public function mesasDisponibles(): array
    {
        /** @var array<int|string, string> */
        return Espacio::where('tipo', 'mesa')
            ->pluck('nombre', 'id')
            ->all();
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    public function platosActivosAgrupadosPorCategoria(): array
    {
        /** @var array<string, array<int|string, string>> */
        return Plato::query()
            ->activos()
            ->with('categoria')
            ->get()
            ->groupBy(fn (Plato $plato): string => $plato->categoria->nombre ?? 'Menú General / Varios')
            ->map(fn (Collection $grupo): array => $grupo->pluck('nombre', 'id')->all())
            ->all();
    }

    public function precioActualDePlato(int $platoId): ?float
    {
        /** @var Plato|null $plato */
        $plato = Plato::query()->find($platoId);

        if (! $plato instanceof Plato) {
            return null;
        }

        $precio = $plato->precios()->latest()->first();

        return $precio !== null ? (float) $precio->precio : null;
    }
}
