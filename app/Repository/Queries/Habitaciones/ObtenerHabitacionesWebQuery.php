<?php

declare(strict_types=1);

namespace App\Repository\Queries\Habitaciones;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final readonly class ObtenerHabitacionesWebQuery
{
    /**
     * @return Collection<int, Habitacion>
     */
    public function obtenerConFiltros(?string $categoria = null, ?string $busqueda = null, ?int $huespedes = null): Collection
    {
        $query = Habitacion::query()
            ->with(['categoria', 'detalle', 'imagenes', 'precios.moneda'])
            ->activas();

        $this->aplicarFiltros($query, $categoria, $busqueda, $huespedes);

        /** @var Collection<int, Habitacion> $habitaciones */
        $habitaciones = $query->orderBy('categoria_id')
            ->orderBy('id')
            ->get();

        return $habitaciones;
    }

    /**
     * @return array<int, string>
     */
    public function obtenerCategoriasActivas(): array
    {
        $categorias = Catalogo::query()
            ->whereIn('id', Habitacion::activas()->whereNotNull('categoria_id')->select('categoria_id'))
            ->pluck('nombre')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        /** @var array<int, string> $categorias */
        return $categorias;
    }

    /**
     * @param  Builder<Habitacion>  $query
     */
    private function aplicarFiltros(Builder $query, ?string $categoria, ?string $busqueda, ?int $huespedes): void
    {
        if ($categoria !== null && trim($categoria) !== '' && strtoupper(trim($categoria)) !== 'TODOS') {
            $query->whereHas('categoria', function (Builder $q) use ($categoria): void {
                $q->where('nombre', trim($categoria));
            });
        }

        if ($busqueda !== null && trim($busqueda) !== '') {
            $term = '%'.trim($busqueda).'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('nombre', 'like', $term)
                    ->orWhere('descripcion', 'like', $term)
                    ->orWhereHas('categoria', fn (Builder $catQ) => $catQ->where('nombre', 'like', $term));
            });
        }

        if ($huespedes !== null && $huespedes > 0) {
            $query->whereHas('detalle', function (Builder $q) use ($huespedes): void {
                $q->whereRaw('(capacidad_adultos + capacidad_ninos) >= ?', [$huespedes]);
            });
        }
    }
}
