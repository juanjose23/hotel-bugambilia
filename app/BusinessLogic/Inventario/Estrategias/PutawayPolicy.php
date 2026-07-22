<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario\Estrategias;

use App\Repository\Models\Catalogos\Ubicacion;

class PutawayPolicy
{
    private static ?Ubicacion $cache = null;

    public static function sugerirUbicacion(): Ubicacion
    {
        if (self::$cache) {
            return self::$cache;
        }

        $ubicacion = Ubicacion::query()
            ->whereIn('tipo', ['zona', 'almacen'])
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

    /**
     * Sugiere la primera sub-ubicación disponible (estante > nivel > posición)
     * dentro de la ubicación dada.
     */
    public static function sugerirSubUbicacion(Ubicacion $ubicacion): ?Ubicacion
    {
        $estante = Ubicacion::query()
            ->where('tipo', 'estante')
            ->where('padre_id', $ubicacion->id)
            ->where('estado', 1)
            ->first();

        if (! $estante) {
            return null;
        }

        $nivel = Ubicacion::query()
            ->where('tipo', 'nivel')
            ->where('padre_id', $estante->id)
            ->where('estado', 1)
            ->first();

        if (! $nivel) {
            return $estante;
        }

        $posicion = Ubicacion::query()
            ->where('tipo', 'posicion')
            ->where('padre_id', $nivel->id)
            ->where('estado', 1)
            ->first();

        return $posicion ?? $nivel;
    }
}
