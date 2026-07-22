<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Enums\Activos\EstadoIndividualizacion;

class ReglasIndividualizacion
{
    public function validarCantidad(int $cantidadRegistrada, int $cantidadAIndividualizar, int $cantidadTotal): void
    {
        if ($cantidadRegistrada + $cantidadAIndividualizar > $cantidadTotal) {
            throw new \RuntimeException('La cantidad a registrar supera el total pendiente.');
        }
    }

    public function determinarNuevoEstado(int $cantidadRegistradaActualizada, int $cantidadTotal): EstadoIndividualizacion
    {
        if ($cantidadRegistradaActualizada >= $cantidadTotal) {
            return EstadoIndividualizacion::Completado;
        }

        return EstadoIndividualizacion::EnProceso;
    }
}
