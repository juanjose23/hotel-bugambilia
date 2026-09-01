<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Catalogo;

final class ObtenerCategoriasHabitacionQuery
{
    /**
     * @return array<int, array{id: int, nombre: string, slug: string, habitaciones_count: int}>
     */
    public function ejecutar(): array
    {
        $categorias = Catalogo::whereHas('catalogoTipo', function ($query): void {
            $query->whereIn('codigo', ['CATEGORIA_HABITACION', 'categoria_habitacion']);
        })
            ->withCount(['habitaciones' => function ($query): void {
                $query->where('estado', 1);
            }])
            ->get();

        return $categorias->map(fn (Catalogo $c): array => [
            'id' => $c->id,
            'nombre' => $c->nombre,
            'slug' => $c->slug ?? strtolower(str_replace(' ', '-', $c->nombre)),
            'habitaciones_count' => (int) ($c->habitaciones_count ?? 0),
        ])->values()->all();
    }
}
