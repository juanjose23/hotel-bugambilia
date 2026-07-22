<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Servicios\Servicio;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

final class ObtenerServiciosLanding
{
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
            ->through(function (Servicio $s) {
                $precioObj = $s->precios->first();
                $monto = $precioObj ? (float) $precioObj->precio : null;
                $moneda = $precioObj?->moneda;
                $simbolo = $moneda ? $moneda->simbolo : '$';

                $categoria = $s->categoria;
                $categoriaNombre = $categoria ? $categoria->nombre : 'Servicio General';

                $imagen = $s->imagenes->first();
                $imagenUrl = null;

                if ($imagen && ! empty($imagen->url)) {
                    $url = trim($imagen->url);
                    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
                        $imagenUrl = $url;
                    } else {
                        $imagenUrl = '/storage/'.ltrim($url, '/');
                    }
                }

                if (! $imagenUrl) {
                    $cat = strtolower($categoriaNombre);
                    $name = strtolower($s->nombre);
                    if (str_contains($cat, 'gastro') || str_contains($cat, 'restaurante') || str_contains($name, 'desayuno') || str_contains($name, 'cena')) {
                        $imagenUrl = '/images/service-kitchen.png';
                    } elseif (str_contains($cat, 'piscina') || str_contains($name, 'piscina') || str_contains($cat, 'agua')) {
                        $imagenUrl = '/images/service-pool.png';
                    } elseif (str_contains($cat, 'bar') || str_contains($cat, 'bebida') || str_contains($name, 'bar')) {
                        $imagenUrl = '/images/service-bartender.png';
                    } elseif (str_contains($cat, 'evento') || str_contains($name, 'evento')) {
                        $imagenUrl = '/images/service-events.png';
                    } else {
                        $imagenUrl = '/images/terrace.jpg';
                    }
                }

                $slug = Str::slug($s->nombre).'-'.$s->id;

                return [
                    'id' => $s->id,
                    'codigo' => $s->codigo,
                    'slug' => $slug,
                    'nombre' => $s->nombre,
                    'descripcion' => $s->descripcion ?? 'Servicio exclusivo para nuestros huéspedes.',
                    'categoria' => $categoriaNombre,
                    'precio' => $monto,
                    'moneda' => $simbolo,
                    'imagen' => $imagenUrl,
                    'icono' => $s->icono,
                ];
            });

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
