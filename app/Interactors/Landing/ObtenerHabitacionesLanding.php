<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Pagination\LengthAwarePaginator;

final class ObtenerHabitacionesLanding
{
    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function ejecutar(int $perPage = 6, ?string $categoria = null, ?string $busqueda = null): LengthAwarePaginator
    {
        $query = Habitacion::with(['categoria', 'detalle', 'imagenes', 'precios.moneda'])
            ->activas();

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

        $habitaciones = $query->orderBy('categoria_id')
            ->orderBy('id')
            ->get();

        $grupos = [];

        foreach ($habitaciones as $h) {
            $catId = (int) ($h->categoria_id ?? 0);
            $catNombre = $h->categoria->nombre ?? 'Habitación';
            $key = (string) $catId;

            if (! isset($grupos[$key])) {
                $grupos[$key] = [
                    'categoria_id' => $catId,
                    'categoria' => $catNombre,
                    'codigo_categoria' => $h->categoria->codigo ?? '',
                    'descripcion' => $h->descripcion ?? '',
                    'ids' => [],
                    'precios' => [],
                    'monedas' => [],
                    'imagenes' => [],
                    'capacidades' => [],
                ];
            }

            $grupos[$key]['ids'][] = (int) $h->id;

            $precioObj = $h->precios->first();
            if ($precioObj !== null) {
                $grupos[$key]['precios'][] = (float) $precioObj->precio;
                if ($precioObj->moneda) {
                    $grupos[$key]['monedas'][] = $precioObj->moneda->simbolo;
                }
            }

            $imagenObj = $h->imagenes->first();
            $grupos[$key]['imagenes'][] = $imagenObj?->url;

            $detalle = $h->detalle;
            $capacidad = $detalle !== null
                ? (int) ($detalle->capacidad_adultos + $detalle->capacidad_ninos)
                : 2;
            $grupos[$key]['capacidades'][] = $capacidad;
        }

        $resultados = [];

        foreach ($grupos as $g) {
            $disponibles = count($g['ids']);
            $total = Habitacion::where('categoria_id', $g['categoria_id'])->count();
            $precioMin = $g['precios'] !== [] ? min($g['precios']) : null;
            $simboloMoneda = $g['monedas'] !== [] ? $g['monedas'][0] : '$';

            $imagen = null;
            foreach ($g['imagenes'] as $img) {
                if (is_string($img) && $img !== '') {
                    $imagen = $img;
                    break;
                }
            }
            if ($imagen === null) {
                $imagen = match ($disponibles % 3) {
                    0 => '/images/group-room.jpg',
                    1 => '/images/room-detail.jpg',
                    default => '/images/main-room.jpg',
                };
            }

            $capacidadMax = max($g['capacidades']);
            $capacidadMin = min($g['capacidades']);
            $capacidadTexto = $capacidadMax === $capacidadMin
                ? (string) $capacidadMax
                : "{$capacidadMin}-{$capacidadMax}";

            $resultados[] = [
                'id' => $g['categoria_id'],
                'codigo' => $g['codigo_categoria'],
                'nombre' => $g['categoria'],
                'descripcion' => $g['descripcion'],
                'categoria' => $g['categoria'],
                'precio_desde' => $precioMin,
                'moneda' => $simboloMoneda,
                'imagen' => $imagen,
                'disponibles' => $disponibles,
                'total' => $total,
                'capacidad' => $capacidadTexto,
                'ids' => $g['ids'],
            ];
        }

        $pageParam = request()->get('page', '1');
        $pagina = max(1, is_numeric($pageParam) ? (int) $pageParam : 1);
        $totalItems = count($resultados);
        $offset = ($pagina - 1) * $perPage;
        $itemsPagina = array_slice($resultados, $offset, $perPage);

        /** @var LengthAwarePaginator<int, array<string, mixed>> $paginator */
        $paginator = new LengthAwarePaginator(
            items: $itemsPagina,
            total: $totalItems,
            perPage: $perPage,
            currentPage: $pagina,
            options: ['path' => request()->url(), 'query' => request()->query()],
        );

        return $paginator;
    }

    /**
     * @return array<int, string>
     */
    public function categorias(): array
    {
        $categorias = Habitacion::activas()
            ->whereNotNull('categoria_id')
            ->with('categoria')
            ->get()
            ->pluck('categoria.nombre')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        /** @var array<int, string> $categorias */
        return $categorias;
    }
}
