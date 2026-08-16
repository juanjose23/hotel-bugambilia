<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @phpstan-type GrupoCategoria array{
 *     categoria_id: int,
 *     categoria: string,
 *     codigo_categoria: string,
 *     descripcion: string,
 *     ids: array<int, int>,
 *     precios: array<int, float>,
 *     monedas: array<int, string>,
 *     imagenes: array<int, string|null>,
 *     capacidades: array<int, int>,
 * }
 */
final class HabitacionCategoriaPresenter
{
    /**
     * Agrupa las habitaciones por categoría acumulando precios, imágenes y capacidades.
     *
     * @param  Collection<int, Habitacion>  $habitaciones
     * @return array<int|string, GrupoCategoria>
     */
    public function agrupar(Collection $habitaciones): array
    {
        /** @var array<int|string, GrupoCategoria> $grupos */
        $grupos = [];

        foreach ($habitaciones as $h) {
            $key = (string) (int) ($h->categoria_id ?? 0);

            if (! isset($grupos[$key])) {
                $grupos[$key] = $this->grupoVacio($h);
            }

            $this->acumular($grupos[$key], $h);
        }

        return $grupos;
    }

    /**
     * @param  array<int, int|string>  $categoriaIds
     * @return Collection<int|string, mixed>
     */
    public function totalesPorCategoria(array $categoriaIds): Collection
    {
        if ($categoriaIds === []) {
            return collect();
        }

        return Habitacion::whereIn('categoria_id', $categoriaIds)
            ->selectRaw('categoria_id, count(*) as total')
            ->groupBy('categoria_id')
            ->pluck('total', 'categoria_id');
    }

    /**
     * @param  array<int|string, GrupoCategoria>  $grupos
     * @param  Collection<int|string, mixed>  $totalesPorCategoria
     * @return array<int, array<string, mixed>>
     */
    public function resultados(array $grupos, Collection $totalesPorCategoria): array
    {
        $resultados = [];

        foreach ($grupos as $grupo) {
            $totalValue = $totalesPorCategoria->get($grupo['categoria_id'], 0);
            $total = is_numeric($totalValue) ? (int) $totalValue : 0;
            $resultados[] = $this->resultado($grupo, $total);
        }

        return $resultados;
    }

    /**
     * @return GrupoCategoria
     */
    private function grupoVacio(Habitacion $h): array
    {
        return [
            'categoria_id' => (int) ($h->categoria_id ?? 0),
            'categoria' => $h->categoria->nombre ?? 'Habitación',
            'codigo_categoria' => $h->categoria->codigo ?? '',
            'descripcion' => $h->descripcion ?? '',
            'ids' => [],
            'precios' => [],
            'monedas' => [],
            'imagenes' => [],
            'capacidades' => [],
        ];
    }

    /**
     * @param  GrupoCategoria  $grupo
     */
    private function acumular(array &$grupo, Habitacion $h): void
    {
        $grupo['ids'][] = (int) $h->id;

        $precioObj = $h->precios->first();
        if ($precioObj !== null) {
            $grupo['precios'][] = (float) $precioObj->precio;
            if ($precioObj->moneda) {
                $grupo['monedas'][] = $precioObj->moneda->simbolo;
            }
        }

        $imagenObj = $h->imagenes->first();
        $grupo['imagenes'][] = $imagenObj !== null ? $imagenObj->url : null;

        $detalle = $h->detalle;
        $grupo['capacidades'][] = $detalle !== null
            ? (int) ($detalle->capacidad_adultos + $detalle->capacidad_ninos)
            : 2;
    }

    /**
     * @param  GrupoCategoria  $grupo
     * @return array<string, mixed>
     */
    private function resultado(array $grupo, int $total): array
    {
        $disponibles = count($grupo['ids']);
        $precioMin = $grupo['precios'] !== [] ? min($grupo['precios']) : null;
        $simboloMoneda = $grupo['monedas'] !== [] ? $grupo['monedas'][0] : '$';
        $imagen = $this->imagenPrincipal($grupo['imagenes'], $disponibles);

        return [
            'id' => $grupo['categoria_id'],
            'codigo' => $grupo['codigo_categoria'],
            'slug' => Str::slug($grupo['categoria']).'-'.$grupo['ids'][0],
            'nombre' => $grupo['categoria'],
            'descripcion' => $grupo['descripcion'],
            'categoria' => $grupo['categoria'],
            'precio' => $precioMin,
            'precio_desde' => $precioMin,
            'moneda' => $simboloMoneda,
            'imagen' => $imagen,
            'disponibles' => $disponibles,
            'total' => $total,
            'capacidad' => $this->capacidadTexto($grupo['capacidades']),
            'ids' => $grupo['ids'],
        ];
    }

    /**
     * @param  array<int, string|null>  $imagenes
     */
    private function imagenPrincipal(array $imagenes, int $disponibles): string
    {
        foreach ($imagenes as $img) {
            if (is_string($img) && $img !== '') {
                return $img;
            }
        }

        return match ($disponibles % 3) {
            0 => '/images/group-room.jpg',
            1 => '/images/room-detail.jpg',
            default => '/images/main-room.jpg',
        };
    }

    /**
     * @param  array<int, int>  $capacidades
     */
    private function capacidadTexto(array $capacidades): string
    {
        if ($capacidades === []) {
            return '2';
        }

        $capacidadMax = max($capacidades);
        $capacidadMin = min($capacidades);

        return $capacidadMax === $capacidadMin
            ? (string) $capacidadMax
            : "{$capacidadMin}-{$capacidadMax}";
    }
}
