<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos\Reportes;

use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;

final class ObtenerOpcionesReportesActivosQuery
{
    /** @return array<int, string> */
    public function opcionesHabitaciones(): array
    {
        /** @var array<int, string> $opciones */
        $opciones = Habitacion::orderBy('numero')->pluck('nombre', 'id')->all();

        return $opciones;
    }

    /** @return array<int, string> */
    public function opcionesEspacios(): array
    {
        /** @var array<int, string> $opciones */
        $opciones = Espacio::orderBy('nombre')->pluck('nombre', 'id')->all();

        return $opciones;
    }

    /** @return array<int, string> */
    public function opcionesActivos(): array
    {
        /** @var array<int, string> $opciones */
        $opciones = Activo::whereNotNull('codigo_inventario')
            ->orderBy('codigo_inventario')
            ->pluck('codigo_inventario', 'id')
            ->all();

        return $opciones;
    }
}
