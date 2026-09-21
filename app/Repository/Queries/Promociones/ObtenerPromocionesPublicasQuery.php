<?php

declare(strict_types=1);

namespace App\Repository\Queries\Promociones;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Promociones\Promocion;
use Illuminate\Support\Collection;

final readonly class ObtenerPromocionesPublicasQuery
{
    /**
     * @return Collection<int, Promocion>
     */
    public function ejecutar(?string $categoria = null, ?string $busqueda = null): Collection
    {
        $query = Promocion::with([
            'tipo',
            'imagenes',
            'beneficios',
            'items.item',
            'precios.moneda',
        ])
            ->activos()
            ->where(function ($q) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', now()->toDateString());
            });

        if ($categoria !== null && trim($categoria) !== '' && strtoupper(trim($categoria)) !== 'TODAS') {
            $query->whereHas('tipo', function ($q) use ($categoria) {
                $q->where('nombre', trim($categoria));
            });
        }

        if ($busqueda !== null && trim($busqueda) !== '') {
            $term = '%'.trim($busqueda).'%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('descripcion', 'like', $term);
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }

    /**
     * @return array<int, string>
     */
    public function obtenerCategorias(): array
    {
        $tipoIds = Promocion::activos()->whereNotNull('tipo_promocion_id')->pluck('tipo_promocion_id')->unique();

        return Catalogo::query()
            ->whereIn('id', $tipoIds)
            ->pluck('nombre')
            ->filter()
            ->map(fn (mixed $nombre): string => is_string($nombre) ? $nombre : '')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
