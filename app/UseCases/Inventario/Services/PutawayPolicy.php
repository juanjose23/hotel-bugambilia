<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Services;

use App\Models\Catalogos\Ubicacion;

class PutawayPolicy
{
    private static ?Ubicacion $cache = null;

    public static function sugerirUbicacion(): Ubicacion
    {
        if (self::$cache) {
            return self::$cache;
        }

        $ubicacion = Ubicacion::whereIn('tipo', ['zona', 'almacen'])
            ->where('estado', 1)
            ->first();

        if (! $ubicacion) {
            throw new \RuntimeException(
                'No hay ubicaciones activas disponibles para asignar inventario. Crea al menos una ubicación de tipo "zona" o "almacen" en Catálogos > Ubicaciones.',
            );
        }

        self::$cache = $ubicacion;

        return $ubicacion;
    }
}
