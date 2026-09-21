<?php

declare(strict_types=1);

namespace App\Interactors\Habitaciones;

use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class ClonarHabitacion
{
    public function __construct(
        private HabitacionRepositorioInterface $habitacionRepositorio,
    ) {}

    public function execute(
        Habitacion $origen,
        int $nuevoNumero,
        ?string $nuevoNombre = null,
        ?string $nuevoSlug = null,
        ?string $nuevoCodigo = null,
    ): Habitacion {
        return $this->ejecutar(
            $origen,
            $nuevoNumero,
            $nuevoNombre,
            $nuevoSlug,
            $nuevoCodigo,
        );
    }

    public function ejecutar(
        Habitacion $origen,
        int $nuevoNumero,
        ?string $nuevoNombre = null,
        ?string $nuevoSlug = null,
        ?string $nuevoCodigo = null,
    ): Habitacion {
        if ($nuevoNumero < 1) {
            throw new \InvalidArgumentException('El número de habitación debe ser mayor a cero.');
        }

        $numeroExiste = $this->habitacionRepositorio->existePorNumero($nuevoNumero, $origen->id);

        if ($numeroExiste) {
            throw new \InvalidArgumentException("El número {$nuevoNumero} ya está en uso.");
        }

        return DB::transaction(fn () => $this->habitacionRepositorio->clonar(
            $origen,
            $nuevoNumero,
            $nuevoNombre,
            $nuevoSlug,
            $nuevoCodigo
        ));
    }
}
