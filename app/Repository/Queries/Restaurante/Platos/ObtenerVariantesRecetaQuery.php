<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Platos;

use App\Repository\Models\Catalogos\ProductoVariante;

final class ObtenerVariantesRecetaQuery
{
    /** @return array<int, string> */
    public function variantesDisponibles(): array
    {
        return ProductoVariante::query()
            ->with('producto.unidadMedida')
            ->whereHas('producto', fn ($q) => $q->whereNull('deleted_at'))
            ->get()
            ->mapWithKeys(fn (ProductoVariante $v) => [
                $v->id => '['.($v->producto->nombre ?? '?').'] '.$v->nombre_variante.' ('.$v->codigo.')',
            ])
            ->all();
    }

    public function unidadMedidaDeVariante(int $varianteId): string
    {
        /** @var ProductoVariante|null $variante */
        $variante = ProductoVariante::with('unidadMedida')->find($varianteId);

        return $variante?->unidadMedida->nombre ?? 'uds';
    }
}
