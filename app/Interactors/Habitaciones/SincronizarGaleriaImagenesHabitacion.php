<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;

final readonly class SincronizarGaleriaImagenesHabitacion
{
    public function __construct(
        private HabitacionRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<array-key, mixed>  $imagenes
     */
    public function ejecutar(Habitacion $habitacion, array $imagenes): void
    {
        $this->repositorio->sincronizarImagenes($habitacion, $imagenes);
    }
}
