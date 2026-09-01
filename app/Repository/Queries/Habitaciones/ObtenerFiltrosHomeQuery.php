<?php

declare(strict_types=1);

namespace App\Repository\Queries\Habitaciones;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Builder;

final class ObtenerFiltrosHomeQuery
{
    /**
     * @return array{
     *     categorias: array<int, array{id: int, nombre: string, slug: string, habitaciones_count: int}>,
     *     vistas: array<int, string>,
     *     servicios: array<int, array{id: int, nombre: string, slug: string, categoria: string}>,
     *     capacidades: array<int, int>,
     *     precioMin: float,
     *     precioMax: float
     * }
     */
    public function ejecutar(): array
    {
        // Categorías de habitaciones con conteo de habitaciones activas
        $categorias = Catalogo::whereHas('catalogoTipo', function (Builder $query): void {
            $query->whereIn('codigo', ['CATEGORIA_HABITACION', 'categoria_habitacion']);
        })
            ->withCount(['habitaciones' => function (Builder $query): void {
                $query->where('estado', 1);
            }])
            ->get()
            ->map(fn (Catalogo $c): array => [
                'id' => (int) $c->id,
                'nombre' => (string) $c->nombre,
                'slug' => (string) ($c->slug ?? strtolower(str_replace(' ', '-', (string) $c->nombre))),
                'habitaciones_count' => (int) ($c->habitaciones_count ?? 0),
            ])
            ->values()
            ->all();

        // Tipos de vistas registradas en el catálogo
        $vistas = Catalogo::whereHas('catalogoTipo', function (Builder $query): void {
            $query->whereIn('codigo', ['TIPO_VISTA', 'tipo_vista']);
        })
            ->pluck('nombre')
            ->filter()
            ->map(fn (mixed $v): string => is_string($v) ? $v : '')
            ->values()
            ->all();

        // Servicios distintos asociados a habitaciones activas (o servicios web activos)
        $serviciosHabitacion = Servicio::whereHas('servicioAsignaciones', function (Builder $query): void {
            $query->whereHasMorph('serviceable', Habitacion::class, function (Builder $q): void {
                $q->where('estado', 1);
            });
        })
            ->orWhere('web', true)
            ->with('categoria')
            ->activos()
            ->distinct()
            ->get()
            ->map(fn (Servicio $s): array => [
                'id' => (int) $s->id,
                'nombre' => (string) $s->nombre,
                'slug' => (string) ($s->slug ?? (string) $s->id),
                'categoria' => (string) ($s->categoria->nombre ?? 'Servicio'),
            ])
            ->unique('id')
            ->values()
            ->all();

        // Calcular rango de precios y capacidades de habitaciones activas
        $habitaciones = Habitacion::with(['precios', 'detalle'])
            ->activas()
            ->get();

        $precios = $habitaciones->map(function (Habitacion $h): ?float {
            $p = $h->precios->first();

            return $p !== null ? (float) $p->precio : null;
        })->filter()->values()->all();

        $capacidades = $habitaciones->map(function (Habitacion $h): int {
            $d = $h->detalle;

            return $d ? (int) ($d->capacidad_adultos + $d->capacidad_ninos) : 2;
        })->unique()->sort()->values()->all();

        return [
            'categorias' => $categorias,
            'vistas' => $vistas,
            'servicios' => $serviciosHabitacion,
            'capacidades' => ! empty($capacidades) ? $capacidades : [1, 2, 3, 4],
            'precioMin' => ! empty($precios) ? (float) min($precios) : 0.0,
            'precioMax' => ! empty($precios) ? (float) max($precios) : 0.0,
        ];
    }
}
