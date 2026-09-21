<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;

final readonly class GenerarCodigoHabitacion
{
    public function __construct(
        private HabitacionRepositorioInterface $habitacionRepositorio,
    ) {}

    public function ejecutar(): string
    {
        return $this->habitacionRepositorio->generarCodigo();
    }
}
