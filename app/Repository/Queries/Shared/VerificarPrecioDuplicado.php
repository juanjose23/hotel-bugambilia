<?php

declare(strict_types=1);

namespace App\Repository\Queries\Shared;

class VerificarPrecioDuplicado
{
    public function ejecutar(
        string $modelClass,
        int $parentId,
        int $monedaId,
        string $foreignKey = 'priceable_id',
        ?string $foreignType = 'priceable_type',
        ?string $parentType = null,
        ?int $excludeId = null,
        ?string $tipoPrecio = null,
    ): bool {
        $query = $modelClass::where($foreignKey, $parentId);

        if ($foreignType !== null && $parentType !== null) {
            $query->where($foreignType, $parentType);
        }

        if ($tipoPrecio !== null) {
            $query->where('tipo_precio', $tipoPrecio);
        }

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query
            ->where('moneda_id', $monedaId)
            ->where('estado', 1)
            ->where('es_oferta', false)
            ->exists();
    }
}
