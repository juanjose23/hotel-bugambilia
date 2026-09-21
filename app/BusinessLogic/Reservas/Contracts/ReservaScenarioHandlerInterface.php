<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Contracts;

use App\BusinessLogic\Reservas\Builders\ReservaBuilder;
use App\BusinessLogic\Reservas\Data\CrearReservaInputData;

interface ReservaScenarioHandlerInterface
{
    /**
     * Prepara, valida reglas de disponibilidad y configura el ReservaBuilder para el escenario concreto.
     *
     * @param  CrearReservaInputData|array<string, mixed>  $datos
     * @param  array<int, mixed>  $serviciosAdicionales
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $habitacionesAdicionales
     */
    public function configurar(
        ReservaBuilder $builder,
        CrearReservaInputData|array $datos,
        array $serviciosAdicionales = [],
        array $espaciosAdicionales = [],
        array $habitacionesAdicionales = [],
    ): ReservaBuilder;
}
