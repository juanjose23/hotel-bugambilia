<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Habitaciones\VistasHabitacionRepositorio;
use Illuminate\Support\Facades\DB;

final readonly class SincronizarVistasHabitacion
{
    public function __construct(private VistasHabitacionRepositorio $vistas) {}

    /** @param array<int, mixed> $ids */
    public function ejecutar(Habitacion $habitacion, array $ids): void
    {
        DB::transaction(fn () => $this->vistas->sincronizar($habitacion, $ids));
    }
}
