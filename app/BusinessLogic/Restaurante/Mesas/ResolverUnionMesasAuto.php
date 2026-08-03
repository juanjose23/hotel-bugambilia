<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Support\Collection;

final class ResolverUnionMesasAuto
{
    /**
     * Calcula y recomienda las mesas secundarias libres necesarias para completar la capacidad solicitada.
     *
     * @param  Espacio  $mesaPrincipal  Mesa seleccionada como principal
     * @param  int  $comensalesTotales  Cantidad de personas requerida
     * @param  Collection<int, Espacio>  $mesasDisponibles  Listado de mesas libres en el mismo sector/restaurante
     * @return int[] IDs de las mesas secundarias a unir
     */
    public function resolver(
        Espacio $mesaPrincipal,
        int $comensalesTotales,
        Collection $mesasDisponibles
    ): array {
        $capacidadActual = (int) $mesaPrincipal->capacidad_personas;

        if ($capacidadActual >= $comensalesTotales) {
            return [];
        }

        $deficit = $comensalesTotales - $capacidadActual;
        $secundariasParaUnir = [];
        $capacidadAcumulada = 0;

        // Filtrar mesas en la misma área/padre que estén completamente disponibles
        $candidatas = $mesasDisponibles
            ->filter(fn (Espacio $m): bool => $m->id !== $mesaPrincipal->id && $m->estado === EstadoEspacio::Disponible)
            ->sortByDesc(fn (Espacio $m): int => (int) $m->capacidad_personas);

        foreach ($candidatas as $candidata) {
            $capCandidata = (int) $candidata->capacidad_personas;
            $secundariasParaUnir[] = (int) $candidata->id;
            $capacidadAcumulada += $capCandidata;

            if ($capacidadAcumulada >= $deficit) {
                break;
            }
        }

        return $secundariasParaUnir;
    }
}
