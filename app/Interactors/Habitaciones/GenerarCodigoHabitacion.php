<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\Repository\Models\Habitaciones\Habitacion;
use App\Services\Shared\GeneradorCodigoService;

class GenerarCodigoHabitacion
{
    public function __construct(
        private readonly GeneradorCodigoService $generadorCodigo
    ) {}

    public function ejecutar(): string
    {
        return $this->generadorCodigo->generarCorrelativo('HAB', Habitacion::class, 'codigo');
    }
}
