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
            ->whereHas('producto')
            ->get()
            ->map(function (ProductoVariante $v) {
                $nombreProducto = $v->producto->nombre ?? 'Producto #'.$v->producto_id;
                $detalleVariante = $v->nombre_variante ?: ($v->codigo ?: null);
                $label = $detalleVariante
                    ? "{$nombreProducto} — {$detalleVariante}"
                    : $nombreProducto;

                return new VarianteData(
                    id: (int) $v->id,
                    label: $label,
                );
            });
    }
}
