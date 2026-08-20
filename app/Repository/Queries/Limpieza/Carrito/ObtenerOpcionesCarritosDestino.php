<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Repository\Models\Catalogos\Ubicacion;

final class ObtenerOpcionesCarritosDestino
{
    /**
     * @return array<int, string>
     */
    public function execute(?int $carritoActualId): array
    {
        $opciones = Ubicacion::query()
            ->where('nombre', 'like', 'Carrito%')
            ->when($carritoActualId !== null, fn ($query) => $query->where('id', '!=', $carritoActualId))
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->mapWithKeys(fn (Ubicacion $ubicacion): array => [(int) $ubicacion->id => (string) $ubicacion->nombre])
            ->toArray();

        /** @var array<int, string> $opciones */
        return $opciones;
    }
}
