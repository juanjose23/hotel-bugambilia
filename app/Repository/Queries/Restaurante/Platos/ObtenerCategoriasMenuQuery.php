<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Platos;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Restaurante\CategoriaPlato;
use App\Repository\Models\Catalogos\Catalogo;
use Illuminate\Support\Collection;

final class ObtenerCategoriasMenuQuery
{
    /**
     * @return Collection<int, string>
     */
    public function ejecutar(): Collection
    {
        /** @var Collection<int, string> $categorias */
        $categorias = Catalogo::query()
            ->whereHas('catalogoTipo', fn ($q) => $q->where('codigo', CatalogoTipo::CATEGORIA_SERVICIO))
            ->whereIn('codigo', CategoriaPlato::codigos())
            ->pluck('nombre', 'id');

        return $categorias;
    }
}
