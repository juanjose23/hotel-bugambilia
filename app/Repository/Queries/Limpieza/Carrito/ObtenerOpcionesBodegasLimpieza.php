<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Repository\Models\Catalogos\Ubicacion;

final class ObtenerOpcionesBodegasLimpieza
{
    /**
     * @return array<int, string>
     */
    public function execute(): array
    {
        $opciones = Ubicacion::query()
            ->whereIn('tipo', ['almacen', 'bodega'])
            ->where('nombre', 'not like', 'Carrito%')
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->mapWithKeys(fn (Ubicacion $ubicacion): array => [(int) $ubicacion->id => (string) $ubicacion->nombre])
            ->toArray();

        /** @var array<int, string> $opciones */
        return $opciones;
    }
}
