<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\Presenters\Habitaciones\HabitacionCategoriaPresenter;
use App\Repository\Queries\Habitaciones\ObtenerHabitacionesWebQuery;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ObtenerHabitaciones
{
    public function __construct(
        private HabitacionCategoriaPresenter $categoriaPresenter,
        private ObtenerHabitacionesWebQuery $habitacionesQuery,
    ) {}

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function ejecutar(int $perPage = 6, ?string $categoria = null, ?string $busqueda = null, ?int $huespedes = null): LengthAwarePaginator
    {
        $habitaciones = $this->habitacionesQuery->obtenerConFiltros($categoria, $busqueda, $huespedes);

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
        return $this->habitacionesQuery->obtenerCategoriasActivas();
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
