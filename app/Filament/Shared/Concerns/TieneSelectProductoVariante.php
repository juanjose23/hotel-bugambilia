<?php

declare(strict_types=1);

namespace App\Filament\Shared\Concerns;

use App\Repository\Models\Catalogos\ProductoVariante;

trait TieneSelectProductoVariante
{
    /** @return array<int|string, string> */
    protected function getProductoVarianteOptions(): array
    {
        return ProductoVariante::with('producto')
            ->get()
            ->mapWithKeys(fn (ProductoVariante $v) => [
                $v->id => '['.($v->producto ? $v->producto->nombre : 'N/A').'] '.($v->nombre_variante ?? '').' ('.$v->codigo.')',
            ])
            ->all();
    }
}
