<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\BusinessLogic\Habitaciones\ServicioClonacionHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Support\Facades\DB;

class ClonarHabitacion
{
    public function __construct(
        private readonly ServicioClonacionHabitacion $servicioClonacion,
    ) {}

    public function execute(
        Habitacion $origen,
        int $nuevoNumero,
        ?string $nuevoNombre = null,
        ?string $nuevoSlug = null,
        ?string $nuevoCodigo = null,
    ): Habitacion {
        if ($nuevoNumero < 1) {
            throw new \InvalidArgumentException('El número de habitación debe ser mayor a cero.');
        }

        $numeroExiste = Habitacion::withTrashed()
            ->where('numero', $nuevoNumero)
            ->where('id', '!=', $origen->id)
            ->exists();

        if ($numeroExiste) {
            throw new \InvalidArgumentException("El número {$nuevoNumero} ya está en uso.");
        }

        return DB::transaction(fn () => $this->servicioClonacion->clonar(
            $origen,
            $nuevoNumero,
            $nuevoNombre,
            $nuevoSlug,
            $nuevoCodigo
        ));
    }
}
