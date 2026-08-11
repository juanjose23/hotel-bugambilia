<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Data;

use Carbon\CarbonInterface;

final readonly class ConsultarDisponibilidadHabitacionData
{
    public function __construct(
        public CarbonInterface $fechaCheckIn,
        public CarbonInterface $fechaCheckOut,
        public int $adultos = 1,
        public int $ninos = 0,
        public int $cantidadHabitaciones = 1,
        public ?int $categoriaHabitacionId = null,
    ) {}
}
