<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ubicacion;

use App\Repository\Models\Catalogos\Ubicacion;

class ObtenerPathUbicacion
{
    /** @param  list<int|string>|null  $tipos
     * @return array<int|string, string>
     */
    public function ejecutar(?array $tipos = null, ?int $estado = null): array
    {
        $query = Ubicacion::query();

        if ($tipos !== null) {
            $query->whereIn('tipo', $tipos);
        }

        if ($estado !== null) {
            $query->where('estado', $estado);
        }

        $all = $query->get();
        $map = $all->keyBy('id');

        $buildPath = function (Ubicacion $u) use (&$buildPath, $map): string {
            if ($u->padre_id && $map->has($u->padre_id)) {
                $padre = $map->get($u->padre_id);
                if ($padre === null) {
                    return $u->nombre;
                }

                return $buildPath($padre).' ➔ '.$u->nombre;
            }

            return $u->nombre;
        };

        $result = [];
        foreach ($all as $u) {
            $result[$u->id] = $buildPath($u);
        }

        asort($result);

        return $result;
    }
}
