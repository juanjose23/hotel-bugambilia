<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Pack;

use App\BusinessLogic\Inventario\Data\Pack\VarianteData;
use App\Repository\Models\Catalogos\ProductoVariante;
use Illuminate\Support\Collection;

class ObtenerVariantesParaPackQuery
{
    /** @return Collection<int, VarianteData> */
    public function ejecutar(): Collection
    {
        return ProductoVariante::query()
            ->with('producto')
            ->whereHas('producto', fn ($q) => $q->whereIn('tipo', [1, 2]))
            ->get()
            ->map(fn (ProductoVariante $v) => new VarianteData(
                id: $v->id,
                label: ($v->producto->nombre ?? '?').' — '.($v->nombre_variante ?? $v->codigo),
            ));
    }
}
