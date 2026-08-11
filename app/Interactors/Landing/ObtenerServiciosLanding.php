<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Presenters\Landing\ServicioTarjetaPresenter;
use App\Repository\Models\Servicios\Servicio;
use Illuminate\Pagination\LengthAwarePaginator;

final class ObtenerServiciosLanding
{
    public function __construct(
        private readonly ServicioTarjetaPresenter $servicioPresenter,
    ) {}

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function ejecutar(int $perPage = 9, ?string $categoria = null, ?string $busqueda = null): LengthAwarePaginator
    {
        $query = Servicio::with(['categoria', 'imagenes', 'precios.moneda'])
            ->activos()
            ->where('web', true);

        if ($categoria !== null && trim($categoria) !== '' && strtoupper(trim($categoria)) !== 'TODOS') {
            $query->whereHas('categoria', fn ($q) => $q->where('nombre', trim($categoria)));
        }

        if ($busqueda !== null && trim($busqueda) !== '') {
            $term = '%'.trim($busqueda).'%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('descripcion', 'like', $term);
            });
        }

        /** @var LengthAwarePaginator<int, array<string, mixed>> $paginator */
        $paginator = $query
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->appends(array_filter([
                'categoria' => $categoria,
                'buscar' => $busqueda,
            ]))
            ->through(fn (Servicio $s): array => $this->servicioPresenter->tarjeta($s));

        return $paginator;
    }

    /**
     * Retorna todas las categorías ordenadas descendentemente por cantidad de servicios activos (la que tiene más servicios primero)
     *
     * @return array<int, array{nombre: string, total: int}>
     */
    public function categoriasConConteo(): array
    {
        $servicios = Servicio::activos()
            ->where('web', true)
            ->whereNotNull('categoria_id')
            ->with('categoria')
            ->get();

        $conteo = [];
        foreach ($servicios as $s) {
            $nombre = $s->categoria->nombre ?? 'General';
            if (! isset($conteo[$nombre])) {
                $conteo[$nombre] = 0;
            }
            $conteo[$nombre]++;
        }

        arsort($conteo);

        $resultado = [];
        foreach ($conteo as $nombre => $total) {
            $resultado[] = ['nombre' => $nombre, 'total' => $total];
        }

        return $resultado;
    }

    /**
     * @return array<int, string>
     */
    public function categorias(): array
    {
        $conConteo = $this->categoriasConConteo();

        return array_column($conConteo, 'nombre');
    }
}
