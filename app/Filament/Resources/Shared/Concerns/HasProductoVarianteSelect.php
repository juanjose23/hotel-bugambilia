<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared\Concerns;

use App\Models\Catalogos\ProductoVariante;

trait HasProductoVarianteSelect
{
    /**
     * @return array<int, string>
     */
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
