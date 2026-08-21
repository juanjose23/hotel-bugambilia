<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;

final class ObtenerOpcionesDestinoLavanderia
{
    /**
     * @return array<int, string>
     */
    public function execute(string $tipoDestino): array
    {
        $opciones = match ($tipoDestino) {
            'habitacion' => Habitacion::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
                ->mapWithKeys(fn (Habitacion $habitacion): array => [(int) $habitacion->id => (string) $habitacion->nombre])
                ->toArray(),
            'espacio' => Espacio::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
                ->mapWithKeys(fn (Espacio $espacio): array => [(int) $espacio->id => (string) $espacio->nombre])
                ->toArray(),
            'ubicacion' => Ubicacion::query()
                ->whereIn('tipo', ['almacen', 'bodega', 'zona'])
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
                ->mapWithKeys(fn (Ubicacion $ubicacion): array => [(int) $ubicacion->id => (string) $ubicacion->nombre])
                ->toArray(),
            'carrito' => Ubicacion::query()
                ->where('tipo', 'carrito')
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
                ->mapWithKeys(fn (Ubicacion $ubicacion): array => [(int) $ubicacion->id => (string) $ubicacion->nombre])
                ->toArray(),
            default => [],
        };

        /** @var array<int, string> $opciones */
        return $opciones;
    }
}
