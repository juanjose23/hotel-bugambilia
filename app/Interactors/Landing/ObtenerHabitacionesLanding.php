<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Presenters\Landing\HabitacionCategoriaPresenter;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ObtenerHabitacionesLanding
{
    public function __construct(
        private readonly HabitacionCategoriaPresenter $categoriaPresenter,
    ) {}

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function ejecutar(int $perPage = 6, ?string $categoria = null, ?string $busqueda = null, ?int $huespedes = null): LengthAwarePaginator
    {
        $query = Habitacion::with(['categoria', 'detalle', 'imagenes', 'precios.moneda'])
            ->activas();

        $this->aplicarFiltros($query, $categoria, $busqueda, $huespedes);

        $habitaciones = $query->orderBy('categoria_id')
            ->orderBy('id')
            ->get();

        $grupos = $this->categoriaPresenter->agrupar($habitaciones);
        $totales = $this->categoriaPresenter->totalesPorCategoria(array_keys($grupos));
        $resultados = $this->categoriaPresenter->resultados($grupos, $totales);

        return $this->paginar($resultados, $perPage);
    }

    /**
     * @return array<int, string>
     */
    public function categorias(): array
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
            $query->whereHas('categoria', function ($q) use ($categoria) {
                $q->where('nombre', trim($categoria));
            });
        }

        if ($busqueda !== null && trim($busqueda) !== '') {
            $term = '%'.trim($busqueda).'%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('descripcion', 'like', $term)
                    ->orWhereHas('categoria', fn ($catQ) => $catQ->where('nombre', 'like', $term));
            });
        }

        if ($huespedes !== null && $huespedes > 0) {
            $query->whereHas('detalle', function ($q) use ($huespedes) {
                $q->whereRaw('(capacidad_adultos + capacidad_ninos) >= ?', [$huespedes]);
            });
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $resultados
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private function paginar(array $resultados, int $perPage): LengthAwarePaginator
    {
        $pageParam = request()->get('page', '1');
        $pagina = max(1, is_numeric($pageParam) ? (int) $pageParam : 1);
        $totalItems = count($resultados);
        $offset = ($pagina - 1) * $perPage;
        $itemsPagina = array_slice($resultados, $offset, $perPage);

        return new LengthAwarePaginator(
            items: $itemsPagina,
            total: $totalItems,
            perPage: $perPage,
            currentPage: $pagina,
            options: ['path' => request()->url(), 'query' => request()->query()],
        );
    }
}
