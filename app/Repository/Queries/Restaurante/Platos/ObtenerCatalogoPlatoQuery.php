<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Platos;

use App\Enums\Restaurante\CategoriaPlato;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Restaurante\Plato;
use Illuminate\Support\Collection;

final class ObtenerCatalogoPlatoQuery
{
    /**
     * @return array<int|string, string>
     */
    public function categoriasDisponibles(): array
    {
        /** @var array<int|string, string> */
        return Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'CATEGORIA_SERVICIO'))
            ->whereIn('codigo', CategoriaPlato::codigos())
            ->pluck('nombre', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function productosDisponibles(): array
    {
        /** @var array<int|string, string> */
        return Producto::whereNull('deleted_at')
            ->pluck('nombre', 'id')
            ->all();
    }

    /**
     * @return Collection<int, string>
     */
    public function imagenesDePlato(Plato $plato): Collection
    {
        /** @var Collection<int, string> */
        return $plato->imagenes()
            ->orderBy('orden')
            ->pluck('url')
            ->filter()
            ->values();
    }
}
